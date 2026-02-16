<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/response.php';
require_once __DIR__ . '/../../app/auth_middleware.php';

require_login();
$pdo = get_pdo();

$userId = current_user_id();

$stmt = $pdo->prepare("
  SELECT user_id, full_name, email, phone,
         address_line1, address_line2, city, state, zip_code, country,
         latitude, longitude,
         created_at, updated_at
  FROM users
  WHERE user_id = ?
");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) unauthorized('User not found.');

json_response(['user' => $user]);

