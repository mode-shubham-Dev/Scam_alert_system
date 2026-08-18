<?php
require_once __DIR__ . '/../src/auth.php';
requireRole('reporter');

$error = '';
$validTypes = ['Phishing Email', 'Fake Job Offer', 'Fraudulent E-commerce', 'Social Media Impersonation', 'Other'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $scamType    = trim($_POST['scam_type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $evidencePath = null;
    $evidenceMime = null;

    if (!in_array($scamType, $validTypes, true)) {
        $error = 'Please select a valid scam type.';
    } elseif ($description === '') {
        $error = 'Description is required.';
    } elseif (strlen($description) > 5000) {
        $error = 'Description must be under 5000 characters.';
    } else {
        if (!empty($_FILES['evidence']['name'])) {
            $validated = validateUpload($_FILES['evidence']);
            if (!$validated['ok']) {
                $error = $validated['error'];
            } else {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $htaccess = $uploadDir . '.htaccess';
                if (!file_exists($htaccess)) {
                    file_put_contents($htaccess,
                        "php_flag engine off\n" .
                        "<FilesMatch \"\\.ph(p[0-9]?|tml)$\">\n" .
                        "  Require all denied\n" .
                        "</FilesMatch>\n" .
                        "Options -Indexes\n"
                    );
                }
                if (move_uploaded_file($_FILES['evidence']['tmp_name'], $uploadDir . $validated['name'])) {
                    $evidencePath = 'uploads/' . $validated['name'];
                    $evidenceMime = $validated['mime'];
                } else {
                    $error = 'Failed to save uploaded file. Please try again.';
                }
            }
        }
        if ($error === '') {
            $pdo = getDB();
            $pdo->prepare(
                "INSERT INTO reports (reporter_id, scam_type, description, evidence_path, evidence_mime)
                 VALUES (?, ?, ?, ?, ?)"
            )->execute([currentUser()['id'], $scamType, $description, $evidencePath, $evidenceMime]);
            // PRG: redirect so browser refresh doesn't re-submit the form
            setFlash('success', 'Your report has been submitted and is pending moderator review.');
            header('Location: my_reports.php');
            exit;
        }
    }
}
require __DIR__ . '/../src/views/header.php';
?>
<div class="row justify-content-center">
  <div class="col-lg-8">

    <div class="pg-header">
      <div>
        <h3 class="pg-title">Submit a Scam Report</h3>
        <p class="pg-subtitle">Help protect the community by reporting what you encountered</p>
      </div>
      <a href="my_reports.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-clipboard-check"></i> My Reports
      </a>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="report-form">
          <?= csrfField() ?>

          <div class="mb-4">
            <label class="form-label">Scam Type</label>
            <select name="scam_type" class="form-select" required>
              <option value="">Select the type of scam…</option>
              <?php foreach ($validTypes as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-4">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="6" required maxlength="5000"
              placeholder="Describe the scam in detail — what happened, how you were contacted, any links, phone numbers, or names involved…"></textarea>
            <div class="fs-xs text-muted mt-1">Be as specific as possible. This helps moderators verify your report faster.</div>
          </div>

          <div class="mb-4">
            <label class="form-label">Evidence <span class="text-muted fw-normal" style="text-transform:none;letter-spacing:0">(optional)</span></label>
            <!-- Custom upload zone -->
            <div class="upload-zone" id="upload-zone">
              <input type="file" name="evidence" id="evidence-file" class="d-none"
                     accept="image/jpeg,image/png,image/gif,image/webp">
              <i class="bi bi-cloud-arrow-up-fill"></i>
              <div class="upload-label">Click to upload or drag & drop</div>
              <div class="upload-hint">JPEG · PNG · GIF · WebP — max 5 MB</div>
              <div class="upload-chosen" id="upload-chosen"></div>
            </div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-send-fill"></i> Submit Report
            </button>
            <a href="my_reports.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const zone   = document.getElementById('upload-zone');
  const input  = document.getElementById('evidence-file');
  const chosen = document.getElementById('upload-chosen');

  zone.addEventListener('click', () => input.click());

  input.addEventListener('change', () => {
    if (input.files[0]) { chosen.textContent = input.files[0].name; chosen.style.display = 'block'; }
  });

  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('is-dragover'); });
  zone.addEventListener('dragleave', ()  => zone.classList.remove('is-dragover'));
  zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('is-dragover');
    if (e.dataTransfer.files[0]) {
      const dt = new DataTransfer();
      dt.items.add(e.dataTransfer.files[0]);
      input.files = dt.files;
      chosen.textContent = e.dataTransfer.files[0].name;
      chosen.style.display = 'block';
    }
  });
})();
</script>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
