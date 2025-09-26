<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/middleware/admin.php';

$errors = [];
$ok = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_validate($_POST['csrf'] ?? null)) {
    $errors[] = 'Invalid request. Please refresh and try again.';
  } else {
    $title = trim($_POST['title'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $level = $_POST['level'] ?? '';
    $year = isset($_POST['year']) && $_POST['year'] !== '' ? (int)$_POST['year'] : null;
    $description = trim($_POST['description'] ?? '');

    if ($title === '' || $subject === '' || $level === '') {
      $errors[] = 'Title, Subject and Level are required.';
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
      $errors[] = 'Please select a file.';
    } else {
      $file = $_FILES['file'];
      global $MAX_UPLOAD_BYTES, $ALLOWED_EXTENSIONS, $DISALLOWED_MIME_PREFIXES, $UPLOAD_PATH;
      if ($file['size'] > $MAX_UPLOAD_BYTES) {
        $errors[] = 'File is too large.';
      }
      $originalName = $file['name'];
      $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
      if (!in_array($ext, $ALLOWED_EXTENSIONS, true)) {
        $errors[] = 'Only document files are allowed (PDF, DOC, PPT, XLS, TXT).';
      }
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime = finfo_file($finfo, $file['tmp_name']);
      finfo_close($finfo);
      if (isDisallowedMime($mime, $DISALLOWED_MIME_PREFIXES)) {
        $errors[] = 'Image files are not allowed.';
      }
      if (!$errors) {
        $stored = generateStoredFileName($originalName);
        $dest = $UPLOAD_PATH . '/' . $stored;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
          $errors[] = 'Failed to store uploaded file.';
        } else {
          $stmt = $pdo->prepare('INSERT INTO files (title, subject, level, year, description, original_name, stored_name, mime_type, file_size, uploaded_by) VALUES (?,?,?,?,?,?,?,?,?,?)');
          $stmt->execute([$title, $subject, $level, $year, $description, $originalName, $stored, $mime, (int)$file['size'], currentUser()['id']]);
          $ok = true;
        }
      }
    }
  }
}

require_once __DIR__ . '/../src/views/header.php';
?>
<div class="card">
  <h2>Upload Document</h2>
  <p class="muted">Admins can upload PDF and other document types. Images are blocked.</p>
  <?php if ($ok): ?><div class="badge">Upload successful</div><?php endif; ?>
  <?php if ($errors): ?>
    <div class="card" style="border-color:#f59e0b;background:#fffbe6">
      <?php foreach ($errors as $err): ?><div><?php echo e($err); ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>" />
    <div class="row">
      <div style="flex:2"><input type="text" name="title" placeholder="Title" required /></div>
      <div style="flex:1">
        <select name="level" required>
          <option value="">Level</option>
          <option>HND</option>
          <option>Diploma</option>
          <option>Bachelor</option>
        </select>
      </div>
    </div>
    <div class="row">
      <div style="flex:1"><input type="text" name="subject" placeholder="Subject" required /></div>
      <div style="flex:1"><input type="number" name="year" placeholder="Year" /></div>
    </div>
    <textarea name="description" placeholder="Description (optional)" rows="3"></textarea>
    <div class="row">
      <input type="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required />
      <button class="btn btn-primary" type="submit">Upload</button>
    </div>
  </form>
</div>
<?php require_once __DIR__ . '/../src/views/footer.php'; ?>
