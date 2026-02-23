<?php
$pageTitle = "Login";
$currentPage = "login";
require_once __DIR__ . '/../partials/header.php';
?>

<h1>Login</h1>

<div class="card">
  <form id="loginForm">
    <label>Email</label>
    <input name="email" type="email" required>
    <label>Password</label>
    <input name="password" type="password" required>
    <button type="submit">Login</button>
  </form>
  <div id="msg"></div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const data = Object.fromEntries(new FormData(e.target).entries());
  try {
    await apiPost('/api/auth/login.php', data);
    window.location.href = '../public/index.php';
  } catch (err) {
    document.getElementById('msg').textContent = err.message || 'Login failed';
  }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

