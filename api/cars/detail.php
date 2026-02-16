<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/response.php';
require_once __DIR__ . '/../../app/validators.php';

$pdo = get_pdo();
$id = to_int($_GET['id'] ?? null, 1, 1000000000);
if ($id === null) bad_request('Missing car id.');

$stmt = $pdo->prepare("SELECT * FROM cars WHERE car_id = ?");
$stmt->execute([$id]);
$car = $stmt->fetch();
if (!$car) not_found('Car not found.');

json_response(['car' => $car]);

