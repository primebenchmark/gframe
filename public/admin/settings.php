<?php
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/AdminLayout.php';
Auth::requireAdmin();

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $footerHeight = (int)($_POST['footer_height'] ?? 155);
    Database::setSetting('footer_height', $footerHeight);
    $success = 'Settings updated successfully.';
}

$footerHeight = Database::getSetting('footer_height', 155);

ob_start();
?>

<div class="section-header">
    <h2 class="section-title">Application Settings</h2>
</div>

<?php if ($success): ?>
    <div class="alert alert-success">
        <span>✓</span> <?= h($success) ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 600px;">
    <form method="post">
        <input type="hidden" name="_csrf" value="<?= h(csrf()) ?>">
        
        <div class="form-group">
            <label class="form-label" for="footer_height">
                Footer Height (Viewer Page)
            </label>
            <div style="display: flex; align-items: center; gap: 1.5rem; margin-top: .5rem;">
                <input 
                    type="range" 
                    id="footerHeightRange" 
                    name="footer_height" 
                    min="50" 
                    max="300" 
                    step="1" 
                    value="<?= h((string)$footerHeight) ?>"
                    style="flex: 1; accent-color: var(--primary);"
                >
                <div style="min-width: 60px; text-align: right;">
                    <span id="footerHeightValue" style="font-size: 1.2rem; font-weight: 700; color: var(--primary);">
                        <?= h((string)$footerHeight) ?>
                    </span>
                    <span style="font-size: .85rem; color: var(--text-muted); margin-left: .2rem;">px</span>
                </div>
            </div>
            <p class="form-hint" style="margin-top: .5rem;">
                Adjust the height of the bottom bar in the quiz viewer. Default is 155px.
            </p>
        </div>

        <div class="modal-footer" style="margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

<!-- Live Preview Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const range = document.getElementById('footerHeightRange');
    const valueDisp = document.getElementById('footerHeightValue');
    
    range.addEventListener('input', function() {
        valueDisp.textContent = this.value;
    });
});
</script>

<?php
$content = ob_get_clean();
adminLayout('settings', 'Settings', $content);
