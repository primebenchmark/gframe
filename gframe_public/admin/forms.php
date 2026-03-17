<?php
require_once __DIR__ . '/../../gframe_src/bootstrap.php';
require_once __DIR__ . '/../../gframe_src/AdminLayout.php';
// ─── Handle actions ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name     = trim($_POST['name'] ?? '');
        $url      = trim($_POST['url'] ?? '');
        $maxOpens = (int)($_POST['max_opens'] ?? 1);
        $duration = max(1, (int)($_POST['duration'] ?? 30));
        if ($name && $url && filter_var($url, FILTER_VALIDATE_URL)) {
            Database::createForm($name, $url, max(1, $maxOpens), $duration);
            flash('success', 'Form created successfully.');
        } else {
            flash('danger', 'Please provide a valid name and URL.');
        }
    } elseif ($action === 'edit') {
        $id       = (int)($_POST['id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $url      = trim($_POST['url'] ?? '');
        $maxOpens = max(1, (int)($_POST['max_opens'] ?? 1));
        $duration = max(1, (int)($_POST['duration'] ?? 30));
        if ($id && $name && $url) {
            Database::updateForm($id, $name, $url, $maxOpens, $duration);
            flash('success', 'Form updated.');
        }
    } elseif ($action === 'toggle') {
        Database::toggleForm((int)($_POST['id'] ?? 0));
        flash('success', 'Form status changed.');
    } elseif ($action === 'delete') {
        Database::deleteForm((int)($_POST['id'] ?? 0));
        flash('success', 'Form deleted.');
    }
    header('Location: /admin/forms.php'); exit;
}

$forms = Database::getAllForms();
csrf();
ob_start();
?>
<?= flash() ?>
<div class="section-header">
  <div class="section-title">Forms</div>
  <button class="btn btn-primary" onclick="openModal('createModal')">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
    Add Form
  </button>
</div>

<?php if (empty($forms)): ?>
  <div class="empty-state">
    <svg width="64" height="64" viewBox="0 0 24 24" fill="var(--text-muted)"><path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
    <p>No forms added yet. Click "Add Form" to create one.</p>
  </div>
<?php else: ?>
<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>#</th><th>Name</th><th>Max Opens</th><th>Duration</th><th>Status</th><th>Created</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($forms as $i => $f): ?>
      <tr>
        <td class="text-muted" style="font-size:.8rem"><?= $i+1 ?></td>
        <td>
          <strong><?= h($f['name']) ?></strong><br>
          <a href="<?= h($f['url']) ?>" target="_blank" rel="noopener"
             class="text-muted" style="font-size:.73rem;text-decoration:underline;text-underline-offset:3px">
            <?= h(mb_strimwidth($f['url'], 0, 55, '…')) ?>
          </a>
        </td>
        <td><?= (int)$f['max_opens'] ?></td>
        <td><?= (int)$f['duration'] ?> min</td>
        <td>
          <?php if ($f['active']): ?>
            <span class="badge badge-success">● Active</span>
          <?php else: ?>
            <span class="badge badge-danger">✕ Inactive</span>
          <?php endif; ?>
        </td>
        <td class="text-muted" style="font-size:.78rem"><?= h(substr($f['created_at'],0,10)) ?></td>
        <td>
          <div class="gap-1">
            <button class="btn btn-ghost btn-sm"
              onclick='openEditModal(<?= json_encode($f) ?>)'>Edit</button>
            <form method="post" style="display:inline">
              <input type="hidden" name="_csrf" value="<?= csrf() ?>">
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="id" value="<?= $f['id'] ?>">
              <button class="btn btn-ghost btn-sm"><?= $f['active']?'Disable':'Enable' ?></button>
            </form>
            <form method="post" style="display:inline"
              onsubmit="return confirm('Delete this form? All access logs will also be removed.')">
              <input type="hidden" name="_csrf" value="<?= csrf() ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $f['id'] ?>">
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
<div id="createModal" class="modal-overlay" style="display:none" onclick="closeOnBg(event,'createModal')">
  <div class="modal">
    <div class="modal-title">Add New Form</div>
    <form method="post">
      <input type="hidden" name="_csrf" value="<?= csrf() ?>">
      <input type="hidden" name="action" value="create">
      <div class="form-group">
        <label class="form-label">Form Name</label>
        <input class="form-control" name="name" required placeholder="e.g. Mid-term Quiz">
      </div>
      <div class="form-group">
        <label class="form-label">Google Forms URL</label>
        <input class="form-control" name="url" type="url" required placeholder="https://docs.google.com/forms/...">
        <div class="form-hint">Paste the full viewform URL from Google Forms.</div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Max Opens per Student</label>
          <input class="form-control" name="max_opens" type="number" min="1" value="1" required>
        </div>
        <div class="form-group">
          <label class="form-label">Timer Duration (minutes)</label>
          <input class="form-control" name="duration" type="number" min="1" value="30" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('createModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Create Form</button>
      </div>
    </form>
  </div>
</div>

<!-- ── Edit Modal ────────────────────────────────────────────────────────── -->
<div id="editModal" class="modal-overlay" style="display:none" onclick="closeOnBg(event,'editModal')">
  <div class="modal">
    <div class="modal-title">Edit Form</div>
    <form method="post">
      <input type="hidden" name="_csrf" value="<?= csrf() ?>">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="editId">
      <div class="form-group">
        <label class="form-label">Form Name</label>
        <input class="form-control" name="name" id="editName" required>
      </div>
      <div class="form-group">
        <label class="form-label">Google Forms URL</label>
        <input class="form-control" name="url" id="editUrl" type="url" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Max Opens per Student</label>
          <input class="form-control" name="max_opens" id="editMaxOpens" type="number" min="1" required>
        </div>
        <div class="form-group">
          <label class="form-label">Timer Duration (minutes)</label>
          <input class="form-control" name="duration" id="editDuration" type="number" min="1" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('editModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) { document.getElementById(id).style.display='flex'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }
function closeOnBg(e,id){ if(e.target.classList.contains('modal-overlay')) closeModal(id); }
function openEditModal(f) {
  document.getElementById('editId').value       = f.id;
  document.getElementById('editName').value     = f.name;
  document.getElementById('editUrl').value      = f.url;
  document.getElementById('editMaxOpens').value = f.max_opens;
  document.getElementById('editDuration').value = f.duration;
  openModal('editModal');
}
</script>
<?php
$content = ob_get_clean();
adminLayout('forms', 'Forms', $content);
