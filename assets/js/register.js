// assets/js/register.js
document.addEventListener('DOMContentLoaded', () => {
  const BASE = window.APP_BASE_URL || '';

  const form = document.getElementById('regForm');
  const msg  = document.getElementById('msg');
  const btn  = document.getElementById('btnCreate');

  const registerCard = document.getElementById('registerCard');
  const successCard  = document.getElementById('successCard');
  const loginLink    = document.getElementById('loginLink');
  const successText  = document.getElementById('successText');

  if (!form || !msg || !btn || !registerCard || !successCard || !loginLink || !successText) return;

  function csrfToken() {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : '';
  }

  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, (m) => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
  }

  async function fetchJsonStrict(url, options = {}) {
    const res = await fetch(url, { credentials: 'same-origin', ...options });
    const ct = (res.headers.get('content-type') || '').toLowerCase();
    const text = await res.text();

    let data = null;
    if (ct.includes('application/json')) {
      try { data = JSON.parse(text); } catch { data = null; }
    }

    if (!res.ok) {
      const message = (data && data.error) ? data.error : `Request failed (${res.status})`;
      throw new Error(message);
    }

    if (!ct.includes('application/json')) {
      throw new Error(`Expected JSON but got "${ct || 'unknown'}". Response starts: ${text.slice(0, 160)}`);
    }

    return data || {};
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    msg.textContent = '';

    const fd = new FormData(form);
    const data = Object.fromEntries(fd.entries());

    // Required checks
    if (!data.full_name || !data.full_name.trim()) { msg.textContent = 'Full name is required.'; return; }
    if (!data.email || !data.email.trim()) { msg.textContent = 'Email is required.'; return; }
    if (!data.password || data.password.length < 6) { msg.textContent = 'Password must be at least 6 characters.'; return; }
    if (data.password !== data.password_confirm) { msg.textContent = 'Passwords do not match.'; return; }

    // Optional format checks
    if (data.state && !/^[A-Za-z]{2}$/.test(data.state)) { msg.textContent = 'State must be 2 letters (e.g., NJ).'; return; }
    if (data.zip_code && !/^\d{5}(-\d{4})?$/.test(data.zip_code)) { msg.textContent = 'Zip must be 5 digits (or ZIP+4).'; return; }

    // do not send confirm password
    delete data.password_confirm;

    // remove empty optional fields
    for (const k of Object.keys(data)) {
      if (data[k] === '') delete data[k];
    }

    btn.disabled = true;
    btn.textContent = 'Creating...';

    try {
      const res = await fetchJsonStrict(`${BASE}/api/auth/register.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken()
        },
        body: JSON.stringify(data)
      });

      const loginPath = (res && res.login_path) ? res.login_path : '/public/login.php';
      loginLink.href = `${BASE}${loginPath}`;
      successText.textContent = (res && res.message) ? res.message : 'Account created successfully. Please log in.';

      registerCard.style.display = 'none';
      successCard.style.display  = 'block';

    } catch (err) {
	    const text = (err && err.message) ? err.message : 'Registration failed';
	    if (text.toLowerCase().includes('email already exists')) {
		    msg.innerHTML = `Email already exists. Please use a different email or <a href="${BASE}/public/login.php">log in</a>.`;
	    } else {
		    msg.innerHTML = escapeHtml(text);
	    }      
	    btn.disabled = false;
      btn.textContent = 'Create Account';
    }
  });
});
