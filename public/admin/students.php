<?php
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/AdminLayout.php';
Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        if ($username && $password && $fullName) {
            if (Database::createStudent($username, $password, $fullName)) {
                flash('success', "Student '{$fullName}' created.");
            } else {
                flash('danger', "Username '{$username}' already exists.");
            }
        } else {
            flash('danger', 'All fields are required.');
        }
    } elseif ($action === 'toggle') {
        Database::toggleStudent((int)($_POST['id'] ?? 0));
        flash('success', 'Student status changed.');
    } elseif ($action === 'delete') {
        Database::deleteStudent((int)($_POST['id'] ?? 0));
        flash('success', 'Student deleted.');
    }
    header('Location: /admin/students.php'); exit;
}

$students = Database::getAllStudents();
csrf();
ob_start();
?>
<?= flash() ?>
<div class="section-header">
  <div class="section-title">Students</div>
  <button class="btn btn-primary" onclick="document.getElementById('createModal').style.display='flex'">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
    Add Student
  </button>
</div>

<?php if (empty($students)): ?>
  <div class="empty-state">
    <svg width="64" height="64" viewBox="0 0 24 24" fill="var(--text-muted)">
      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>
    </svg>
    <p>No students yet. Click "Add Student" to register one.</p>
  </div>
<?php else: ?>
<div class="table-wrap">
  <table>
    <thead>
      <tr><th>#</th><th>Name</th><th>Username</th><th>Opens</th><th>Status</th><th>Registered</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($students as $i => $s): ?>
      <tr>
        <td class="text-muted" style="font-size:.8rem"><?= $i+1 ?></td>
        <td><strong><?= h($s['full_name']) ?></strong></td>
        <td class="text-dim"><?= h($s['username']) ?></td>
        <td>
          <span class="badge badge-info"><?= (int)$s['total_opens'] ?> opens</span>
        </td>
        <td>
          <?php if ($s['active']): ?>
            <span class="badge badge-success">● Active</span>
          <?php else: ?>
            <span class="badge badge-danger">✕ Disabled</span>
          <?php endif; ?>
        </td>
        <td class="text-muted" style="font-size:.78rem"><?= h(substr($s['created_at'],0,10)) ?></td>
        <td>
          <div class="gap-1">
            <form method="post" style="display:inline">
              <input type="hidden" name="_csrf" value="<?= csrf() ?>">
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="id" value="<?= $s['id'] ?>">
              <button class="btn btn-ghost btn-sm"><?= $s['active']?'Disable':'Enable' ?></button>
            </form>
            <form method="post" style="display:inline"
              onsubmit="return confirm('Delete this student? All their access logs will also be removed.')">
              <input type="hidden" name="_csrf" value="<?= csrf() ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $s['id'] ?>">
              <button class="btn btn-danger-ghost btn-sm">Delete</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<!-- ── Create Modal ──────────────────────────────────────────────────────── -->
<div id="createModal" class="modal-overlay" style="display:none"
     onclick="if(event.target===this)this.style.display='none'">
  <div class="modal">
    <div class="modal-title">Add Student</div>
    <form method="post" autocomplete="off">
      <input type="hidden" name="_csrf" value="<?= csrf() ?>">
      <input type="hidden" name="action" value="create">
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input class="form-control" name="full_name" required placeholder="Jane Smith">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Username</label>
          <input class="form-control" name="username" required placeholder="jsmith" autocomplete="off">
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input class="form-control" name="password" type="password" required placeholder="••••••" autocomplete="new-password">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="document.getElementById('createModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn btn-primary">Create Student</button>
      </div>
    </form>
  </div>
</div>
<?php
$content = ob_get_clean();
adminLayout('students', 'Students', $content);
