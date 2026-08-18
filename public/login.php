<?php
require_once __DIR__ . '/../src/auth.php';
if (currentUser()) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $result = loginUser(trim($_POST['email'] ?? ''), $_POST['password'] ?? '');
    if ($result['ok']) { header('Location: index.php'); exit; }
    $error = $result['error'];
}
require __DIR__ . '/../src/views/header.php';
?>
<div class="auth-wrap">
  <div class="auth-logo-mark"><i class="bi bi-shield-fill-check"></i></div>
  <h1 class="auth-heading">Welcome back</h1>
  <p class="auth-sub">Sign in to your ScamShield account to continue.</p>

  <div class="card auth-card">
    <div class="card-body">
      <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
          <i class="bi bi-exclamation-circle-fill"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <form method="POST" autocomplete="on">
        <?= csrfField() ?>
        <div class="mb-3">
          <label class="form-label">Email address</label>
          <input type="email" name="email" class="form-control" required autocomplete="email" placeholder="you@example.com">
        </div>
        <div class="mb-4">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required autocomplete="current-password" placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-primary w-100">
          <i class="bi bi-box-arrow-in-right"></i> Sign In
        </button>
      </form>
      <p class="text-center fs-xs text-muted mt-3 mb-0">
        No account? <a href="register.php">Create one</a>
      </p>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
