<?php
require_once __DIR__ . '/../src/auth.php';
requireRole('awareness_manager');
$pdo     = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $title = trim($_POST['title'] ?? '');
    $body  = trim($_POST['body']  ?? '');
    if ($title === '' || $body === '') {
        $error = 'Title and message body are required.';
    } elseif (strlen($title) > 255) {
        $error = 'Title must be under 255 characters.';
    } elseif (strlen($body) > 5000) {
        $error = 'Message must be under 5000 characters.';
    } else {
        $pdo->prepare("INSERT INTO alerts (title, body, created_by) VALUES (?, ?, ?)")
            ->execute([$title, $body, currentUser()['id']]);
        // PRG: redirect so browser refresh doesn't re-publish the same alert
        setFlash('success', 'Alert published successfully.');
        header('Location: publish_alert.php');
        exit;
    }
}
$alerts = $pdo->query("SELECT * FROM alerts ORDER BY created_at DESC")->fetchAll();
require __DIR__ . '/../src/views/header.php';
?>
<div class="row g-4">
  <!-- Publish form -->
  <div class="col-lg-5">
    <div class="pg-header d-block mb-3">
      <h3 class="pg-title">Publish New Alert</h3>
      <p class="pg-subtitle">Broadcast a verified scam warning to the community</p>
    </div>

    <?php if ($error): ?><div class="alert alert-danger d-flex gap-2 align-items-center mb-3"><i class="bi bi-exclamation-circle-fill"></i><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card">
      <div class="card-body">
        <form method="POST">
          <?= csrfField() ?>
          <div class="mb-3">
            <label class="form-label">Alert Title</label>
            <input type="text" name="title" class="form-control" required maxlength="255"
                   placeholder="e.g. New phishing campaign targeting Gmail users">
          </div>
          <div class="mb-4">
            <label class="form-label">Message</label>
            <textarea name="body" class="form-control" rows="6" required maxlength="5000"
              placeholder="Describe the threat, what to watch out for, and how people can protect themselves…"></textarea>
          </div>
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-megaphone-fill"></i> Publish Alert
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Published alerts -->
  <div class="col-lg-7">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h5 class="mb-0 fw-bold" style="color:var(--ss-navy)">
        Published Alerts
        <span class="badge bg-secondary ms-1"><?= count($alerts) ?></span>
      </h5>
    </div>

    <?php if (empty($alerts)): ?>
      <div class="empty-state">
        <i class="bi bi-megaphone"></i>
        <p>No alerts published yet. Use the form to publish your first one.</p>
      </div>
    <?php else: ?>
      <div class="d-flex flex-column gap-2">
        <?php foreach ($alerts as $a): ?>
          <div class="alert-card">
            <div class="alert-card-tag"><i class="bi bi-megaphone-fill"></i> Alert</div>
            <div class="alert-card-title"><?= htmlspecialchars($a['title']) ?></div>
            <div class="alert-card-body"><?= nl2br(htmlspecialchars(mb_substr($a['body'], 0, 160))) ?><?= mb_strlen($a['body']) > 160 ? '…' : '' ?></div>
            <div class="alert-card-meta"><i class="bi bi-clock"></i> <?= htmlspecialchars($a['created_at']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
