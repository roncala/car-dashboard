<?php
$pageTitle = "Login";
$currentPage = "login";
require_once __DIR__ . '/../partials/header.php';
$base = getenv('APP_BASE_URL') ?: '';
?>

<h1>Login</h1>

<div class="card">
  <form id="loginForm">
    <label>Email</label>
    <input name="email" type="email" required>

    <label>Password</label>
    <input name="password" type="password" required>

    <button id="btnLogin" type="submit">Login</button>
  </form>

  <div id="msg" class="muted" style="margin-top:10px;"></div>
</div>

<script defer src="<?= $base ?>/assets/js/login.js?v=1"></script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
