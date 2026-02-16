<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/response.php';

$pdo = get_pdo();

// Basic KPI
$kpi = $pdo->query("
  SELECT
    COUNT(*) AS total_cars,
    ROUND(AVG(price),2) AS avg_price,
    MAX(price) AS max_price,
    MAX(horsepower) AS max_hp,
    MIN(accel_0_100) AS best_0_100
  FROM cars
")->fetch();

// Fuel distribution
$fuel = $pdo->query("
  SELECT fuel_type, COUNT(*) AS cnt
  FROM cars
  GROUP BY fuel_type
  ORDER BY cnt DESC
")->fetchAll();

// Top priced
$top_price = $pdo->query("
  SELECT car_id, company_name, car_name, price
  FROM cars
  ORDER BY price DESC
  LIMIT 10
")->fetchAll();

// Top HP
$top_hp = $pdo->query("
  SELECT car_id, company_name, car_name, horsepower
  FROM cars
  ORDER BY horsepower DESC
  LIMIT 10
")->fetchAll();

json_response([
  'kpi' => $kpi,
  'fuel_distribution' => $fuel,
  'top_priced' => $top_price,
  'top_horsepower' => $top_hp
]);

