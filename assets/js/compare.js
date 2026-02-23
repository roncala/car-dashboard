// assets/js/compare.js
document.addEventListener('DOMContentLoaded', () => {
  const BASE = window.APP_BASE_URL || '';

  const selectEl = document.getElementById('carSelect');
  const btnCompare = document.getElementById('btnCompare');
  const btnClear = document.getElementById('btnClearCompare');
  const msgEl = document.getElementById('compareMsg');
  const outEl = document.getElementById('compareOut');

  if (!selectEl || !btnCompare || !outEl) return;

  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, (m) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[m]));
  }

  async function fetchJsonStrict(url) {
    const res = await fetch(url, { credentials: 'same-origin' });
    const ct = (res.headers.get('content-type') || '').toLowerCase();
    const text = await res.text();

    if (!res.ok) {
      if (ct.includes('application/json')) {
        try {
          const data = JSON.parse(text);
          throw new Error(data.error || `Request failed (${res.status})`);
        } catch {}
      }
      throw new Error(`Request failed (${res.status}): ${text.slice(0, 160)}`);
    }

    if (!ct.includes('application/json')) {
      throw new Error(`Expected JSON but got "${ct || 'unknown'}". Response starts: ${text.slice(0, 160)}`);
    }

    return JSON.parse(text);
  }

  function money(v) {
    if (v === null || v === undefined || v === '') return '';
    const n = Number(v);
    if (Number.isNaN(n)) return String(v);
    return '$' + n.toLocaleString();
  }

  function row(label, cars, key, formatter = null) {
    return `
      <tr>
        <td class="muted"><b>${escapeHtml(label)}</b></td>
        ${cars.map(c => {
          const val = c[key];
          const out = formatter ? formatter(val, c) : (val ?? '');
          return `<td>${escapeHtml(out)}</td>`;
        }).join('')}
      </tr>
    `;
  }

  // ----- Select2 init -----
  if (!window.jQuery || !jQuery.fn || !jQuery.fn.select2) {
    msgEl.textContent = 'Select2 failed to load. Check CDN/network.';
    return;
  }

  const $sel = jQuery('#carSelect');

  $sel.select2({
    placeholder: 'Type to search (e.g., Toyota, Civic, Bugatti)',
    width: 'resolve',
    multiple: true,
    maximumSelectionLength: 4,
    minimumInputLength: 1,
    ajax: {
      url: `${BASE}/api/cars/list.php`,
      dataType: 'json',
      delay: 250,
      data: (params) => ({
        q: params.term || '',
        page: 1,
        per_page: 20
      }),
      processResults: (data) => ({
        results: (data.cars || []).map(c => ({
          id: String(c.car_id),
          text: `${c.company_name} — ${c.car_name}`,
          // extra fields for nice display
          meta: {
            fuel: c.fuel_type,
            price: c.price,
            hp: c.horsepower
          }
        }))
      })
    },
    templateResult: (item) => {
      if (!item.id) return item.text;
      const m = item.meta || {};
      const sub = `${m.fuel || ''}${m.hp ? ' • ' + m.hp + ' hp' : ''}${m.price ? ' • $' + Number(m.price).toLocaleString() : ''}`;
      const div = document.createElement('div');
      div.innerHTML = `<div>${escapeHtml(item.text)}</div><div class="muted" style="font-size:12px;">${escapeHtml(sub)}</div>`;
      return div;
    }
  });

  function getSelectedIds() {
    const ids = $sel.val() || [];
    // enforce max 4 (Select2 should handle this but extra safety)
    return ids.slice(0, 4);
  }

  async function runCompare() {
    msgEl.textContent = '';
    outEl.innerHTML = '';

    const ids = getSelectedIds();
    if (ids.length < 2) {
      msgEl.textContent = 'Select at least 2 cars (max 4).';
      return;
    }

    btnCompare.disabled = true;
    btnClear.disabled = true;
    outEl.innerHTML = '<div class="muted">Loading comparison...</div>';

    try {
      const url = `${BASE}/api/compare/get.php?ids=${encodeURIComponent(ids.join(','))}`;
      const res = await fetchJsonStrict(url);
      const cars = res.cars || [];

      if (!cars.length) {
        outEl.innerHTML = '<div class="muted">No cars returned.</div>';
        return;
      }

      const cols = cars.map(c => `
        <th>
          <div>${escapeHtml(c.company_name || '')}</div>
          <div class="muted">${escapeHtml(c.car_name || '')}</div>
          <div style="margin-top:6px;">
            <a class="btnLink" href="${BASE}/public/car.php?id=${c.car_id}">Details</a>
          </div>
        </th>
      `).join('');

      outEl.innerHTML = `
        <table class="table">
          <thead>
            <tr><th>Spec</th>${cols}</tr>
          </thead>
          <tbody>
            ${row('Price', cars, 'price', money)}
            ${row('Fuel Type', cars, 'fuel_type')}
            ${row('Engine', cars, 'engine')}
            ${row('CC/Battery', cars, 'cc_battery_capacity')}
            ${row('Horsepower', cars, 'horsepower')}
            ${row('Top Speed', cars, 'total_speed', v => v ? `${v} km/h` : '')}
            ${row('0–100', cars, 'accel_0_100', v => v ? `${v} sec` : '')}
            ${row('Seats', cars, 'seats')}
            ${row('Torque', cars, 'torque')}
          </tbody>
        </table>
      `;

    } catch (e) {
      outEl.innerHTML = `<div class="muted">Error: ${escapeHtml(e.message)}</div>`;
    } finally {
      btnCompare.disabled = false;
      btnClear.disabled = false;
    }
  }

  btnCompare.addEventListener('click', runCompare);

  btnClear.addEventListener('click', () => {
    $sel.val(null).trigger('change');
    msgEl.textContent = '';
    outEl.innerHTML = '';
  });
});
