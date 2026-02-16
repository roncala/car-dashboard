<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/response.php';
require_once __DIR__ . '/../../app/validators.php';

$pdo = get_pdo();

$company = str_clean($_GET['company'] ?? null, 80);
$fuel    = str_clean($_GET['fuel'] ?? null, 40);
$seats   = to_int($_GET['seats'] ?? null, 1, 12);

$min_price = to_float($_GET['min_price'] ?? null);
$max_price = to_float($_GET['max_price'] ?? null);

$search = str_clean($_GET['q'] ?? null, 120);

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(50, max(5, (int)($_GET['per_page'] ?? 10)));
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($company) { $where[] = "company_name = ?"; $params[] = $company; }
if ($fuel)    { $where[] = "fuel_type = ?";    $params[] = $fuel; }
if ($seats !== null) { $where[] = "seats = ?"; $params[] = $seats; }
if ($min_price !== null) { $where[] = "price >= ?"; $params[] = $min_price; }
if ($max_price !== null) { $where[] = "price <= ?"; $params[] = $max_price; }
if ($search) {
  $where[] = "(company_name LIKE ? OR car_name LIKE ?)";
  $params[] = "%{$search}%";
  $params[] = "%{$search}%";
}

$whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

$countStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM cars {$whereSql}");
$countStmt->execute($params);
$total = (int)$countStmt->fetch()['cnt'];

$sql = "SELECT car_id, company_name, car_name, engine, cc_battery_capacity, horsepower,
               total_speed, accel_0_100, price, fuel_type, seats, torque
        FROM cars
        {$whereSql}
        ORDER BY price DESC
        LIMIT {$perPage} OFFSET {$offset}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

json_response([
  'page' => $page,
  'per_page' => $perPage,
  'total' => $total,
  'cars' => $stmt->fetchAll()
]);

