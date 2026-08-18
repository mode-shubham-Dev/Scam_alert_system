<?php
require_once __DIR__ . '/../src/auth.php';
if (currentUser()) { header('Location: index.php'); exit; }
$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($name === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } else {
        $result = registerUser($name, $email, $password);
        $result['success']
            ? $success = $result['message'] . ' You can now log in.'
            : $error   = $result['message'];
    }
}
require __DIR__ . '/../src/views/header.php';
?>
<div class="auth-wrap">
  <div class="auth-logo-mark"><i class="bi bi-shield-fill-check"></i></div>
  <h1 class="auth-heading">Create an account</h1>
  <p class="auth-sub">
    Public sign-up creates a <strong>Reporter</strong> account.<br>
    Admins can assign other roles from the users panel.
  </p>

  <div class="card auth-card">
    <div class="card-body">
      <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
          <i class="bi bi-exclamation-circle-fill"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
          <i class="bi bi-check-circle-fill"></i>
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>
      <form method="POST" autocomplete="on">
        <?= csrfField() ?>
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" required maxlength="100" placeholder="Jane Smith">
        </div>
        <div class="mb-3">
          <label class="form-label">Email address</label>
          <input type="email" name="email" class="form-control" required autocomplete="email" placeholder="you@example.com">
        </div>
        <div class="mb-4">
          <label class="form-label">Password <span class="text-muted fw-normal" style="text-transform:none;letter-spacing:0">(min. 8 characters)</span></label>
          <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password" placeholder="Choose a strong password">
        </div>
        <button type="submit" class="btn btn-primary w-100">
          <i class="bi bi-person-plus-fill"></i> Create Account
        </button>
      </form>
      <p class="text-center fs-xs text-muted mt-3 mb-0">
        Already have an account? <a href="login.php">Sign in</a>
      </p>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
