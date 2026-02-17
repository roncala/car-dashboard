<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/security.php';
require_once __DIR__ . '/../app/db.php';

start_secure_session();

$pageTitle   = $pageTitle ?? 'Car Dashboard';
$currentPage = $currentPage ?? '';

$isLoggedIn = !empty($_SESSION['user_id']);
$csrf       = csrf_token();

// IMPORTANT: set this in .env
// Example: APP_BASE_URL=/~roncala/car-dashboard
$base = getenv('APP_BASE_URL') ?: '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <meta name="csrf-token" content="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>

  <!-- Make base URL available to JS -->
  <script>
    window.APP_BASE_URL = <?= json_encode($base) ?>;
  </script>

  <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/dashboard.css">

  <script defer src="<?= $base ?>/assets/js/app.js?v=3"></script>

  <!-- Chart.js for dashboard charts -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header class="site-header">
  <div class="container header-row">
    <a class="brand" href="<?= $base ?>/public/index.php">Automobile Dashboard</a>

    <nav class="nav">
      <a class="<?= $currentPage==='dashboard' ? 'active' : '' ?>" href="<?= $base ?>/public/index.php">Dashboard</a>
      <a class="<?= $currentPage==='compare' ? 'active' : '' ?>" href="<?= $base ?>/public/compare.php">Compare</a>

      <?php if ($isLoggedIn): ?>
        <a class="<?= $currentPage==='favorites' ? 'active' : '' ?>" href="<?= $base ?>/public/favorites.php">Favorites</a>
        <a class="<?= $currentPage==='profile' ? 'active' : '' ?>" href="<?= $base ?>/public/profile.php">Profile</a>
        <a href="<?= $base ?>/public/logout.php">Logout</a>
      <?php else: ?>
        <a class="<?= $currentPage==='login' ? 'active' : '' ?>" href="<?= $base ?>/public/login.php">Login</a>
        <a class="<?= $currentPage==='register' ? 'active' : '' ?>" href="<?= $base ?>/public/register.php">Register</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="container">

