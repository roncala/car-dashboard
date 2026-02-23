<?php
$pageTitle = "Favorites";
$currentPage = "favorites";
require_once __DIR__ . '/../partials/header.php';

if (empty($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
$base = getenv('APP_BASE_URL') ?: '';
?>

<h1>Favorites</h1>

<div class="card">
  <div id="favList">Loading...</div>
</div>

<script defer src="../assets/js/favorites.js?v=2"></script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>

