<?php
require_once __DIR__ . '/../../gframe_src/bootstrap.php';
require_once __DIR__ . '/../../gframe_src/AdminLayout.php';
$stats = Database::getStats();

ob_start();
?>
<?= flash() ?>
<div class="section-header">
  <div class="section-title">Dashboard</div>
</div>

<div class="stats-grid">
  <div class="stat-card" style="--grad:linear-gradient(90deg,#06b6d4,#0891b2)">
    <div class="stat-value"><?= $stats['forms'] ?></div>
    <div class="stat-label">Forms</div>
  </div>
</div>
<?php
$content = ob_get_clean();
adminLayout('dashboard', 'Dashboard', $content);
