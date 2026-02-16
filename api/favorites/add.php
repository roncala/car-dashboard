<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/response.php';
require_once __DIR__ . '/../../app/auth_middleware.php';
require_once __DIR__ . '/../../app/security.php';
require_once __DIR__ . '/../../app/validators.php';

require_login();
require_csrf();

$pdo = get_pdo();
$userId = current_user_id();
$body = read_json_body();

$carId = to_int($body['car_id'] ?? null, 1, 1000000000);
if ($carId === null) bad_request('car_id required.');

$stmt = $pdo->prepare("INSERT IGNORE INTO favorites(user_id, car_id) VALUES(?,?)");
$stmt->execute([$userId, $carId]);

json_response(['message' => 'Added to favorites.']);

