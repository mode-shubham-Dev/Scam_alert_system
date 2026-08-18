<?php
require_once __DIR__ . '/../src/auth.php';
$user = currentUser();
if ($user) {
    $redirects = [
        'reporter'          => 'report_form.php',
        'moderator'         => 'review_queue.php',
        'admin'             => 'dashboard.php',
        'awareness_manager' => 'publish_alert.php',
    ];
    header('Location: ' . ($redirects[$user['role']] ?? 'login.php'));
    exit;
}
$pdo          = getDB();
$latestAlerts = $pdo->query("SELECT * FROM alerts ORDER BY created_at DESC LIMIT 3")->fetchAll();
$totalReports = (int)$pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();
$totalAlerts  = (int)$pdo->query("SELECT COUNT(*) FROM alerts")->fetchColumn();
$totalUsers   = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
require __DIR__ . '/../src/views/header.php';
?>

<!-- ── Hero ── -->
<div class="hero-section">
  <div class="row align-items-center">
    <div class="col-lg-8" style="position:relative;z-index:1">
      <div class="hero-eyebrow">Community-powered scam detection</div>
      <h1 class="hero-title">
        Stay one step ahead<br>of online scammers.
      </h1>
      <p class="hero-sub">
        Report threats, get verified alerts, and help protect your community
        from digital fraud — all in one place.
      </p>
      <div class="d-flex flex-wrap gap-3">
        <a href="register.php" class="btn btn-primary btn-lg">
          <i class="bi bi-person-plus-fill"></i> Get Started Free
        </a>
        <a href="alerts.php" class="btn btn-hero-ghost btn-lg">
          Browse Alerts <i class="bi bi-arrow-right"></i>
        </a>
      </div>

      <!-- Stats bar -->
      <div class="hero-stats">
        <div>
          <div class="hero-stat-num"><?= $totalReports ?>+</div>
          <div class="hero-stat-lbl">Reports submitted</div>
        </div>
        <div>
          <div class="hero-stat-num"><?= $totalAlerts ?>+</div>
          <div class="hero-stat-lbl">Alerts published</div>
        </div>
        <div>
          <div class="hero-stat-num"><?= $totalUsers ?>+</div>
          <div class="hero-stat-lbl">Community members</div>
        </div>
      </div>
    </div>
    <div class="col-lg-4 d-none d-lg-flex justify-content-center align-items-center" style="position:relative;z-index:1">
      <span class="hero-shield-glyph">
        <i class="bi bi-shield-check"></i>
      </span>
    </div>
  </div>
</div>

<!-- ── Latest Alerts ── -->
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h5 class="mb-0 fw-bold" style="color:var(--ss-navy)">Latest Alerts</h5>
    <p class="fs-xs text-muted mb-0 mt-1">Most recently published scam warnings</p>
  </div>
  <a href="alerts.php" class="btn btn-outline-secondary btn-sm">
    View all <i class="bi bi-arrow-right"></i>
  </a>
</div>

<?php if (empty($latestAlerts)): ?>
  <div class="empty-state">
    <i class="bi bi-bell-slash"></i>
    <p>No alerts published yet. Check back soon.</p>
  </div>
<?php else: ?>
  <div class="row g-3 mb-5">
    <?php foreach ($latestAlerts as $a): ?>
      <div class="col-md-4">
        <div class="alert-card">
          <div class="alert-card-tag">
            <i class="bi bi-exclamation-triangle-fill"></i> Scam Alert
          </div>
          <div class="alert-card-title"><?= htmlspecialchars($a['title']) ?></div>
          <div class="alert-card-body">
            <?= htmlspecialchars(mb_substr($a['body'], 0, 130)) ?><?= mb_strlen($a['body']) > 130 ? '…' : '' ?>
          </div>
          <div class="alert-card-meta">
            <i class="bi bi-clock"></i>
            <?= htmlspecialchars($a['created_at']) ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- ── How it works ── -->
<div class="card mb-4">
  <div class="card-body">
    <h5 class="fw-bold mb-4" style="color:var(--ss-navy)">How ScamShield works</h5>
    <div class="row g-4 text-center">
      <div class="col-md-4">
        <div class="mb-2" style="font-size:2rem;color:var(--ss-blue)"><i class="bi bi-person-plus"></i></div>
        <h6 class="fw-bold">1. Create an account</h6>
        <p class="text-muted fs-xs mb-0">Sign up as a reporter and start contributing to your community's safety.</p>
      </div>
      <div class="col-md-4">
        <div class="mb-2" style="font-size:2rem;color:var(--ss-amber)"><i class="bi bi-file-earmark-text"></i></div>
        <h6 class="fw-bold">2. Submit a report</h6>
        <p class="text-muted fs-xs mb-0">Describe the scam, attach evidence, and submit it for review by our moderators.</p>
      </div>
      <div class="col-md-4">
        <div class="mb-2" style="font-size:2rem;color:var(--ss-green)"><i class="bi bi-shield-check"></i></div>
        <h6 class="fw-bold">3. Community is protected</h6>
        <p class="text-muted fs-xs mb-0">Verified reports become public alerts — helping everyone stay one step ahead.</p>
      </div>
    </div>
  </div>
</div>

<div class="text-center py-2 mb-4">
  <p class="text-muted fs-xs mb-2">Want to be notified when new alerts are published?</p>
  <a href="subscribe.php" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-envelope"></i> Subscribe to alerts
  </a>
</div>

<?php require __DIR__ . '/../src/views/footer.php'; ?>
