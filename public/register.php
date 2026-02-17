<?php
$pageTitle = "Register";
$currentPage = "register";
require_once __DIR__ . '/../partials/header.php';
#small test
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
        <input name="password" id="password" type="password" minlength="6" maxlength="128" required>
      </div>

      <div>
        <label>Confirm Password *</label>
        <input name="password_confirm" id="password_confirm" type="password" minlength="6" maxlength="128" required>
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
  <p class="muted" id="successText">
    Your account has been created successfully. Please log in to continue.
  </p>
  <div class="row">
    <a id="loginLink" class="btnLink" href="#">Go to Login</a>
    <a class="btnLink" href="#" id="createAnother">Create another account</a>
  </div>
</div>

<style>
.btnLink{
  display:inline-block;
  padding:10px 12px;
  border-radius:10px;
  border:1px solid rgba(247,181,0,.35);
  background:rgba(247,181,0,.12);
  text-decoration:none;
}
.btnLink:hover{background:rgba(247,181,0,.18)}
</style>

<script>
(function(){
  const form = document.getElementById('regForm');
  const msg = document.getElementById('msg');
  const btn = document.getElementById('btnCreate');

  const registerCard = document.getElementById('registerCard');
  const successCard = document.getElementById('successCard');
  const successText = document.getElementById('successText');
  const loginLink = document.getElementById('loginLink');
  const createAnother = document.getElementById('createAnother');

  let submitting = false;

  createAnother.addEventListener('click', (e) => {
    e.preventDefault();
    successCard.style.display = 'none';
    registerCard.style.display = 'block';
    msg.textContent = '';
    form.reset();
    btn.disabled = false;
    btn.textContent = 'Create Account';
    submitting = false;
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (submitting) return;

    msg.textContent = '';

    const fd = new FormData(form);
    const data = Object.fromEntries(fd.entries());

    // Required checks
    if (!data.full_name || !data.full_name.trim()) { msg.textContent = 'Full name is required.'; return; }
    if (!data.email || !data.email.trim()) { msg.textContent = 'Email is required.'; return; }
    if (!data.password || data.password.length < 6) { msg.textContent = 'Password must be at least 6 characters.'; return; }
    if (data.password !== data.password_confirm) { msg.textContent = 'Passwords do not match.'; return; }

    // Optional field format checks
    if (data.state && !/^[A-Za-z]{2}$/.test(data.state)) { msg.textContent = 'State must be 2 letters (e.g., NJ).'; return; }
    if (data.zip_code && !/^\d{5}(-\d{4})?$/.test(data.zip_code)) { msg.textContent = 'Zip must be 5 digits (or ZIP+4).'; return; }

    // Do not send confirm password
    delete data.password_confirm;

    // Remove empty optional fields
    for (const k of Object.keys(data)) {
      if (data[k] === '') delete data[k];
    }

    submitting = true;
    btn.disabled = true;
    btn.textContent = 'Creating...';

    try {
      const res = await apiPost('/api/auth/register.php', data);

      // Build login link using base URL
      const base = window.APP_BASE_URL || '';
      const loginPath = (res && res.login_path) ? res.login_path : '/public/login.php';
      loginLink.href = base + loginPath;

      // If API returns debug info, show it (safe)
    //  let extra = '';
    //  if (res && res.user_id) extra += ` (User ID: ${res.user_id})`;
    //  if (res && res.connected_db) extra += ` — DB: ${res.connected_db}`;

      successText.textContent = (res && res.message ? res.message : 'Account created successfully. Please log in.') + extra;

      registerCard.style.display = 'none';
      successCard.style.display = 'block';

    } catch (err) {
      msg.textContent = err && err.message ? err.message : 'Registration failed';
      submitting = false;
      btn.disabled = false;
      btn.textContent = 'Create Account';
      return;
    }

    // keep button disabled because we moved to success card
  });
})();
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

