// assets/js/profile.js
document.addEventListener('DOMContentLoaded', () => {
  const BASE = window.APP_BASE_URL || '';

  const form = document.getElementById('profileForm');
  const msg  = document.getElementById('msg');
  const btn  = document.getElementById('btnSave');

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
      const err = new Error(message);
      err.status = res.status;
      throw err;
    }

    if (!ct.includes('application/json')) {
      throw new Error(`Expected JSON but got "${ct || 'unknown'}". Response starts: ${text.slice(0, 160)}`);
    }

    return data || {};
  }

  async function loadProfile() {
    msg.textContent = 'Loading...';
    try {
      const res = await fetchJsonStrict(`${BASE}/api/users/me_get.php`);
      const u = res.user || {};
      for (const k in u) {
        if (form.elements[k]) form.elements[k].value = u[k] ?? '';
      }
      msg.textContent = '';
    } catch (e) {
      if (e.status === 401) {
        msg.innerHTML = `Please <a href="${BASE}/public/login.php">log in</a> to view your profile.`;
      } else {
        msg.textContent = e.message;
      }
    }
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    msg.textContent = '';
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const fd = new FormData(form);
    const data = Object.fromEntries(fd.entries());

    // remove empty fields so you don't overwrite with blanks
    for (const k of Object.keys(data)) {
      if (data[k] === '') delete data[k];
    }

    // password handling
    if ((data.password_current && !data.password_new) || (!data.password_current && data.password_new)) {
      msg.textContent = 'To change password, provide both current and new password.';
      btn.disabled = false;
      btn.textContent = 'Save';
      return;
    }

    try {
      await fetchJsonStrict(`${BASE}/api/users/me_patch.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken()
        },
        body: JSON.stringify(data)
      });

      msg.textContent = 'Profile saved ✅';

      // clear password fields after save
      if (form.elements.password_current) form.elements.password_current.value = '';
      if (form.elements.password_new) form.elements.password_new.value = '';

    } catch (e2) {
      msg.textContent = e2.message;
    } finally {
      btn.disabled = false;
      btn.textContent = 'Save';
    }
  });

  loadProfile();
});
