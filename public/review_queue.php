<?php
require_once __DIR__ . '/../src/auth.php';
requireRole('moderator');
$pdo        = getDB();
$validTypes = ['Phishing Email', 'Fake Job Offer', 'Fraudulent E-commerce', 'Social Media Impersonation', 'Other'];
$filter     = trim($_GET['type'] ?? '');
if (!in_array($filter, $validTypes, true)) { $filter = ''; }

if ($filter !== '') {
    $stmt = $pdo->prepare("
        SELECT reports.*, users.name AS reporter_name
        FROM reports JOIN users ON reports.reporter_id = users.id
        WHERE reports.status = 'pending' AND reports.scam_type = ?
        ORDER BY reports.submitted_at ASC
    ");
    $stmt->execute([$filter]);
} else {
    $stmt = $pdo->query("
        SELECT reports.*, users.name AS reporter_name
        FROM reports JOIN users ON reports.reporter_id = users.id
        WHERE reports.status = 'pending' ORDER BY reports.submitted_at ASC
    ");
}
$reports = $stmt->fetchAll();
require __DIR__ . '/../src/views/header.php';
?>
<div class="pg-header">
  <div>
    <h3 class="pg-title">
      Pending Reports
      <span class="badge bg-warning ms-2" style="font-size:.75rem;vertical-align:middle"><?= count($reports) ?></span>
    </h3>
    <p class="pg-subtitle">Reports awaiting your review and moderation decision</p>
  </div>
  <form method="GET" class="d-flex align-items-center gap-2">
    <i class="bi bi-funnel text-muted fs-xs"></i>
    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
      <option value="">All Types</option>
      <?php foreach ($validTypes as $t): ?>
        <option value="<?= htmlspecialchars($t) ?>" <?= $filter === $t ? 'selected' : '' ?>>
          <?= htmlspecialchars($t) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <?php if ($filter): ?>
      <a href="review_queue.php" class="btn btn-outline-danger btn-sm text-nowrap">
        <i class="bi bi-x"></i> Clear
      </a>
    <?php endif; ?>
  </form>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table">
      <thead>
        <tr>
          <th>Reporter</th>
          <th>Scam Type</th>
          <th>Submitted</th>
          <th style="width:90px">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reports as $r): ?>
          <tr>
            <td class="fw-medium"><?= htmlspecialchars($r['reporter_name']) ?></td>
            <td>
              <span class="badge bg-secondary"><?= htmlspecialchars($r['scam_type']) ?></span>
            </td>
            <td class="text-muted fs-xs text-nowrap"><?= htmlspecialchars($r['submitted_at']) ?></td>
            <td>
              <a href="report_detail.php?id=<?= (int)$r['id'] ?>" class="btn btn-primary btn-sm">
                Review
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($reports)): ?>
          <tr>
            <td colspan="4">
              <div class="empty-state">
                <i class="bi bi-check2-circle"></i>
                <p>
                  <?= $filter
                      ? 'No pending reports for <strong>' . htmlspecialchars($filter) . '</strong>.'
                      : 'All caught up — no pending reports right now!' ?>
                </p>
              </div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
