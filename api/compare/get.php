<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/response.php';

$pdo = get_pdo();
$ids = $_GET['ids'] ?? '';
if (!$ids) bad_request('Provide ids=1,2,3,4');

$arr = array_values(array_filter(array_map('intval', explode(',', $ids))));
$arr = array_unique($arr);
$arr = array_slice($arr, 0, 4);
if (count($arr) === 0) bad_request('No valid ids.');

$in = implode(',', array_fill(0, count($arr), '?'));
$stmt = $pdo->prepare("SELECT * FROM cars WHERE car_id IN ($in)");
$stmt->execute($arr);

json_response(['cars' => $stmt->fetchAll()]);

