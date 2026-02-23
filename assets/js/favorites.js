// assets/js/favorites.js
document.addEventListener('DOMContentLoaded', () => {
  const BASE = window.APP_BASE_URL || '';
  const out = document.getElementById('favList');

  if (!out) return;

  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, (m) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[m]));
  }

  function csrfToken() {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : '';
  }

  async function fetchJsonStrict(url, options = {}) {
    const res = await fetch(url, { credentials: 'same-origin', ...options });
    const ct = (res.headers.get('content-type') || '').toLowerCase();
    const text = await res.text();

    // try parse json if possible
    let data = null;
    if (ct.includes('application/json')) {
      try { data = JSON.parse(text); } catch { data = null; }
    }

    if (!res.ok) {
      const msg = (data && data.error) ? data.error : `Request failed (${res.status})`;
      throw new Error(msg);
    }

    if (!ct.includes('application/json')) {
      throw new Error(`Expected JSON but got "${ct || 'unknown'}". Response starts: ${text.slice(0, 160)}`);
    }

    return data || {};
  }

  async function loadFavs() {
    out.innerHTML = '<div class="muted">Loading...</div>';

    try {
      const res = await fetchJsonStrict(`${BASE}/api/favorites/list.php`);
      const favs = res.favorites || [];

      if (!favs.length) {
        out.innerHTML = '<div class="muted">No favorites yet.</div>';
        return;
      }

      out.innerHTML = `
        <table class="table">
          <thead>
            <tr>
              <th>Company</th>
              <th>Car</th>
              <th>Fuel</th>
              <th>Price</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            ${favs.map(c => `
              <tr>
                <td>${escapeHtml(c.company_name)}</td>
                <td><a href="${BASE}/public/car.php?id=${c.car_id}">${escapeHtml(c.car_name)}</a></td>
                <td>${escapeHtml(c.fuel_type || '')}</td>
                <td>${c.price != null ? ('$' + Number(c.price).toLocaleString()) : ''}</td>
                <td><button type="button" data-remove="${c.car_id}">Remove</button></td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      `;

    } catch (e) {
      // If they are not logged in, API returns 401; show a nice message
      const msg = escapeHtml(e.message);
      out.innerHTML = `
        <div class="muted">Error: ${msg}</div>
        <div style="margin-top:10px;">
          <a class="btnLink" href="${BASE}/public/login.php">Go to Login</a>
        </div>
      `;
    }
  }

  // Event delegation for remove buttons
  out.addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-remove]');
    if (!btn) return;

    const carId = btn.getAttribute('data-remove');
    if (!carId) return;

    btn.disabled = true;

    try {
      await fetchJsonStrict(`${BASE}/api/favorites/delete.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken()
        },
        body: JSON.stringify({ car_id: Number(carId) })
      });
      await loadFavs();
    } catch (err) {
      alert(err.message);
      btn.disabled = false;
    }
  });

  loadFavs();
});
