<?php
require_once __DIR__ . '/../src/auth.php';
requireRole('admin');
$pdo        = getDB();
$me         = currentUser();
$error   = '';
$validRoles = ['reporter', 'moderator', 'admin', 'awareness_manager'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if (isset($_POST['delete_id'])) {
        $deleteId = (int)$_POST['delete_id'];
        if ($deleteId === $me['id']) {
            $error = 'You cannot delete your own account.';
        } else {
            try {
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$deleteId]);
                header('Location: manage_users.php'); exit;
            } catch (PDOException) {
                $error = 'Could not delete this user. They may have linked records.';
            }
        }
    } elseif (isset($_POST['change_role_id'])) {
        $targetId = (int)$_POST['change_role_id'];
        $newRole  = $_POST['new_role'] ?? '';
        if ($targetId === $me['id']) {
            $error = 'You cannot change your own role.';
        } elseif (!in_array($newRole, $validRoles, true)) {
            $error = 'Invalid role selected.';
        } else {
            $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$newRole, $targetId]);
            // PRG: redirect so browser refresh doesn't re-submit the role change
            setFlash('success', 'User role updated successfully.');
            header('Location: manage_users.php'); exit;
        }
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
require __DIR__ . '/../src/views/header.php';
?>
<div class="pg-header">
  <div>
    <h3 class="pg-title">Manage Users</h3>
    <p class="pg-subtitle"><?= count($users) ?> registered user<?= count($users) !== 1 ? 's' : '' ?> in the system</p>
  </div>
  <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-grid-1x2"></i> Dashboard
  </a>
</div>

<?php if ($error): ?><div class="alert alert-danger d-flex gap-2 align-items-center"><i class="bi bi-exclamation-circle-fill"></i><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-body p-0">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>User</th>
          <th>Role</th>
          <th>Joined</th>
          <th style="width:80px" class="text-center">Delete</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): $isSelf = ((int)$u['id'] === $me['id']); ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="avatar-circle" style="width:34px;height:34px;font-size:.8rem;background:var(--ss-navy-2)">
                  <?= strtoupper(mb_substr($u['name'], 0, 1)) ?>
                </div>
                <div>
                  <div class="fw-medium fs-xs" style="color:var(--ss-navy)">
                    <?= htmlspecialchars($u['name']) ?>
                    <?php if ($isSelf): ?><span class="badge bg-primary ms-1">You</span><?php endif; ?>
                  </div>
                  <div class="fs-xs text-muted"><?= htmlspecialchars($u['email']) ?></div>
                </div>
              </div>
            </td>
            <td>
              <?php if ($isSelf): ?>
                <span class="badge bg-secondary"><?= htmlspecialchars($u['role']) ?></span>
              <?php else: ?>
                <form method="POST" class="d-flex align-items-center gap-1 mb-0">
                  <?= csrfField() ?>
                  <input type="hidden" name="change_role_id" value="<?= (int)$u['id'] ?>">
                  <select name="new_role" class="form-select form-select-sm">
                    <?php foreach ($validRoles as $r): ?>
                      <option value="<?= $r ?>" <?= $u['role'] === $r ? 'selected' : '' ?>><?= $r ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap">Save</button>
                </form>
              <?php endif; ?>
            </td>
            <td class="fs-xs text-muted text-nowrap"><?= htmlspecialchars($u['created_at']) ?></td>
            <td class="text-center">
              <?php if (!$isSelf): ?>
                <form method="POST" onsubmit="return confirm('Permanently delete <?= htmlspecialchars(addslashes($u['name'])) ?>?');">
                  <?= csrfField() ?>
                  <input type="hidden" name="delete_id" value="<?= (int)$u['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash3"></i></button>
                </form>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
