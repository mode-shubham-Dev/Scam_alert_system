<?php
require_once __DIR__ . '/../src/auth.php';
requireRole('admin');
$pdo = getDB();

$totalUsers   = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalReports = (int)$pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();
$pending      = (int)$pdo->query("SELECT COUNT(*) FROM reports WHERE status='pending'")->fetchColumn();
$verified     = (int)$pdo->query("SELECT COUNT(*) FROM reports WHERE status='verified'")->fetchColumn();
$rejected     = (int)$pdo->query("SELECT COUNT(*) FROM reports WHERE status='rejected'")->fetchColumn();
$totalAlerts  = (int)$pdo->query("SELECT COUNT(*) FROM alerts")->fetchColumn();
$totalSubs    = (int)$pdo->query("SELECT COUNT(*) FROM subscribers")->fetchColumn();
$byType       = $pdo->query("SELECT scam_type, COUNT(*) AS cnt FROM reports GROUP BY scam_type ORDER BY cnt DESC")->fetchAll();

require __DIR__ . '/../src/views/header.php';
$total = max($totalReports, 1);
?>

<div class="pg-header">
  <div>
    <h3 class="pg-title">Admin Dashboard</h3>
    <p class="pg-subtitle">System-wide statistics and report breakdown</p>
  </div>
  <a href="manage_users.php" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-people"></i> Manage Users
  </a>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon si-blue"><i class="bi bi-people-fill"></i></div>
      <div>
        <div class="stat-value"><?= $totalUsers ?></div>
        <div class="stat-label">Total Users</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon si-purple"><i class="bi bi-file-earmark-text-fill"></i></div>
      <div>
        <div class="stat-value"><?= $totalReports ?></div>
        <div class="stat-label">Total Reports</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon si-amber"><i class="bi bi-hourglass-split"></i></div>
      <div>
        <div class="stat-value"><?= $pending ?></div>
        <div class="stat-label">Pending Review</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon si-green"><i class="bi bi-check-circle-fill"></i></div>
      <div>
        <div class="stat-value"><?= $verified ?></div>
        <div class="stat-label">Verified</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon si-red"><i class="bi bi-x-circle-fill"></i></div>
      <div>
        <div class="stat-value"><?= $rejected ?></div>
        <div class="stat-label">Rejected</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon si-cyan"><i class="bi bi-megaphone-fill"></i></div>
      <div>
        <div class="stat-value"><?= $totalAlerts ?></div>
        <div class="stat-label">Alerts Published</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon si-indigo"><i class="bi bi-envelope-fill"></i></div>
      <div>
        <div class="stat-value"><?= $totalSubs ?></div>
        <div class="stat-label">Subscribers</div>
      </div>
    </div>
  </div>
</div>

<!-- Status Breakdown -->
<div class="card mb-4">
  <div class="card-body">
    <h6 class="fw-bold mb-3" style="color:var(--ss-navy)">
      <i class="bi bi-bar-chart-fill me-1" style="color:var(--ss-blue)"></i>
      Report Status Overview
    </h6>
    <div class="progress mb-3" style="height:26px;">
      <?php if ($pending > 0): ?>
        <div class="progress-bar" style="width:<?= round($pending/$total*100) ?>%;background:var(--ss-amber);color:#78350f"
             title="Pending: <?= $pending ?>">
          <?= $pending ?> Pending
        </div>
      <?php endif; ?>
      <?php if ($verified > 0): ?>
        <div class="progress-bar" style="width:<?= round($verified/$total*100) ?>%;background:var(--ss-green)"
             title="Verified: <?= $verified ?>">
          <?= $verified ?> Verified
        </div>
      <?php endif; ?>
      <?php if ($rejected > 0): ?>
        <div class="progress-bar" style="width:<?= round($rejected/$total*100) ?>%;background:var(--ss-red)"
             title="Rejected: <?= $rejected ?>">
          <?= $rejected ?> Rejected
        </div>
      <?php endif; ?>
    </div>
    <div class="d-flex flex-wrap gap-4 fs-xs">
      <span>
        <span class="badge bg-warning me-1">Pending</span>
        <?= $pending ?> <span class="text-muted">(<?= round($pending/$total*100) ?>%)</span>
      </span>
      <span>
        <span class="badge bg-success me-1">Verified</span>
        <?= $verified ?> <span class="text-muted">(<?= round($verified/$total*100) ?>%)</span>
      </span>
      <span>
        <span class="badge bg-danger me-1">Rejected</span>
        <?= $rejected ?> <span class="text-muted">(<?= round($rejected/$total*100) ?>%)</span>
      </span>
    </div>
  </div>
</div>

<!-- Scam Type Breakdown -->
<?php if (!empty($byType)): ?>
<div class="card">
  <div class="card-body">
    <h6 class="fw-bold mb-4" style="color:var(--ss-navy)">
      <i class="bi bi-pie-chart-fill me-1" style="color:var(--ss-purple)"></i>
      Reports by Scam Type
    </h6>
    <?php foreach ($byType as $row): $pct = round($row['cnt'] / $total * 100); ?>
      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="fw-medium fs-xs" style="color:var(--ss-navy-2)"><?= htmlspecialchars($row['scam_type']) ?></span>
          <span class="fs-xs text-muted">
            <?= $row['cnt'] ?> report<?= $row['cnt'] !== 1 ? 's' : '' ?> &nbsp;&bull;&nbsp; <?= $pct ?>%
          </span>
        </div>
        <div class="progress" style="height:10px;">
          <div class="progress-bar" style="width:<?= $pct ?>%;background:var(--ss-blue)"></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../src/views/footer.php'; ?>
