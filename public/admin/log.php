<?php
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/AdminLayout.php';
Auth::requireAdmin();

$log = Database::getAccessLog();
csrf();
ob_start();
?>
<?= flash() ?>
<div class="section-header">
  <div class="section-title">Access Log</div>
  <span class="badge badge-info"><?= count($log) ?> records (last 200)</span>
</div>

<?php if (empty($log)): ?>
  <div class="empty-state">
    <svg width="64" height="64" viewBox="0 0 24 24" fill="var(--text-muted)">
      <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/>
    </svg>
    <p>No access records found.</p>
  </div>
<?php else: ?>
<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Timestamp</th>
        <th>Student Name</th>
        <th>Username</th>
        <th>Form</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($log as $i => $row): ?>
      <tr>
        <td class="text-muted" style="font-size:.78rem"><?= $i+1 ?></td>
        <td class="text-dim" style="font-size:.82rem;white-space:nowrap"><?= h($row['opened_at']) ?></td>
        <td><strong><?= h($row['full_name']) ?></strong></td>
        <td class="text-muted" style="font-size:.82rem"><?= h($row['username']) ?></td>
        <td>
          <span class="badge badge-info"><?= h($row['form_name']) ?></span>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
adminLayout('log', 'Access Log', $content);
