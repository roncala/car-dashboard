<?php
$pageTitle = "Car Details";
$currentPage = "";
require_once __DIR__ . '/../partials/header.php';

$base = getenv('APP_BASE_URL') ?: '';
?>

<h1>Car Details</h1>

<div class="card" id="carOut">Loading...</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const base = window.APP_BASE_URL || "<?= $base ?>";
  const carOut = document.getElementById('carOut');

  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, (m) => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
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
      const err = new Error(msg);
      err.status = res.status;
      throw err;
    }

    if (!ct.includes('application/json')) {
      throw new Error(`Expected JSON but got "${ct || 'unknown'}". Response starts: ${text.slice(0, 160)}`);
    }

    return data || {};
  }

  async function loadCar() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');

    if (!id) {
      carOut.textContent = 'Missing car id in URL (expected ?id=123).';
      return;
    }

    try {
      const res = await fetchJsonStrict(`${base}/api/cars/detail.php?id=${encodeURIComponent(id)}`);
      const c = res.car;

      carOut.innerHTML = `
        <div class="row" style="justify-content:space-between; align-items:flex-start;">
          <div>
            <h2 style="margin:0;">${escapeHtml(c.company_name)} - ${escapeHtml(c.car_name)}</h2>
            <p class="muted" style="margin:6px 0 0;">
              ${escapeHtml(c.engine || '')} • ${escapeHtml(c.fuel_type || '')}
            </p>
          </div>
          <div id="favBadge" class="muted" style="padding:8px 10px; border:1px solid var(--border); border-radius:10px;">
            Checking favorite…
          </div>
        </div>

        <div class="grid kpis" style="margin-top:12px;">
          <div class="card"><div class="label">Price</div><div class="value">$${Number(c.price || 0).toLocaleString()}</div></div>
          <div class="card"><div class="label">Horsepower</div><div class="value">${c.horsepower ?? '—'}</div></div>
          <div class="card"><div class="label">Top Speed</div><div class="value">${c.total_speed ?? '—'} km/h</div></div>
          <div class="card"><div class="label">0–100</div><div class="value">${c.accel_0_100 ?? '—'} sec</div></div>
        </div>

        <hr>
        <p><b>CC/Battery:</b> ${escapeHtml(c.cc_battery_capacity || '')}</p>
        <p><b>Seats:</b> ${c.seats ?? ''}</p>
        <p><b>Torque:</b> ${escapeHtml(c.torque || '')}</p>

        <div class="row" style="margin-top:12px;">
          <button id="btnFav" type="button">Add to Favorites</button>
          <a class="btnLink" style="margin-left:8px;" href="${base}/public/favorites.php">View Favorites</a>
        </div>

        <div class="muted" id="favMsg" style="margin-top:10px;"></div>
      `;

      const favBadge = document.getElementById('favBadge');
      const btnFav = document.getElementById('btnFav');
      const favMsg = document.getElementById('favMsg');

      let isFavorite = false;

      // Check favorite status (if not logged in, API returns 401)
      try {
        const favRes = await fetchJsonStrict(`${base}/api/favorites/is_favorite.php?car_id=${encodeURIComponent(c.car_id)}`);
        isFavorite = !!favRes.is_favorite;
      } catch (e) {
        if (e.status === 401) {
          // not logged in
          favBadge.textContent = 'Log in to use favorites';
          btnFav.disabled = true;
          favMsg.innerHTML = `Please <a href="${base}/public/login.php">log in</a> to add favorites.`;
          return;
        }
        // other error
        favBadge.textContent = 'Favorite status unavailable';
        console.error(e);
      }

      function updateFavUI() {
        if (isFavorite) {
          favBadge.textContent = '⭐ Favorited';
          btnFav.textContent = 'Remove from Favorites';
        } else {
          favBadge.textContent = 'Not favorited';
          btnFav.textContent = 'Add to Favorites';
        }
      }

      updateFavUI();

      btnFav.addEventListener('click', async () => {
        favMsg.textContent = '';
        btnFav.disabled = true;

        try {
          if (!isFavorite) {
            await fetchJsonStrict(`${base}/api/favorites/add.php`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken()
              },
              body: JSON.stringify({ car_id: c.car_id })
            });
            isFavorite = true;
            favMsg.textContent = 'Added to favorites ✅';
          } else {
            await fetchJsonStrict(`${base}/api/favorites/delete.php`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken()
              },
              body: JSON.stringify({ car_id: c.car_id })
            });
            isFavorite = false;
            favMsg.textContent = 'Removed from favorites ✅';
          }

          updateFavUI();
        } catch (e) {
          favMsg.textContent = e.message;
        } finally {
          btnFav.disabled = false;
        }
      });

    } catch (e) {
      carOut.innerHTML = `<div class="muted">Error: ${escapeHtml(e.message)}</div>`;
    }
  }

  loadCar();
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
