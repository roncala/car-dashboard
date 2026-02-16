<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/response.php';
require_once __DIR__ . '/../../app/auth_middleware.php';

require_login();
$pdo = get_pdo();
$userId = current_user_id();

$stmt = $pdo->prepare("
  SELECT c.*
  FROM favorites f
  JOIN cars c ON c.car_id = f.car_id
  WHERE f.user_id = ?
  ORDER BY f.created_at DESC
");
$stmt->execute([$userId]);

json_response(['favorites' => $stmt->fetchAll()]);

