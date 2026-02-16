<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/response.php';
require_once __DIR__ . '/../../app/auth_middleware.php';
require_once __DIR__ . '/../../app/security.php';
require_once __DIR__ . '/../../app/validators.php';

start_secure_session();
require_csrf();

$pdo = get_pdo();
$body = read_json_body();

$email    = validate_email($body['email'] ?? null);
$password = $body['password'] ?? null;

if (!$email) bad_request('Valid email is required.');
if (!$password) bad_request('Password is required.');

$stmt = $pdo->prepare("SELECT user_id, password_hash FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify((string)$password, $user['password_hash'])) {
  unauthorized('Invalid email or password.');
}

$_SESSION['user_id'] = (int)$user['user_id'];

json_response(['message' => 'Logged in.']);

