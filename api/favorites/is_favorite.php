<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/response.php';
require_once __DIR__ . '/../../app/auth_middleware.php';
require_once __DIR__ . '/../../app/validators.php';

require_login();

$carId = to_int($_GET['car_id'] ?? null, 1, 1000000000);
if ($carId === null) bad_request('car_id required.');

$pdo = get_pdo();
$userId = current_user_id();

$stmt = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND car_id = ? LIMIT 1");
$stmt->execute([$userId, $carId]);

json_response([
  'is_favorite' => $stmt->fetch() ? true : false
]);
