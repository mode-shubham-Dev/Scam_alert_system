<?php
require_once __DIR__ . '/../src/auth.php';
requireRole('moderator');
$pdo = getDB();
$id  = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    if (!in_array($action, ['verify', 'reject'], true)) {
        http_response_code(400);
        die('Invalid action.');
    }
    // Guard: only allow reviewing a pending report (prevents re-review via crafted POST)
    $chk = $pdo->prepare("SELECT status FROM reports WHERE id = ?");
    $chk->execute([$id]);
    $chk = $chk->fetch();
    if (!$chk || $chk['status'] !== 'pending') {
        header('Location: review_queue.php');
        exit;
    }
    $newStatus = $action === 'verify' ? 'verified' : 'rejected';
    $notes     = trim($_POST['moderator_notes'] ?? '');
    // AND status = 'pending' acts as a race-condition guard at the DB level
    $pdo->prepare(
        "UPDATE reports SET status = ?, reviewed_by = ?, reviewed_at = NOW(), moderator_notes = ?
         WHERE id = ? AND status = 'pending'"
    )->execute([$newStatus, currentUser()['id'], $notes !== '' ? $notes : null, $id]);
    header('Location: review_queue.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT reports.*, users.name AS reporter_name, users.email AS reporter_email
    FROM reports JOIN users ON reports.reporter_id = users.id WHERE reports.id = ?
");
$stmt->execute([$id]);
$report = $stmt->fetch();

require __DIR__ . '/../src/views/header.php';

if (!$report) {
    echo '<div class="alert alert-danger"><i class="bi bi-exclamation-circle-fill me-2"></i>Report not found.</div>';
    echo '<a href="review_queue.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Queue</a>';
    require __DIR__ . '/../src/views/footer.php';
    exit;
}

$statusBadge = ['pending' => 'bg-warning', 'verified' => 'bg-success', 'rejected' => 'bg-danger'];
$isImage     = !empty($report['evidence_mime']) && str_starts_with($report['evidence_mime'], 'image/');
?>

<div class="pg-header">
  <div>
    <h3 class="pg-title">Review Report</h3>
    <p class="pg-subtitle">ID #<?= (int)$report['id'] ?> &bull; <?= htmlspecialchars($report['scam_type']) ?></p>
  </div>
  <a href="review_queue.php" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Back to Queue
  </a>
</div>

<div class="row g-3">
  <!-- Report details -->
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h6 class="mb-0 fw-bold" style="color:var(--ss-navy)">Report Details</h6>
          <span class="badge <?= $statusBadge[$report['status']] ?? 'bg-secondary' ?>"><?= htmlspecialchars($report['status']) ?></span>
        </div>
        <dl class="row fs-xs mb-3">
          <dt class="col-sm-3 text-muted">Reporter</dt>
          <dd class="col-sm-9 fw-medium"><?= htmlspecialchars($report['reporter_name']) ?></dd>
          <dt class="col-sm-3 text-muted">Email</dt>
          <dd class="col-sm-9"><?= htmlspecialchars($report['reporter_email']) ?></dd>
          <dt class="col-sm-3 text-muted">Submitted</dt>
          <dd class="col-sm-9"><?= htmlspecialchars($report['submitted_at']) ?></dd>
        </dl>
        <hr>
        <p class="fs-xs text-muted mb-1 fw-bold" style="text-transform:uppercase;letter-spacing:.05em">Description</p>
        <div class="p-3 rounded" style="background:var(--ss-bg);font-size:.9rem;line-height:1.7">
          <?= nl2br(htmlspecialchars($report['description'])) ?>
        </div>
      </div>
    </div>

    <?php if ($report['evidence_path']): ?>
      <div class="card mb-3">
        <div class="card-body">
          <p class="fs-xs text-muted mb-2 fw-bold" style="text-transform:uppercase;letter-spacing:.05em">Evidence</p>
          <?php if ($isImage): ?>
            <img src="<?= htmlspecialchars($report['evidence_path']) ?>"
                 class="rounded border img-fluid" style="max-width:420px;">
          <?php else: ?>
            <a href="<?= htmlspecialchars($report['evidence_path']) ?>"
               class="btn btn-outline-secondary btn-sm" target="_blank">
              <i class="bi bi-paperclip"></i> View Attachment
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Decision panel -->
  <div class="col-lg-4">
    <?php if ($report['status'] === 'pending'): ?>
      <div class="card">
        <div class="card-body">
          <h6 class="fw-bold mb-3" style="color:var(--ss-navy)">Your Decision</h6>
          <form method="POST">
            <?= csrfField() ?>
            <div class="mb-3">
              <label class="form-label">Moderator Notes <span class="text-muted fw-normal" style="text-transform:none;letter-spacing:0">(optional)</span></label>
              <textarea name="moderator_notes" class="form-control" rows="4" maxlength="1000"
                placeholder="Reason for your decision, additional context…"><?= htmlspecialchars($report['moderator_notes'] ?? '') ?></textarea>
            </div>
            <div class="d-grid gap-2">
              <button type="submit" name="action" value="verify" class="btn btn-success">
                <i class="bi bi-check-circle-fill"></i> Mark Verified
              </button>
              <button type="submit" name="action" value="reject" class="btn btn-danger">
                <i class="bi bi-x-circle-fill"></i> Reject Report
              </button>
            </div>
          </form>
        </div>
      </div>
    <?php else: ?>
      <div class="card">
        <div class="card-body">
          <h6 class="fw-bold mb-2" style="color:var(--ss-navy)">Decision Recorded</h6>
          <p class="fs-xs text-muted mb-3">This report has already been reviewed.</p>
          <?php if (!empty($report['moderator_notes'])): ?>
            <div class="p-3 rounded" style="background:var(--ss-bg);font-size:.85rem">
              <p class="fs-xs text-muted mb-1 fw-bold" style="text-transform:uppercase;letter-spacing:.05em">Moderator Notes</p>
              <?= nl2br(htmlspecialchars($report['moderator_notes'])) ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../src/views/footer.php'; ?>
