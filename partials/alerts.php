<?php
// Optional flash message support
require_once __DIR__ . '/../app/security.php';
start_secure_session();

if (!empty($_SESSION['flash'])):
  $msg = $_SESSION['flash'];
  unset($_SESSION['flash']);
?>
<div class="alert"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

