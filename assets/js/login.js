// assets/js/login.js
document.addEventListener('DOMContentLoaded', () => {
  const BASE = window.APP_BASE_URL || '';

  const form = document.getElementById('loginForm');
  const msg  = document.getElementById('msg');
  const btn  = document.getElementById('btnLogin');

  if (!form || !msg || !btn) return;

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
    const email = (fd.get('email') || '').toString().trim();
    const password = (fd.get('password') || '').toString();

    if (!email) { msg.textContent = 'Email is required.'; return; }
    if (!password) { msg.textContent = 'Password is required.'; return; }

    btn.disabled = true;
    btn.textContent = 'Signing in...';

    try {
      await fetchJsonStrict(`${BASE}/api/auth/login.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken()
        },
        body: JSON.stringify({ email, password })
      });

      window.location.href = `${BASE}/public/index.php`;
    } catch (err) {
      msg.innerHTML = escapeHtml(err.message || 'Login failed');
      btn.disabled = false;
      btn.textContent = 'Login';
    }
  });
});
