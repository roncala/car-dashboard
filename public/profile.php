<?php
$pageTitle = "Profile";
$currentPage = "profile";
require_once __DIR__ . '/../partials/header.php';

if (empty($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$base = getenv('APP_BASE_URL') ?: '';
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
        <input name="state" maxlength="2" placeholder="NJ">
      </div>

      <div>
        <label>Zip Code</label>
        <input name="zip_code" maxlength="10" placeholder="07083">
      </div>

      <div>
        <label>Country</label>
        <input name="country" value="USA">
      </div>
    </div>

    <hr>

    <label>Change Password (optional)</label>
    <input name="password_current" type="password" placeholder="Current password">
    <input name="password_new" type="password" placeholder="New password (min 6)">

    <button id="btnSave" type="submit">Save</button>
  </form>

  <div id="msg" class="muted" style="margin-top:10px;"></div>
</div>

<script defer src="<?= $base ?>/assets/js/profile.js?v=1"></script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
