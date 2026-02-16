<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/response.php';
require_once __DIR__ . '/../../app/auth_middleware.php';
require_once __DIR__ . '/../../app/security.php';
require_once __DIR__ . '/../../app/validators.php';

require_login();
require_csrf();

$pdo  = get_pdo();
$body = read_json_body();
$userId = current_user_id();

$allowed = [
  'full_name','email','phone',
  'address_line1','address_line2','city','state','zip_code','country',
  'latitude','longitude'
];

$updates = [];
$params  = [];

foreach ($allowed as $k) {
  if (array_key_exists($k, $body)) {
    $val = $body[$k];

    if (in_array($k, ['latitude','longitude'], true)) {
      $val = to_float($val);
    } elseif ($k === 'email') {
      $val = validate_email($val);
      if ($val === null) bad_request('Invalid email.');
      // ensure unique
      $chk = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id <> ?");
      $chk->execute([$val, $userId]);
      if ($chk->fetch()) bad_request('Email already in use.');
    } else {
      $val = str_clean($val, 150);
    }

    $updates[] = "{$k} = ?";
    $params[]  = $val;
  }
}

// optional password change
if (!empty($body['password_current']) || !empty($body['password_new'])) {
  if (empty($body['password_current']) || empty($body['password_new'])) {
    bad_request('Provide password_current and password_new to change password.');
  }
  $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
  $stmt->execute([$userId]);
  $row = $stmt->fetch();
  if (!$row || !password_verify((string)$body['password_current'], $row['password_hash'])) {
    forbidden('Current password is incorrect.');
  }
  if (strlen((string)$body['password_new']) < 6) bad_request('New password must be at least 6 characters.');

  $updates[] = "password_hash = ?";
  $params[] = password_hash((string)$body['password_new'], PASSWORD_DEFAULT);
}

if (empty($updates)) bad_request('No fields provided.');

$params[] = $userId;

$sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

json_response(['message' => 'Profile updated.']);

