document.getElementById('btnCompare').addEventListener('click', async () => {
  const ids = document.getElementById('compareIds').value.trim();
  if (!ids) return;

  try {
    const res = await apiGet('/api/compare/get.php?ids=' + encodeURIComponent(ids));
    const cars = res.cars;

    if (!cars.length) {
      document.getElementById('compareOut').textContent = 'No cars returned.';
      return;
    }

    const cols = cars.map(c => `<th>${escapeHtml(c.company_name)}<br>${escapeHtml(c.car_name)}</th>`).join('');

    const row = (label, key) => `
      <tr>
        <td class="muted"><b>${label}</b></td>
        ${cars.map(c => `<td>${escapeHtml(c[key] ?? '')}</td>`).join('')}
      </tr>
    `;

    document.getElementById('compareOut').innerHTML = `
      <table class="table">
        <thead><tr><th>Spec</th>${cols}</tr></thead>
        <tbody>
          ${row('Price', 'price')}
          ${row('Fuel Type', 'fuel_type')}
          ${row('Engine', 'engine')}
          ${row('CC/Battery', 'cc_battery_capacity')}
          ${row('Horsepower', 'horsepower')}
          ${row('Top Speed', 'total_speed')}
          ${row('0–100', 'accel_0_100')}
          ${row('Seats', 'seats')}
          ${row('Torque', 'torque')}
        </tbody>
      </table>
    `;
  } catch (e) {
    document.getElementById('compareOut').textContent = e.message;
  }
});

