<?php
require_once __DIR__ . '/../src/auth.php';
$pdo    = getDB();
$alerts = $pdo->query("SELECT * FROM alerts ORDER BY created_at DESC")->fetchAll();
require __DIR__ . '/../src/views/header.php';
?>
<div class="pg-header">
  <div>
    <h3 class="pg-title">Scam Alerts</h3>
    <p class="pg-subtitle">Verified and published alerts from our awareness team</p>
  </div>
  <a href="subscribe.php" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-bell"></i> Subscribe to alerts
  </a>
</div>

<?php if (empty($alerts)): ?>
  <div class="empty-state">
    <i class="bi bi-bell-slash"></i>
    <p>No alerts have been published yet. Check back soon.</p>
  </div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($alerts as $a): ?>
      <div class="col-md-6">
        <div class="alert-card">
          <div class="alert-card-tag">
            <i class="bi bi-exclamation-triangle-fill"></i> Scam Alert
          </div>
          <div class="alert-card-title"><?= htmlspecialchars($a['title']) ?></div>
          <div class="alert-card-body"><?= nl2br(htmlspecialchars($a['body'])) ?></div>
          <div class="alert-card-meta">
            <i class="bi bi-clock"></i> <?= htmlspecialchars($a['created_at']) ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
