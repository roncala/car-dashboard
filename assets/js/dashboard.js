// assets/js/dashboard.js
document.addEventListener('DOMContentLoaded', () => {
  const BASE = window.APP_BASE_URL || '';

  // KPI elements (optional on some pages)
  const kpiTotal = document.getElementById('kpiTotal');
  const kpiAvg   = document.getElementById('kpiAvg');
  const kpiMax   = document.getElementById('kpiMax');
  const kpiAccel = document.getElementById('kpiAccel');

  // Chart canvases (optional)
  const fuelCanvas  = document.getElementById('fuelChart');
  const priceCanvas = document.getElementById('priceChart');

  // If dashboard widgets aren't present, do nothing
  if (!kpiTotal && !fuelCanvas && !priceCanvas) return;

  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, (m) => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
  }

  async function fetchJsonStrict(url) {
    const res = await fetch(url, { credentials: 'same-origin' });
    const ct = (res.headers.get('content-type') || '').toLowerCase();
    const text = await res.text();

    if (!res.ok) {
      // if server returned JSON error, surface it
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

  let fuelChart = null;
  let priceChart = null;

  function safeSet(el, value) {
    if (!el) return;
    el.textContent = value;
  }

  function formatMoney(x) {
    if (x === null || x === undefined || x === '') return '—';
    const n = Number(x);
    if (Number.isNaN(n)) return '—';
    return '$' + n.toLocaleString();
  }

  async function loadStats() {
    try {
      const url = `${BASE}/api/cars/stats.php`;
      const res = await fetchJsonStrict(url);

      // KPIs
      if (res.kpi) {
        safeSet(kpiTotal, res.kpi.total_cars ?? '—');
        safeSet(kpiAvg,   res.kpi.avg_price ? formatMoney(res.kpi.avg_price) : '—');
        safeSet(kpiMax,   res.kpi.max_price ? formatMoney(res.kpi.max_price) : '—');
        safeSet(kpiAccel, res.kpi.best_0_100 ? `${res.kpi.best_0_100} sec` : '—');
      }

      // Charts require Chart.js
      if (typeof Chart === 'undefined') {
        console.warn('Chart.js is not loaded. Charts will not render.');
        return;
      }

      // Fuel distribution chart
      if (fuelCanvas && Array.isArray(res.fuel_distribution)) {
        const fuelLabels = res.fuel_distribution.map(x => x.fuel_type || 'Unknown');
        const fuelData   = res.fuel_distribution.map(x => Number(x.cnt || 0));

        if (fuelChart) fuelChart.destroy();
        fuelChart = new Chart(fuelCanvas, {
          type: 'doughnut',
          data: { labels: fuelLabels, datasets: [{ data: fuelData }] }
        });
      }

      // Top priced chart
      if (priceCanvas && Array.isArray(res.top_priced)) {
        const labels = res.top_priced.map(x => `${x.company_name} ${x.car_name}`.trim());
        const data   = res.top_priced.map(x => Number(x.price || 0));

        if (priceChart) priceChart.destroy();
        priceChart = new Chart(priceCanvas, {
          type: 'bar',
          data: { labels, datasets: [{ data }] },
          options: { plugins: { legend: { display: false } } }
        });
      }

    } catch (e) {
      // Show errors non-intrusively
      console.error(e);
      const msg = `Dashboard stats error: ${escapeHtml(e.message)}`;
      if (kpiTotal) kpiTotal.textContent = '—';
      if (kpiAvg)   kpiAvg.textContent = '—';
      if (kpiMax)   kpiMax.textContent = '—';
      if (kpiAccel) kpiAccel.textContent = '—';

      // Optionally show a message somewhere if you add a div like #statsError
      const statsError = document.getElementById('statsError');
      if (statsError) statsError.textContent = msg;
    }
  }

  loadStats();
});
