<?php
$pageTitle = "Register";
$currentPage = "register";
require_once __DIR__ . '/../partials/header.php';
$base = getenv('APP_BASE_URL') ?: '';
?>

<h1>Create Account</h1>

<div class="card" id="registerCard">
  <form id="regForm" autocomplete="off">
    <div class="grid form-grid">
      <div>
        <label>Full Name *</label>
        <input name="full_name" maxlength="100" required>
      </div>

      <div>
        <label>Email *</label>
        <input name="email" type="email" maxlength="150" required>
      </div>

      <div>
        <label>Password *</label>
        <input name="password" type="password" minlength="6" maxlength="128" required>
      </div>

      <div>
        <label>Confirm Password *</label>
        <input name="password_confirm" type="password" minlength="6" maxlength="128" required>
      </div>

      <div>
        <label>Phone</label>
        <input name="phone" maxlength="25" placeholder="e.g., 555-123-4567"
               pattern="[0-9+\-\s().]{7,25}">
      </div>

      <div>
        <label>Address Line 1</label>
        <input name="address_line1" maxlength="150">
      </div>

      <div>
        <label>Address Line 2</label>
        <input name="address_line2" maxlength="150">
      </div>

      <div>
        <label>City</label>
        <input name="city" maxlength="80">
      </div>

      <div>
        <label>State (2 letters)</label>
        <input name="state" maxlength="2" placeholder="NJ" pattern="[A-Za-z]{2}">
      </div>

      <div>
        <label>Zip Code</label>
        <input name="zip_code" maxlength="10" placeholder="07083" pattern="\d{5}(-\d{4})?">
      </div>

      <div>
        <label>Country</label>
        <input name="country" maxlength="60" value="USA">
      </div>
    </div>

    <button id="btnCreate" type="submit">Create Account</button>
  </form>

  <div id="msg" class="muted" style="margin-top:10px;"></div>
</div>

<div class="card" id="successCard" style="display:none;">
  <h2>Account created ✅</h2>
  <p class="muted" id="successText">Account created successfully. Please log in.</p>
  <div class="row">
    <a id="loginLink" class="btnLink" href="#">Go to Login</a>
  </div>
</div>

<script defer src="<?= $base ?>/assets/js/register.js?v=2"></script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
