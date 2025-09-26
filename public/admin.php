<?php
require_once __DIR__ . '/../app/db.php';
session_start();
$user = $_SESSION['user'] ?? null;
if (!$user || $user['role'] !== 'admin') { http_response_code(403); exit('Admins only'); }
$pdo = get_pdo();
$config = load_config();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='upload') {
    $title = trim($_POST['title'] ?? '');
    $level = trim($_POST['level'] ?? '');
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if (!$title || !$level || !$subject_id) {
        $error = 'Please fill all required fields';
    } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a file';
    } else {
        $f = $_FILES['file'];
        $mime = mime_content_type($f['tmp_name']);
        $allowed = $config['app']['allowed_mime_types'];
        $denyImages = str_starts_with($mime, 'image/');
        if ($denyImages || !in_array($mime, $allowed, true)) {
            $error = 'Only PDF or document files allowed (no images).';
        } else {
            $maxBytes = $config['app']['max_upload_mb'] * 1024 * 1024;
            if ($f['size'] > $maxBytes) {
                $error = 'File too large.';
            } else {
                $stored = bin2hex(random_bytes(16)).'-'.preg_replace('/[^A-Za-z0-9_.-]/','_', $f['name']);
                $dest = rtrim($config['app']['upload_dir'], '/').'/'.$stored;
                if (!move_uploaded_file($f['tmp_name'], $dest)) {
                    $error = 'Failed to save file.';
                } else {
                    $stmt = $pdo->prepare('INSERT INTO files (title, level, subject_id, original_name, stored_name, mime_type, file_size, description, uploaded_by) VALUES (?,?,?,?,?,?,?,?,?)');
                    $stmt->execute([$title,$level,$subject_id,$f['name'],$stored,$mime,$f['size'],$description,$user['id']]);
                    $success = 'File uploaded successfully';
                }
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('SELECT * FROM files WHERE id=?');
    $stmt->execute([$id]);
    if ($row = $stmt->fetch()) {
        @unlink(rtrim($config['app']['upload_dir'],'/').'/'.$row['stored_name']);
        $pdo->prepare('DELETE FROM files WHERE id=?')->execute([$id]);
        header('Location: /admin.php'); exit;
    }
}

$subjects = $pdo->query('SELECT id,name FROM subjects ORDER BY name')->fetchAll();
$list = $pdo->query('SELECT f.*, s.name AS subject_name FROM files f INNER JOIN subjects s ON s.id=f.subject_id ORDER BY f.created_at DESC LIMIT 50')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - University IT Library</title>
  <style>
    :root{--yellow:#FFD400;--blue:#0057B8;--white:#ffffff;--bg:#f8fbff;--text:#0a2540}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial;background:var(--bg);color:var(--text)}
    header{background:linear-gradient(90deg,var(--blue),#0b4aa0);color:var(--white);padding:14px 20px;display:flex;justify-content:space-between}
    .container{max-width:1100px;margin:24px auto;padding:0 16px}
    .card{background:var(--white);border:1px solid #e6eef8;border-radius:12px;padding:16px;margin-bottom:16px}
    label{display:block;margin:10px 0 6px}
    input,select,textarea{width:100%;padding:10px;border:1px solid #d6e3f5;border-radius:10px}
    .btn{display:inline-block;background:var(--yellow);color:#0a2540;padding:10px 14px;border-radius:10px;font-weight:700;border:none}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #eef5ff;text-align:left}
    .danger{color:#b00020}
    a{color:var(--blue);text-decoration:none}
  </style>
</head>
<body>
  <header>
    <div><strong>Admin Panel</strong></div>
    <div><a style="color:#fff" href="/">Home</a> · <a style="color:#fff" href="/logout.php">Logout</a></div>
  </header>
  <div class="container">
    <?php if($error): ?><div class="card" style="border-color:#ffd0d0;color:#b00020"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if($success): ?><div class="card" style="border-color:#d0ffd6;color:#0a6b2a"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="card">
      <h3>Upload file</h3>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload" />
        <label>Title</label>
        <input type="text" name="title" required />
        <label>Level</label>
        <select name="level" required>
          <option value="">Select level</option>
          <option>HND</option>
          <option>Diploma</option>
          <option>Bachelor</option>
        </select>
        <label>Subject</label>
        <select name="subject_id" required>
          <option value="">Select subject</option>
          <?php foreach($subjects as $s): ?>
          <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <label>Description</label>
        <textarea name="description" rows="3" placeholder="Optional"></textarea>
        <label>File (PDF or document; no images)</label>
        <input type="file" name="file" required />
        <button class="btn" type="submit">Upload</button>
      </form>
    </div>

    <div class="card">
      <h3>Recent files</h3>
      <table>
        <thead><tr><th>Title</th><th>Level</th><th>Subject</th><th>Downloads</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach($list as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['level']) ?></td>
            <td><?= htmlspecialchars($row['subject_name']) ?></td>
            <td><?= (int)$row['download_count'] ?></td>
            <td>
              <a href="/download.php?id=<?= $row['id'] ?>">Download</a>
              &nbsp;|&nbsp;
              <a class="danger" href="/admin.php?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this file?')">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>

