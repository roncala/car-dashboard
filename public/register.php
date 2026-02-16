<?php
$pageTitle = "Register";
$currentPage = "register";
require_once __DIR__ . '/../partials/header.php';
?>

<h1>Register</h1>

<div class="card">
  <form id="regForm">
    <label>Full Name</label>
    <input name="full_name" required>
    <label>Email</label>
    <input name="email" type="email" required>
    <label>Password</label>
    <input name="password" type="password" minlength="6" required>
    <button type="submit">Create Account</button>
  </form>
  <div id="msg"></div>
</div>

<script>
document.getElementById('regForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const data = Object.fromEntries(new FormData(e.target).entries());
  try {
    await apiPost('/api/auth/register.php', data);
    window.location.href = '../public/index.php';
  } catch (err) {
    document.getElementById('msg').textContent = err.message || 'Registration failed';
  }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

