<?php
require_once __DIR__ . '/../src/bootstrap.php';
csrf(); // ensure token exists for navHtml
Auth::requireStudent();

$studentId = $_SESSION['student_id'];
$forms     = Database::getActiveForms();
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
<?= navHtml(h($_SESSION['student_name'])) ?>
<div class="page">
  <?= flash() ?>
  <div class="section-header">
    <div>
      <div class="section-title">My Forms</div>
      <p class="text-muted" style="font-size:.85rem">Click a form below to open it. Each form has a limited number of opens.</p>
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
        $opens    = Database::countOpens($studentId, (int)$form['id']);
        $maxOpens = (int)$form['max_opens'];
        $remaining= $maxOpens - $opens;
        $pct      = $maxOpens > 0 ? min(100, (int)(($opens / $maxOpens) * 100)) : 100;
        $canOpen  = $remaining > 0;
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
            <?= $duration ?> min timer &nbsp;·&nbsp;
            <?= $canOpen ? "Opens remaining: <strong style='color:var(--text)'>{$remaining}/{$maxOpens}</strong>" : "<span style='color:var(--danger)'>No opens remaining</span>" ?>
          </div>
        </div>
        <div class="form-card-body">
          <div>
            <div style="display:flex;justify-content:space-between;font-size:.75rem;color:var(--text-muted);margin-bottom:.4rem">
              <span>Opens used</span>
              <span><?= $opens ?> / <?= $maxOpens ?></span>
            </div>
            <div class="opens-bar">
              <div class="opens-bar-fill" style="width:<?= $pct ?>%;<?= $pct>=100?'background:var(--danger)':'' ?>"></div>
            </div>
          </div>
          <div class="form-card-meta">
            <?php if ($canOpen): ?>
              <span class="badge badge-success">● Available</span>
            <?php else: ?>
              <span class="badge badge-danger">✕ Exhausted</span>
            <?php endif; ?>
            <span class="badge badge-info">⏱ <?= $duration ?> min</span>
          </div>
          <div class="form-card-actions">
            <?php if ($canOpen): ?>
              <a href="/viewer.php?form=<?= $form['id'] ?>" class="btn btn-primary" style="width:100%;justify-content:center">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>
                </svg>
                Open Form
              </a>
            <?php else: ?>
              <button class="btn btn-ghost" style="width:100%;justify-content:center" disabled>
                Limit Reached
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
