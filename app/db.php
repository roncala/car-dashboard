<?php
// app/db.php
declare(strict_types=1);

function load_env(string $path): void {
  if (!file_exists($path)) return;

  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;

    $parts = explode('=', $line, 2);
    if (count($parts) !== 2) continue;

    $key = trim($parts[0]);
    $val = trim($parts[1]);

    // strip quotes
    $val = trim($val, "\"'");

    if ($key !== '' && getenv($key) === false) {
      putenv("$key=$val");
      $_ENV[$key] = $val;
    }
  }
}

$root = dirname(__DIR__);
load_env($root . '/.env');

function envv(string $key, string $default = ''): string {
  $v = getenv($key);
  if ($v === false || $v === null || $v === '') return $default;
  return $v;
}

function get_pdo(): PDO {
  static $pdo = null;
  if ($pdo instanceof PDO) return $pdo;

  $host = envv('DB_HOST', 'imc.kean.edu');
  $port = envv('DB_PORT', '3306');
  $db   = envv('DB_NAME', 'CPS4951_26S_01db');
  $user = envv('DB_USER', 'CPS4951_26S_01');
  $pass = envv('DB_PASS', '20260209');

  $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);

  return $pdo;
}

