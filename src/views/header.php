<?php require_once __DIR__ . '/../auth.php'; $user = currentUser(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ScamShield</title>
<!-- Inter font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<!-- ScamShield Design System -->
<link href="assets/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container">

    <!-- Brand -->
    <a class="navbar-brand" href="index.php">
      <span class="brand-box"><i class="bi bi-shield-fill-check"></i></span>
      ScamShield
    </a>

    <!-- Nav links -->
    <div class="navbar-nav me-auto d-flex flex-row flex-wrap gap-1">
      <a class="nav-link" href="alerts.php"><i class="bi bi-bell"></i> Alerts</a>

      <?php if ($user): ?>
        <?php if ($user['role'] === 'reporter'): ?>
          <a class="nav-link" href="report_form.php"><i class="bi bi-plus-circle"></i> Submit Report</a>
          <a class="nav-link" href="my_reports.php"><i class="bi bi-clipboard-check"></i> My Reports</a>

        <?php elseif ($user['role'] === 'moderator'): ?>
          <a class="nav-link" href="review_queue.php"><i class="bi bi-list-check"></i> Review Queue</a>
          <a class="nav-link" href="review_history.php"><i class="bi bi-clock-history"></i> Review History</a>

        <?php elseif ($user['role'] === 'admin'): ?>
          <a class="nav-link" href="dashboard.php"><i class="bi bi-grid-1x2"></i> Dashboard</a>
          <a class="nav-link" href="manage_users.php"><i class="bi bi-people"></i> Users</a>

        <?php elseif ($user['role'] === 'awareness_manager'): ?>
          <a class="nav-link" href="publish_alert.php"><i class="bi bi-megaphone"></i> Publish Alert</a>
          <a class="nav-link" href="verified_reports.php"><i class="bi bi-shield-check"></i> Verified Reports</a>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- Right side -->
    <div class="nav-end">
      <?php if ($user): ?>
        <div class="avatar-circle"><?= strtoupper(mb_substr($user['name'], 0, 1)) ?></div>
        <div class="d-none d-md-flex flex-column" style="line-height:1.2">
          <span class="nav-user-name"><?= htmlspecialchars($user['name']) ?></span>
          <span class="role-pill mt-1"><?= htmlspecialchars(str_replace('_', ' ', $user['role'])) ?></span>
        </div>
        <a class="btn-nav-out" href="logout.php">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      <?php else: ?>
        <a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a>
        <a class="btn-primary btn btn-sm" href="register.php">Register</a>
      <?php endif; ?>
    </div>

  </div>
</nav>

<div class="container mt-4 mb-5">
<?php $flash = getFlash(); if ($flash): ?>
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill' ?>"></i>
    <?= htmlspecialchars($flash['message']) ?>
  </div>
<?php endif; ?>
