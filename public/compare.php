<?php
$pageTitle = "Compare";
$currentPage = "compare";
require_once __DIR__ . '/../partials/header.php';
$base = getenv('APP_BASE_URL') ?: '';
?>

<h1>Compare (select up to 4 cars)</h1>

<div class="card">
  <label>Select cars (search by company or car name)</label>
  <select id="carSelect" multiple style="width:100%"></select>

  <div class="row" style="margin-top:12px;">
    <button id="btnCompare" type="button">Compare</button>
    <button id="btnClearCompare" type="button">Clear</button>
  </div>

  <div class="muted" id="compareMsg" style="margin-top:10px;"></div>
  <div id="compareOut" style="margin-top:10px;"></div>
</div>

<!-- Select2 + jQuery (CDN) -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Dark theme tweaks for Select2 -->
<style>
  .select2-container--default .select2-selection--multiple{
    background:#0f1620;
    border:1px solid #1f2a36;
    border-radius:12px;
    min-height:44px;
    padding:6px 8px;
  }
  .select2-container--default .select2-selection--multiple .select2-selection__choice{
    background:rgba(247,181,0,.12);
    border:1px solid rgba(247,181,0,.25);
    color:#e6eef7;
  }
  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove{
    color:#e6eef7;
  }
  .select2-container--default .select2-search--inline .select2-search__field{
    color:#e6eef7;
  }
  .select2-dropdown{
    background:#121a22;
    border:1px solid #1f2a36;
  }
  .select2-container--default .select2-results__option{
    color:#e6eef7;
  }
  .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable{
    background:rgba(247,181,0,.18);
    color:#e6eef7;
  }
</style>

<script defer src="<?= $base ?>/assets/js/compare.js?v=3"></script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>:
