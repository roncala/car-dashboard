<?php
// simple redirect wrapper
require_once __DIR__ . '/../app/security.php';
start_secure_session();

$base = getenv('APP_BASE_URL') ?: '';
// call API logout then redirect
header("Location: {$base}/api/auth/logout.php");
exit;

