<?php
require_once __DIR__ . '/../src/bootstrap.php';

$forms = Database::getActiveForms();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= getTheme() ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Forms – <?= APP_NAME ?></title>
<meta name="description" content="Your assigned Google Forms. Open, complete, and track your submissions.">
<link rel="stylesheet" href="/style.css">
</head>
<body>
<?= navHtml() ?>
<div class="page">
  <div class="section-header">
    <div>
      <div class="section-title">My Forms</div>
      <p class="text-muted" style="font-size:.85rem">Click a form below to open it.</p>
    </div>
  </div>

  <?php if (empty($forms)): ?>
    <div class="empty-state">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="var(--text-muted)">
        <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
      </svg>
      <p>No forms are available right now. Check back later.</p>
    </div>
  <?php else: ?>
    <div class="forms-grid">
      <?php foreach ($forms as $form):
        $duration = (int)$form['duration'];
      ?>
      <div class="form-card">
        <div class="form-card-top">
          <div class="form-card-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="#fff">
              <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
            </svg>
          </div>
          <div class="form-card-name"><?= h($form['name']) ?></div>
          <div style="font-size:.75rem;color:var(--text-muted);margin-top:.15rem">
            <?= $duration ?> min timer
          </div>
        </div>
        <div class="form-card-body">
          <div class="form-card-meta">
            <span class="badge badge-success">● Available</span>
            <span class="badge badge-info">⏱ <?= $duration ?> min</span>
          </div>
          <div class="form-card-actions">
            <a href="/viewer.php?form=<?= $form['id'] ?>" class="btn btn-primary" style="width:100%;justify-content:center">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>
              </svg>
              Open Form
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
