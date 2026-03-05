<?php
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/AdminLayout.php';
Auth::requireAdmin();

$stats = Database::getStats();
$log   = Database::getAccessLog();

ob_start();
?>
<?= flash() ?>
<div class="section-header">
  <div class="section-title">Dashboard</div>
</div>

<div class="stats-grid">
  <div class="stat-card" style="--grad:linear-gradient(90deg,#6366f1,#818cf8)">
    <div class="stat-value"><?= $stats['students'] ?></div>
    <div class="stat-label">Students</div>
  </div>
  <div class="stat-card" style="--grad:linear-gradient(90deg,#06b6d4,#0891b2)">
    <div class="stat-value"><?= $stats['forms'] ?></div>
    <div class="stat-label">Forms</div>
  </div>
  <div class="stat-card" style="--grad:linear-gradient(90deg,#22c55e,#16a34a)">
    <div class="stat-value"><?= $stats['opens'] ?></div>
    <div class="stat-label">Total Opens</div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary-h)" stroke-width="2">
        <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/>
      </svg>
      Recent Access Log
    </div>
    <a href="/admin/log.php" class="btn btn-ghost btn-sm ml-auto">View All</a>
  </div>
  <?php if (empty($log)): ?>
    <div class="empty-state"><p>No access records yet.</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Student</th><th>Form</th><th>Opened At</th></tr></thead>
        <tbody>
        <?php foreach (array_slice($log, 0, 10) as $row): ?>
          <tr>
            <td><strong><?= h($row['full_name']) ?></strong><br><span class="text-muted" style="font-size:.75rem"><?= h($row['username']) ?></span></td>
            <td><?= h($row['form_name']) ?></td>
            <td class="text-dim" style="font-size:.8rem"><?= h($row['opened_at']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
adminLayout('dashboard', 'Dashboard', $content);
