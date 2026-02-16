#!/usr/bin/env python3
import argparse
import csv
import re
from decimal import Decimal
from typing import Optional, List, Dict, Any

# EXACT headers you provided
H_COMPANY = "Company Names"
H_CAR     = "Cars Names"
H_ENGINE  = "Engines"
H_CC      = "CC/Battery Capacity"
H_HP      = "HorsePower"
H_SPEED   = "Total Speed"
H_ACCEL   = "Performance(0 - 100 )KM/H"
H_PRICE   = "Cars Prices"
H_FUEL    = "Fuel Types"
H_SEATS   = "Seats"
H_TORQUE  = "Torque"

# ----------------------------
# Basic cleaning helpers
# ----------------------------
def norm(s: Optional[str]) -> Optional[str]:
    if s is None:
        return None
    s = str(s).strip()
    s = re.sub(r"\s+", " ", s)
    return s if s != "" else None

def extract_numbers(text: Optional[str]) -> List[float]:
    """Extract all numbers (int/float) from a string. Handles commas."""
    if not text:
        return []
    t = str(text).replace(",", "")
    nums = re.findall(r"(\d+(?:\.\d+)?)", t)
    return [float(x) for x in nums] if nums else []

def fmt_num(n: float) -> str:
    """Format number without .0 if integer."""
    if abs(n - round(n)) < 1e-9:
        return str(int(round(n)))
    return str(n)

def strip_leading_index(v: Optional[str]) -> Optional[str]:
    """Remove leading '1 ' / '2 ' / etc from a value."""
    v = norm(v)
    if not v:
        return None
    v = re.sub(r"^\d+\s+", "", v).strip()
    return v if v else None

# ----------------------------
# Field-specific cleaners
# ----------------------------
def clean_company(value: Optional[str]) -> Optional[str]:
    return strip_leading_index(value)

def clean_car_name(value: Optional[str]) -> Optional[str]:
    # Sometimes datasets include leading numbers; safe to strip
    return strip_leading_index(value)

def clean_engine(value: Optional[str]) -> Optional[str]:
    v = norm(value)
    if not v:
        return None
    # Rule: contains electric -> "Electric"
    if "electric" in v.lower():
        return "Electric"
    return v

def clean_cc_battery(value: Optional[str]) -> Optional[str]:
    """
    Rules:
    - If multiple cc values: keep highest cc
    - If multiple kWh values: keep highest kWh
    - If both exist: keep both: "MAXcc cc / MAXkWh kWh"
      Example: "1,600 cc / 13.8 kwh" -> "1600 cc / 13.8 kWh"
    - If units not detected: fallback to highest number only
    """
    v = norm(value)
    if not v:
        return None

    t = v.replace(",", "")
    low = t.lower()

    # unit-tagged values
    cc_nums  = re.findall(r"(\d+(?:\.\d+)?)\s*cc\b", low)
    kwh_nums = re.findall(r"(\d+(?:\.\d+)?)\s*kwh\b", low)

    cc_vals  = [float(x) for x in cc_nums] if cc_nums else []
    kwh_vals = [float(x) for x in kwh_nums] if kwh_nums else []

    if cc_vals and kwh_vals:
        return f"{fmt_num(max(cc_vals))} cc / {fmt_num(max(kwh_vals))} kWh"
    if cc_vals:
        return f"{fmt_num(max(cc_vals))} cc"
    if kwh_vals:
        return f"{fmt_num(max(kwh_vals))} kWh"

    # fallback: no explicit unit found -> highest number
    nums = extract_numbers(t)
    if not nums:
        return v
    return fmt_num(max(nums))

def clean_torque(value: Optional[str]) -> Optional[str]:
    """
    Rule: if torque has range, pick highest.
    Example: "100 - 140 Nm." -> "140Nm"
    """
    v = norm(value)
    if not v:
        return None

    low = v.lower()
    nums = extract_numbers(v)
    if not nums:
        return v

    max_num = max(nums)

    if "nm" in low:
        return f"{fmt_num(max_num)}Nm"
    if "lb" in low:
        return f"{fmt_num(max_num)}lb-ft"
    return fmt_num(max_num)

def clean_horsepower(value: Optional[str]) -> Optional[int]:
    """If range like '70-85 hp' -> 85"""
    v = norm(value)
    if not v:
        return None
    nums = extract_numbers(v)
    return int(round(max(nums))) if nums else None

def clean_total_speed(value: Optional[str]) -> Optional[int]:
    """If range -> highest"""
    v = norm(value)
    if not v:
        return None
    nums = extract_numbers(v)
    return int(round(max(nums))) if nums else None

def clean_accel_0_100(value: Optional[str]) -> Optional[float]:
    """If multiple values, pick lowest (best time)."""
    v = norm(value)
    if not v:
        return None
    nums = extract_numbers(v)
    return float(min(nums)) if nums else None

def clean_price(value: Optional[str]) -> Optional[Decimal]:
    """If range -> lowest (starting price)."""
    v = norm(value)
    if not v:
        return None
    nums = extract_numbers(v)
    return Decimal(str(min(nums))) if nums else None

def clean_seats(value: Optional[str]) -> Optional[int]:
    """Handles '2+2' => 4, '2-7' => 7, '5' => 5"""
    v = norm(value)
    if not v:
        return None

    if "+" in v:
        parts = [p.strip() for p in v.split("+") if p.strip()]
        total = 0
        ok = False
        for p in parts:
            nums = extract_numbers(p)
            if nums:
                total += int(round(nums[0]))
                ok = True
        return total if ok else None

    nums = extract_numbers(v)
    return int(round(max(nums))) if nums else None

def normalize_fuel(value: Optional[str]) -> Optional[str]:
    v = norm(value)
    if not v:
        return None
    low = v.lower()

    if "plug" in low and "hybrid" in low:
        return "Plug-in Hybrid"
    if "hybrid" in low:
        return "Hybrid"
    if "electric" in low:
        return "Electric"
    if "diesel" in low:
        return "Diesel"
    if "petrol" in low or "gasoline" in low:
        return "Petrol"
    if "cng" in low:
        return "CNG"
    if "hydrogen" in low:
        return "Hydrogen"

    return v

# ----------------------------
# DB connection
# ----------------------------
def connect_db(host: str, user: str, password: str, database: str, port: int):
    """
    Tries mysql-connector-python first, then pymysql.
    """
    try:
        import mysql.connector  # type: ignore
        return mysql.connector.connect(
            host=host, user=user, password=password, database=database, port=port
        )
    except Exception:
        pass

    try:
        import pymysql  # type: ignore
        return pymysql.connect(
            host=host, user=user, password=password, database=database, port=port,
            autocommit=False, charset="utf8mb4"
        )
    except Exception as e:
        raise RuntimeError(
            "Install a MySQL driver:\n"
            "  pip3 install --user mysql-connector-python\n"
            "or\n"
            "  pip3 install --user pymysql\n"
        ) from e

# ----------------------------
# CSV reading: robust delimiter detection
# ----------------------------
def detect_delimiter(csv_path: str) -> str:
    """
    Prefer a quick deterministic check (tab vs comma) by reading first line.
    Fallback to csv.Sniffer.
    """
    with open(csv_path, "r", encoding="utf-8-sig", newline="") as f:
        first = f.readline()
        if "\t" in first and first.count("\t") >= 3:
            return "\t"
        if "," in first and first.count(",") >= 3:
            return ","

    with open(csv_path, "r", encoding="utf-8-sig", newline="") as f:
        sample = f.read(4096)
        sniffer = csv.Sniffer()
        try:
            dialect = sniffer.sniff(sample, delimiters=[",", "\t", ";", "|"])
            return dialect.delimiter
        except Exception:
            # safest default given your dataset sample
            return "\t"

def read_and_clean_rows(csv_path: str, limit: Optional[int] = None) -> List[Dict[str, Any]]:
    cleaned: List[Dict[str, Any]] = []
    seen = set()

    delimiter = detect_delimiter(csv_path)

    with open(csv_path, "r", encoding="utf-8-sig", newline="") as f:
        reader = csv.DictReader(f, delimiter=delimiter)

        required = [H_COMPANY, H_CAR, H_ENGINE, H_CC, H_HP, H_SPEED, H_ACCEL, H_PRICE, H_FUEL, H_SEATS, H_TORQUE]
        missing = [h for h in required if h not in (reader.fieldnames or [])]
        if missing:
            raise ValueError(f"Missing expected headers: {missing}\nFound: {reader.fieldnames}")

        for i, row in enumerate(reader, start=1):
            if limit and i > limit:
                break

            company = clean_company(row.get(H_COMPANY))
            car_name = clean_car_name(row.get(H_CAR))
            if not company or not car_name:
                continue

            key = (company.lower(), car_name.lower())
            if key in seen:
                continue
            seen.add(key)

            engine = clean_engine(row.get(H_ENGINE))
            cc_batt = clean_cc_battery(row.get(H_CC))
            hp = clean_horsepower(row.get(H_HP))
            speed = clean_total_speed(row.get(H_SPEED))
            accel = clean_accel_0_100(row.get(H_ACCEL))
            price = clean_price(row.get(H_PRICE))
            fuel = normalize_fuel(row.get(H_FUEL))
            seats = clean_seats(row.get(H_SEATS))
            torque = clean_torque(row.get(H_TORQUE))

            cleaned.append({
                "company_name": company,
                "car_name": car_name,
                "engine": engine,
                "cc_battery_capacity": cc_batt,
                "horsepower": hp,
                "total_speed": speed,
                "accel_0_100": accel,
                "price": price,
                "fuel_type": fuel,
                "seats": seats,
                "torque": torque,
            })

    return cleaned

def write_cleaned_csv(out_path: str, rows: List[Dict[str, Any]]) -> None:
    fieldnames = [
        "company_name","car_name","engine","cc_battery_capacity","horsepower",
        "total_speed","accel_0_100","price","fuel_type","seats","torque"
    ]
    with open(out_path, "w", encoding="utf-8", newline="") as f:
        w = csv.DictWriter(f, fieldnames=fieldnames)
        w.writeheader()
        for r in rows:
            rr = dict(r)
            if isinstance(rr.get("price"), Decimal):
                rr["price"] = str(rr["price"])
            w.writerow(rr)

# ----------------------------
# UPSERT into cars table
# ----------------------------
def upsert_cars(conn, rows: List[Dict[str, Any]], batch_size: int = 500) -> int:
    """
    Upsert using UNIQUE(company_name, car_name).
    Uses COALESCE(VALUES(col), cars.col) so NULL from CSV won't overwrite existing values.
    """
    sql = """
    INSERT INTO cars
      (company_name, car_name, engine, cc_battery_capacity, horsepower,
       total_speed, accel_0_100, price, fuel_type, seats, torque)
    VALUES
      (%s, %s, %s, %s, %s,
       %s, %s, %s, %s, %s, %s)
    ON DUPLICATE KEY UPDATE
      engine              = COALESCE(VALUES(engine), cars.engine),
      cc_battery_capacity = COALESCE(VALUES(cc_battery_capacity), cars.cc_battery_capacity),
      horsepower          = COALESCE(VALUES(horsepower), cars.horsepower),
      total_speed         = COALESCE(VALUES(total_speed), cars.total_speed),
      accel_0_100         = COALESCE(VALUES(accel_0_100), cars.accel_0_100),
      price               = COALESCE(VALUES(price), cars.price),
      fuel_type           = COALESCE(VALUES(fuel_type), cars.fuel_type),
      seats               = COALESCE(VALUES(seats), cars.seats),
      torque              = COALESCE(VALUES(torque), cars.torque);
    """

    values = []
    for r in rows:
        price_val = str(r["price"]) if isinstance(r.get("price"), Decimal) else r.get("price")
        values.append((
            r.get("company_name"),
            r.get("car_name"),
            r.get("engine"),
            r.get("cc_battery_capacity"),
            r.get("horsepower"),
            r.get("total_speed"),
            r.get("accel_0_100"),
            price_val,
            r.get("fuel_type"),
            r.get("seats"),
            r.get("torque"),
        ))

    cur = conn.cursor()
    try:
        for start in range(0, len(values), batch_size):
            cur.executemany(sql, values[start:start + batch_size])
        conn.commit()
    except Exception:
        conn.rollback()
        raise
    finally:
        cur.close()

    return len(values)

# ----------------------------
# Main
# ----------------------------
def main():
    ap = argparse.ArgumentParser(description="Clean Cars Datasets 2025.csv and upsert into MySQL cars table (no LOAD DATA).")
    ap.add_argument("--csv", required=True, help="Path to Cars Datasets 2025.csv on server.")
    ap.add_argument("--host", required=True, help="MySQL host.")
    ap.add_argument("--port", type=int, default=3306, help="MySQL port.")
    ap.add_argument("--user", required=True, help="MySQL username.")
    ap.add_argument("--password", required=True, help="MySQL password.")
    ap.add_argument("--db", required=True, help="Database/schema name.")
    ap.add_argument("--limit", type=int, default=None, help="Limit rows for testing.")
    ap.add_argument("--dry-run", action="store_true", help="Clean only, do not insert.")
    ap.add_argument("--write-cleaned-csv", default=None, help="Optional path to write cleaned CSV.")
    ap.add_argument("--batch-size", type=int, default=500, help="Insert batch size.")
    args = ap.parse_args()

    rows = read_and_clean_rows(args.csv, limit=args.limit)
    print(f"Cleaned unique rows ready: {len(rows)}")

    if args.write_cleaned_csv:
        write_cleaned_csv(args.write_cleaned_csv, rows)
        print(f"Wrote cleaned CSV: {args.write_cleaned_csv}")

    if args.dry_run:
        for r in rows[:5]:
            print(r)
        print("Dry-run enabled: skipping DB insert.")
        return

    conn = connect_db(args.host, args.user, args.password, args.db, args.port)
    processed = upsert_cars(conn, rows, batch_size=args.batch_size)

    try:
        conn.close()
    except Exception:
        pass

    print(f"Upsert complete. Rows processed: {processed}")

if __name__ == "__main__":
    main()

