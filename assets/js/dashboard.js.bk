let fuelChart, priceChart;

async function loadStats() {
  const res = await apiGet('/api/cars/stats.php');

  document.getElementById('kpiTotal').textContent = res.kpi.total_cars ?? '—';
  document.getElementById('kpiAvg').textContent = res.kpi.avg_price ? ('$' + Number(res.kpi.avg_price).toLocaleString()) : '—';
  document.getElementById('kpiMax').textContent = res.kpi.max_price ? ('$' + Number(res.kpi.max_price).toLocaleString()) : '—';
  document.getElementById('kpiAccel').textContent = res.kpi.best_0_100 ? (res.kpi.best_0_100 + ' sec') : '—';

  const fuelLabels = res.fuel_distribution.map(x => x.fuel_type || 'Unknown');
  const fuelData = res.fuel_distribution.map(x => x.cnt);

  if (fuelChart) fuelChart.destroy();
  fuelChart = new Chart(document.getElementById('fuelChart'), {
    type: 'doughnut',
    data: { labels: fuelLabels, datasets: [{ data: fuelData }] }
  });

  const pLabels = res.top_priced.map(x => `${x.company_name} ${x.car_name}`);
  const pData = res.top_priced.map(x => Number(x.price));

  if (priceChart) priceChart.destroy();
  priceChart = new Chart(document.getElementById('priceChart'), {
    type: 'bar',
    data: { labels: pLabels, datasets: [{ data: pData }] },
    options: { plugins: { legend: { display: false } } }
  });
}

async function loadCars() {
  const q = document.getElementById('q').value.trim();
  const fuel = document.getElementById('fuel').value;

  const qs = new URLSearchParams();
  if (q) qs.set('q', q);
  if (fuel) qs.set('fuel', fuel);
  qs.set('per_page', '10');
  qs.set('page', '1');

  const res = await apiGet('/api/cars/list.php?' + qs.toString());

  const rows = res.cars.map(c => `
    <tr>
      <td>${escapeHtml(c.company_name)}</td>
      <td><a href="car.php?id=${c.car_id}">${escapeHtml(c.car_name)}</a></td>
      <td>${escapeHtml(c.fuel_type || '')}</td>
      <td>$${Number(c.price).toLocaleString()}</td>
      <td>${c.horsepower ?? ''}</td>
      <td>
        <button onclick="fav(${c.car_id})">Fav</button>
      </td>
    </tr>
  `).join('');

  document.getElementById('carsTable').innerHTML = `
    <table class="table">
      <thead>
        <tr>
          <th>Company</th><th>Car</th><th>Fuel</th><th>Price</th><th>HP</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>${rows}</tbody>
    </table>
  `;
}

async function fav(carId){
  try {
    await apiPost('/api/favorites/add.php', { car_id: carId });
    alert('Added to favorites');
  } catch(e){
    alert(e.message);
  }
}

document.getElementById('btnSearch').addEventListener('click', loadCars);

loadStats();
loadCars();

