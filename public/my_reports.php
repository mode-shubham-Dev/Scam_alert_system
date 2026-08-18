<?php
require_once __DIR__ . '/../src/auth.php';
requireRole('reporter');
$pdo  = getDB();
$stmt = $pdo->prepare("SELECT * FROM reports WHERE reporter_id = ? ORDER BY submitted_at DESC");
$stmt->execute([currentUser()['id']]);
$reports = $stmt->fetchAll();
require __DIR__ . '/../src/views/header.php';
?>
<div class="pg-header">
  <div>
    <h3 class="pg-title">My Reports</h3>
    <p class="pg-subtitle">All scam reports you have submitted</p>
  </div>
  <a href="report_form.php" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-circle-fill"></i> New Report
  </a>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table">
      <thead>
        <tr>
          <th>Scam Type</th>
          <th>Description</th>
          <th>Status</th>
          <th>Submitted</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reports as $r): ?>
          <tr class="status-<?= htmlspecialchars($r['status']) ?>">
            <td class="fw-medium"><?= htmlspecialchars($r['scam_type']) ?></td>
            <td style="max-width:280px">
              <span class="text-muted"><?= htmlspecialchars(mb_substr($r['description'], 0, 90)) ?><?= mb_strlen($r['description']) > 90 ? '…' : '' ?></span>
            </td>
            <td>
              <?php
                $badge = ['pending' => 'bg-warning', 'verified' => 'bg-success', 'rejected' => 'bg-danger'][$r['status']] ?? 'bg-secondary';
              ?>
              <span class="badge <?= $badge ?>"><?= htmlspecialchars($r['status']) ?></span>
            </td>
            <td class="text-muted fs-xs text-nowrap"><?= htmlspecialchars($r['submitted_at']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($reports)): ?>
          <tr>
            <td colspan="4">
              <div class="empty-state">
                <i class="bi bi-clipboard-x"></i>
                <p>You haven't submitted any reports yet.</p>
                <a href="report_form.php" class="btn btn-primary btn-sm mt-2">Submit your first report</a>
              </div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
