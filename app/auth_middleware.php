<?php
// app/auth_middleware.php
declare(strict_types=1);

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/response.php';

function require_login(): void {
  start_secure_session();
  if (empty($_SESSION['user_id'])) {
    unauthorized('Please log in.');
  }
}

function current_user_id(): ?int {
  start_secure_session();
  return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function read_json_body(): array {
  $ct = $_SERVER['CONTENT_TYPE'] ?? '';
  if (stripos($ct, 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
  }
  return $_POST ?? [];
}

