<?php
$pageTitle = "Profile";
$currentPage = "profile";
require_once __DIR__ . '/../partials/header.php';

if (empty($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
?>

<h1>Profile</h1>

<div class="card">
  <form id="profileForm">
    <div class="grid form-grid">
      <div>
        <label>Full Name</label>
        <input name="full_name">
      </div>
      <div>
        <label>Email</label>
        <input name="email" type="email">
      </div>
      <div>
        <label>Phone</label>
        <input name="phone">
      </div>

      <div>
        <label>Address Line 1</label>
        <input name="address_line1">
      </div>
      <div>
        <label>Address Line 2</label>
        <input name="address_line2">
      </div>
      <div>
        <label>City</label>
        <input name="city">
      </div>
      <div>
        <label>State</label>
        <input name="state">
      </div>
      <div>
        <label>Zip Code</label>
        <input name="zip_code">
      </div>
      <div>
        <label>Country</label>
        <input name="country">
      </div>
    </div>

    <hr>

    <label>Change Password (optional)</label>
    <input name="password_current" type="password" placeholder="Current password">
    <input name="password_new" type="password" placeholder="New password (min 6)">

    <button type="submit">Save</button>
  </form>

  <div id="msg"></div>
</div>

<script>
(async function(){
  try {
    const res = await apiGet('/api/users/me_get.php');
    const u = res.user;
    const form = document.getElementById('profileForm');
    for (const k in u) {
      if (form.elements[k]) form.elements[k].value = u[k] ?? '';
    }
  } catch (e) {
    document.getElementById('msg').textContent = e.message;
  }
})();

document.getElementById('profileForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const data = Object.fromEntries(new FormData(e.target).entries());
  // remove empty password fields
  if (!data.password_current) delete data.password_current;
  if (!data.password_new) delete data.password_new;

  try {
    await apiPost('/api/users/me_patch.php', data);
    document.getElementById('msg').textContent = 'Saved.';
  } catch (err) {
    document.getElementById('msg').textContent = err.message || 'Update failed';
  }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

