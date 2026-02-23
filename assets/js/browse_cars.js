//assets/js/browse_cars.js
document.addEventListener('DOMContentLoaded', () => {
  let page = 1;
  const perPage = 10;

  const qEl = document.getElementById('q');
  const fuelEl = document.getElementById('fuel');
  const btnSearch = document.getElementById('btnSearch');
  const btnClear = document.getElementById('btnClear');

  const carsTable = document.getElementById('carsTable');
  const resultsInfo = document.getElementById('resultsInfo');
  const pageInfo = document.getElementById('pageInfo');

  const btnPrev = document.getElementById('btnPrev');
  const btnNext = document.getElementById('btnNext');

  // If elements not present, do nothing
  if (!qEl || !fuelEl || !btnSearch || !carsTable) return;

  const BASE = window.APP_BASE_URL || ''; // e.g. "/~roncala/car-dashboard"

  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, (m) => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
  }

  async function fetchJson(url) {
    const res = await fetch(url, { credentials: 'same-origin' });
    const ct = (res.headers.get('content-type') || '').toLowerCase();
    const text = await res.text();

    if (!res.ok) {
      throw new Error(`Request failed (${res.status}). ${text.slice(0, 160)}`);
    }
    if (!ct.includes('application/json')) {
      throw new Error(`Expected JSON, got ${ct || 'unknown'}: ${text.slice(0, 160)}`);
    }
    return JSON.parse(text);
  }

  function setLoading(on) {
    if (on) {
      carsTable.innerHTML = '<div class="muted">Loading cars...</div>';
      btnSearch.disabled = true;
      if (btnPrev) btnPrev.disabled = true;
      if (btnNext) btnNext.disabled = true;
    } else {
      btnSearch.disabled = false;
    }
  }

  function renderTable(cars) {
    if (!cars || cars.length === 0) {
      carsTable.innerHTML = '<div class="muted">No cars found.</div>';
      return;
    }

    const rows = cars.map(c => `
      <tr>
        <td>${escapeHtml(c.company_name)}</td>
        <td><a href="${BASE}/public/car.php?id=${c.car_id}">${escapeHtml(c.car_name)}</a></td>
        <td>${escapeHtml(c.fuel_type || '')}</td>
        <td>${c.price != null ? ('$' + Number(c.price).toLocaleString()) : ''}</td>
        <td>${c.horsepower ?? ''}</td>
      </tr>
    `).join('');

    carsTable.innerHTML = `
      <table class="table">
        <thead>
          <tr>
            <th>Company</th>
            <th>Car</th>
            <th>Fuel</th>
            <th>Price</th>
            <th>HP</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    `;
  }

  async function loadCars() {
    setLoading(true);

    const qs = new URLSearchParams();
    const q = qEl.value.trim();
    const fuel = fuelEl.value;

    if (q) qs.set('q', q);          // API searches company_name OR car_name
    if (fuel) qs.set('fuel', fuel);

    qs.set('page', String(page));
    qs.set('per_page', String(perPage));

    const url = `${BASE}/api/cars/list.php?` + qs.toString();

    try {
      const res = await fetchJson(url);
      renderTable(res.cars);

      const total = Number(res.total || 0);
      const totalPages = Math.max(1, Math.ceil(total / perPage));

      if (resultsInfo) resultsInfo.textContent = `Showing page ${page} of ${totalPages} — ${total} result(s).`;
      if (pageInfo) pageInfo.textContent = `Page ${page} / ${totalPages}`;

      if (btnPrev) btnPrev.disabled = (page <= 1);
      if (btnNext) btnNext.disabled = (page >= totalPages);

    } catch (e) {
      carsTable.innerHTML = `<div class="muted">Error loading cars: ${escapeHtml(e.message)}</div>`;
      if (resultsInfo) resultsInfo.textContent = '';
      if (pageInfo) pageInfo.textContent = '';
    } finally {
      btnSearch.disabled = false;
    }
  }

  btnSearch.addEventListener('click', () => {
    page = 1;
    loadCars();
  });

  if (btnClear) {
    btnClear.addEventListener('click', () => {
      qEl.value = '';
      fuelEl.value = '';
      page = 1;
      loadCars();
    });
  }

  qEl.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      page = 1;
      loadCars();
    }
  });

  if (btnPrev) {
    btnPrev.addEventListener('click', () => {
      if (page > 1) { page--; loadCars(); }
    });
  }

  if (btnNext) {
    btnNext.addEventListener('click', () => {
      page++;
      loadCars();
    });
  }

  // Initial load
  loadCars();
});
