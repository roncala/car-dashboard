<?php
$pageTitle = "Compare";
$currentPage = "compare";
require_once __DIR__ . '/../partials/header.php';
?>

<h1>Compare (up to 4 cars)</h1>

<div class="card">
  <div class="filters">
    <input id="compareIds" placeholder="Enter car IDs e.g. 1,2,3,4">
    <button id="btnCompare">Compare</button>
  </div>
  <div id="compareOut"></div>
</div>

<script defer src="../assets/js/compare.js"></script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

