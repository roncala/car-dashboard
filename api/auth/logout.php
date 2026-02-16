<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/response.php';
require_once __DIR__ . '/../../app/security.php';

start_secure_session();
session_unset();
session_destroy();

json_response(['message' => 'Logged out.']);

