<?php
$pageTitle = "Car Details";
$currentPage = "";
require_once __DIR__ . '/../partials/header.php';
?>

<h1>Car Details</h1>
<div class="card" id="carOut">Loading...</div>

<script>
(async function(){
  const params = new URLSearchParams(window.location.search);
  const id = params.get('id');
  if (!id) { document.getElementById('carOut').textContent = 'Missing id'; return; }

  try {
    const res = await apiGet('/api/cars/detail.php?id=' + encodeURIComponent(id));
    const c = res.car;

    document.getElementById('carOut').innerHTML = `
      <h2>${escapeHtml(c.company_name)} - ${escapeHtml(c.car_name)}</h2>
      <p class="muted">${escapeHtml(c.engine || '')} • ${escapeHtml(c.fuel_type || '')}</p>
      <div class="grid kpis">
        <div class="card"><div class="label">Price</div><div class="value">$${Number(c.price).toLocaleString()}</div></div>
        <div class="card"><div class="label">HP</div><div class="value">${c.horsepower ?? '—'}</div></div>
        <div class="card"><div class="label">Top Speed</div><div class="value">${c.total_speed ?? '—'} km/h</div></div>
        <div class="card"><div class="label">0–100</div><div class="value">${c.accel_0_100 ?? '—'} sec</div></div>
      </div>
      <hr>
      <p><b>CC/Battery:</b> ${escapeHtml(c.cc_battery_capacity || '')}</p>
      <p><b>Seats:</b> ${c.seats ?? ''}</p>
      <p><b>Torque:</b> ${escapeHtml(c.torque || '')}</p>
      <div class="row">
        <button onclick="addFav(${c.car_id})">Add to Favorites</button>
      </div>
    `;
  } catch (e) {
    document.getElementById('carOut').textContent = e.message;
  }
})();

async function addFav(carId){
  try {
    await apiPost('/api/favorites/add.php', { car_id: carId });
    alert('Added to favorites');
  } catch(e){
    alert(e.message);
  }
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

