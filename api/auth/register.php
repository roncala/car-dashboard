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

$full_name = str_clean($body['full_name'] ?? null, 100);
$email     = validate_email($body['email'] ?? null);
$password  = $body['password'] ?? null;

if (!$full_name) bad_request('Full name is required.');
if (!$email) bad_request('Valid email is required.');
if (!$password || strlen((string)$password) < 6) bad_request('Password must be at least 6 characters.');

$stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) bad_request('Email already in use.');

$hash = password_hash((string)$password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users(full_name, email, password_hash) VALUES(?,?,?)");
$stmt->execute([$full_name, $email, $hash]);

$_SESSION['user_id'] = (int)$pdo->lastInsertId();

json_response(['message' => 'Registered and logged in.']);

