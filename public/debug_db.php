<?php
require_once __DIR__ . '/../app/db.php';
header('Content-Type: text/plain; charset=utf-8');

$pdo = get_pdo();
$db = $pdo->query("SELECT DATABASE() AS db")->fetch();
echo "CONNECTED_DB=" . ($db['db'] ?? 'NULL') . PHP_EOL;

$cnt = $pdo->query("SELECT COUNT(*) AS c FROM users")->fetch();
echo "USERS_COUNT=" . ($cnt['c'] ?? '0') . PHP_EOL;

