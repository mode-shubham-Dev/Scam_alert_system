<?php
require_once __DIR__ . '/../src/auth.php';
requireRole('moderator');
$pdo = getDB();

$stmt = $pdo->query("
    SELECT reports.*,
           reporters.name  AS reporter_name,
           reviewers.name  AS reviewer_name
    FROM reports
    JOIN users AS reporters ON reports.reporter_id = reporters.id
    LEFT JOIN users AS reviewers ON reports.reviewed_by = reviewers.id
    WHERE reports.status IN ('verified', 'rejected')
    ORDER BY reports.reviewed_at DESC
");
$reports = $stmt->fetchAll();

$total    = count($reports);
$verified = count(array_filter($reports, fn($r) => $r['status'] === 'verified'));
$rejected = count(array_filter($reports, fn($r) => $r['status'] === 'rejected'));

require __DIR__ . '/../src/views/header.php';
?>

<div class="pg-header">
  <div>
    <h3 class="pg-title">Review History</h3>
    <p class="pg-subtitle">All reports that have already been reviewed and decided</p>
  </div>
  <a href="review_queue.php" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-hourglass-split"></i> Pending Queue
  </a>
</div>

<!-- Summary badges -->
<div class="d-flex flex-wrap gap-3 mb-4">
  <div class="stat-card" style="padding:.9rem 1.2rem;flex:0">
    <div class="stat-icon si-purple"><i class="bi bi-clock-history"></i></div>
    <div>
      <div class="stat-value" style="font-size:1.4rem"><?= $total ?></div>
      <div class="stat-label">Total Reviewed</div>
    </div>
  </div>
  <div class="stat-card" style="padding:.9rem 1.2rem;flex:0">
    <div class="stat-icon si-green"><i class="bi bi-check-circle-fill"></i></div>
    <div>
      <div class="stat-value" style="font-size:1.4rem"><?= $verified ?></div>
      <div class="stat-label">Verified</div>
    </div>
  </div>
  <div class="stat-card" style="padding:.9rem 1.2rem;flex:0">
    <div class="stat-icon si-red"><i class="bi bi-x-circle-fill"></i></div>
    <div>
      <div class="stat-value" style="font-size:1.4rem"><?= $rejected ?></div>
      <div class="stat-label">Rejected</div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>Reporter</th>
          <th>Scam Type</th>
          <th>Decision</th>
          <th>Reviewed By</th>
          <th>Reviewed At</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($reports)): ?>
          <tr>
            <td colspan="6">
              <div class="empty-state">
                <i class="bi bi-clock-history"></i>
                <p>No reports have been reviewed yet.</p>
              </div>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($reports as $r): ?>
            <tr>
              <td class="fw-medium"><?= htmlspecialchars($r['reporter_name']) ?></td>
              <td>
                <span class="badge bg-secondary"><?= htmlspecialchars($r['scam_type']) ?></span>
              </td>
              <td>
                <?php if ($r['status'] === 'verified'): ?>
                  <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Verified</span>
                <?php else: ?>
                  <span class="badge bg-danger"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>
                <?php endif; ?>
              </td>
              <td class="fs-xs text-muted"><?= htmlspecialchars($r['reviewer_name'] ?? '—') ?></td>
              <td class="fs-xs text-muted text-nowrap"><?= htmlspecialchars($r['reviewed_at'] ?? '—') ?></td>
              <td class="fs-xs text-muted" style="max-width:220px">
                <?php if (!empty($r['moderator_notes'])): ?>
                  <?= htmlspecialchars(mb_substr($r['moderator_notes'], 0, 80)) ?><?= mb_strlen($r['moderator_notes']) > 80 ? '…' : '' ?>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../src/views/footer.php'; ?>
