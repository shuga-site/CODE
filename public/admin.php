<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/middleware/admin.php';

$editingId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$deletingId = isset($_POST['delete']) ? (int)$_POST['delete'] : 0;
$message = '';
$errors = [];

if ($deletingId > 0 && csrf_validate($_POST['csrf'] ?? null)) {
  $stmt = $pdo->prepare('SELECT stored_name FROM files WHERE id = ?');
  $stmt->execute([$deletingId]);
  $file = $stmt->fetch();
  if ($file) {
    @unlink($UPLOAD_PATH . '/' . $file['stored_name']);
    $pdo->prepare('DELETE FROM files WHERE id = ?')->execute([$deletingId]);
    $message = 'File deleted.';
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save']) && $editingId > 0) {
  if (!csrf_validate($_POST['csrf'] ?? null)) {
    $errors[] = 'Invalid request.';
  } else {
    $title = trim($_POST['title'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $level = $_POST['level'] ?? '';
    $year = isset($_POST['year']) && $_POST['year'] !== '' ? (int)$_POST['year'] : null;
    $description = trim($_POST['description'] ?? '');
    if ($title === '' || $subject === '' || $level === '') {
      $errors[] = 'Title, Subject and Level are required.';
    }
    if (!$errors) {
      $stmt = $pdo->prepare('UPDATE files SET title=?, subject=?, level=?, year=?, description=? WHERE id=?');
      $stmt->execute([$title, $subject, $level, $year, $description, $editingId]);
      $message = 'File updated.';
    }
  }
}

$list = $pdo->query('SELECT * FROM files ORDER BY created_at DESC LIMIT 500')->fetchAll();

require_once __DIR__ . '/../src/views/header.php';
?>
<div class="card">
  <h2>Admin Panel</h2>
  <?php if ($message): ?><div class="badge"><?php echo e($message); ?></div><?php endif; ?>
  <?php if ($errors): ?>
    <div class="card" style="border-color:#f59e0b;background:#fffbe6">
      <?php foreach ($errors as $err): ?><div><?php echo e($err); ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>
  <table class="table">
    <thead>
      <tr>
        <th>Title</th>
        <th>Subject</th>
        <th>Level</th>
        <th>Year</th>
        <th>Downloads</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($list as $row): ?>
      <tr>
        <td><?php echo e($row['title']); ?></td>
        <td><?php echo e($row['subject']); ?></td>
        <td><?php echo e($row['level']); ?></td>
        <td><?php echo e((string)($row['year'] ?? '')); ?></td>
        <td><?php echo (int)$row['download_count']; ?></td>
        <td>
          <a class="btn btn-primary" href="/download.php?id=<?php echo (int)$row['id']; ?>">Download</a>
          <a class="btn btn-outline" href="/admin.php?edit=<?php echo (int)$row['id']; ?>">Edit</a>
          <form method="post" style="display:inline">
            <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>" />
            <button class="btn btn-accent" name="delete" value="<?php echo (int)$row['id']; ?>" onclick="return confirm('Delete this file?')">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if ($editingId > 0):
  $stmt = $pdo->prepare('SELECT * FROM files WHERE id = ?');
  $stmt->execute([$editingId]);
  $edit = $stmt->fetch();
  if ($edit): ?>
  <div class="spacer"></div>
  <div class="card">
    <h3>Edit File</h3>
    <form method="post">
      <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>" />
      <input type="hidden" name="save" value="1" />
      <div class="row">
        <div style="flex:2"><input type="text" name="title" value="<?php echo e($edit['title']); ?>" required /></div>
        <div style="flex:1">
          <select name="level" required>
            <?php foreach (["HND","Diploma","Bachelor"] as $lvl): ?>
              <option value="<?php echo e($lvl); ?>" <?php echo $edit['level']===$lvl?'selected':''; ?>><?php echo e($lvl); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row">
        <div style="flex:1"><input type="text" name="subject" value="<?php echo e($edit['subject']); ?>" required /></div>
        <div style="flex:1"><input type="number" name="year" value="<?php echo e((string)($edit['year'] ?? '')); ?>" /></div>
      </div>
      <textarea name="description" rows="3"><?php echo e($edit['description'] ?? ''); ?></textarea>
      <button class="btn btn-primary" type="submit">Save changes</button>
    </form>
  </div>
  <?php endif; endif; ?>

<?php require_once __DIR__ . '/../src/views/footer.php'; ?>
