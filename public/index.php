<?php
$pageTitle = "Dashboard";
$currentPage = "dashboard";
require_once __DIR__ . '/../partials/header.php';
?>

<h1>Dashboard</h1>

<div class="grid kpis">
  <div class="card"><div class="label">Total Cars</div><div id="kpiTotal" class="value">—</div></div>
  <div class="card"><div class="label">Avg Price</div><div id="kpiAvg" class="value">—</div></div>
  <div class="card"><div class="label">Max Price</div><div id="kpiMax" class="value">—</div></div>
  <div class="card"><div class="label">Best 0–100</div><div id="kpiAccel" class="value">—</div></div>
</div>

<div class="grid charts">
  <div class="card">
    <h3>Fuel Type Distribution</h3>
    <canvas id="fuelChart"></canvas>
  </div>
  <div class="card">
    <h3>Top 10 Highest Priced</h3>
    <canvas id="priceChart"></canvas>
  </div>
</div>

<div class="card">
  <h3>Browse Cars</h3>

  <div class="filters">
    <input id="q" placeholder="Search (company or car name)">
    <select id="fuel">
      <option value="">All fuels</option>
      <option>Petrol</option>
      <option>Diesel</option>
      <option>Hybrid</option>
      <option>Plug-in Hybrid</option>
      <option>Electric</option>
    </select>
    <button id="btnSearch">Search</button>
  </div>

  <div id="carsTable"></div>
</div>

<script defer src="../assets/js/dashboard.js"></script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

