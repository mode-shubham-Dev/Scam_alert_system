<?php
require_once __DIR__ . '/../src/auth.php';
requireRole('awareness_manager');
$pdo = getDB();

$stmt = $pdo->query("
    SELECT reports.*,
           reporters.name AS reporter_name
    FROM reports
    JOIN users AS reporters ON reports.reporter_id = reporters.id
    WHERE reports.status = 'verified'
    ORDER BY reports.reviewed_at DESC
");
$reports = $stmt->fetchAll();

require __DIR__ . '/../src/views/header.php';
?>

<div class="pg-header">
  <div>
    <h3 class="pg-title">Verified Reports</h3>
    <p class="pg-subtitle">Confirmed scam reports — use these as reference when writing alerts</p>
  </div>
  <a href="publish_alert.php" class="btn btn-primary btn-sm">
    <i class="bi bi-megaphone-fill"></i> Publish Alert
  </a>
</div>

<?php if (empty($reports)): ?>
  <div class="empty-state">
    <i class="bi bi-shield-check"></i>
    <p>No verified reports yet. Once a moderator verifies a report it will appear here.</p>
  </div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($reports as $r): ?>
      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-body">

            <!-- Header row -->
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="badge bg-secondary"><?= htmlspecialchars($r['scam_type']) ?></span>
              <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Verified</span>
            </div>

            <!-- Description -->
            <p class="fs-xs mb-3" style="color:var(--ss-text);line-height:1.65">
              <?= nl2br(htmlspecialchars(mb_substr($r['description'], 0, 250))) ?><?= mb_strlen($r['description']) > 250 ? '…' : '' ?>
            </p>

            <!-- Evidence image -->
            <?php if (!empty($r['evidence_path']) && !empty($r['evidence_mime']) && str_starts_with($r['evidence_mime'], 'image/')): ?>
              <img src="<?= htmlspecialchars($r['evidence_path']) ?>"
                   class="img-fluid rounded border mb-3" style="max-height:160px;object-fit:cover;">
            <?php endif; ?>

            <!-- Footer meta -->
            <div class="d-flex flex-wrap gap-3 fs-xs text-muted mt-auto pt-2"
                 style="border-top:1px solid var(--ss-border)">
              <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($r['reporter_name']) ?></span>
              <span><i class="bi bi-calendar-check me-1"></i><?= htmlspecialchars($r['reviewed_at'] ?? '') ?></span>
            </div>

            <?php if (!empty($r['moderator_notes'])): ?>
              <div class="mt-2 p-2 rounded fs-xs" style="background:var(--ss-green-50);color:#14532d;border:1px solid #86efac">
                <i class="bi bi-chat-left-text me-1"></i><strong>Moderator note:</strong>
                <?= htmlspecialchars($r['moderator_notes']) ?>
              </div>
            <?php endif; ?>

          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../src/views/footer.php'; ?>
