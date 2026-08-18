<?php
require_once __DIR__ . '/../src/auth.php';
$pdo     = getDB();
$message = '';
$isError = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email = trim(strtolower($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $isError = true;
    } elseif (!checkSubscribeLimit()) {
        $message = 'Please wait a moment before trying again.';
        $isError = true;
    } else {
        markSubscribeAttempt();
        try {
            $pdo->prepare("INSERT INTO subscribers (email) VALUES (?)")->execute([$email]);
        } catch (PDOException) { /* silent — don't reveal duplicates */ }
        $message = 'You\'re on the list. We\'ll notify you when new scam alerts are published.';
    }
}
require __DIR__ . '/../src/views/header.php';
?>
<div class="auth-wrap">
  <div class="auth-logo-mark" style="background:var(--ss-blue)"><i class="bi bi-bell-fill"></i></div>
  <h1 class="auth-heading">Stay in the loop</h1>
  <p class="auth-sub">Subscribe to receive scam alerts directly in your inbox. No spam, ever.</p>

  <div class="card auth-card">
    <div class="card-body">
      <?php if ($message): ?>
        <div class="alert alert-<?= $isError ? 'danger' : 'success' ?> d-flex align-items-center gap-2 mb-3">
          <i class="bi bi-<?= $isError ? 'exclamation-circle-fill' : 'check-circle-fill' ?>"></i>
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>
      <form method="POST">
        <?= csrfField() ?>
        <div class="mb-4">
          <label class="form-label">Email address</label>
          <input type="email" name="email" class="form-control" required placeholder="you@example.com">
        </div>
        <button type="submit" class="btn btn-primary w-100">
          <i class="bi bi-envelope-check"></i> Subscribe to Alerts
        </button>
      </form>
      <p class="text-center fs-xs text-muted mt-3 mb-0">
        <i class="bi bi-lock-fill me-1"></i>We never share your email with third parties.
      </p>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
