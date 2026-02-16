<?php
// app/security.php
declare(strict_types=1);

function start_secure_session(): void {
  if (session_status() === PHP_SESSION_ACTIVE) return;

  $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
  session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'secure' => $secure,
    'samesite' => 'Lax',
  ]);

  session_start();
}

function csrf_token(): string {
  start_secure_session();
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

function require_csrf(): void {
  start_secure_session();
  $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
  if (!$token || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'CSRF token invalid or missing']);
    exit;
  }
}

