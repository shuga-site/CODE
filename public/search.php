<?php
require_once __DIR__ . '/../app/db.php';
$pdo = get_pdo();

$q = trim($_GET['q'] ?? '');
$level = trim($_GET['level'] ?? '');
$subject = trim($_GET['subject'] ?? '');

$where = [];
$params = [];
if ($q !== '') { $where[] = '(f.title LIKE ? OR f.description LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
if ($level !== '') { $where[] = 'f.level = ?'; $params[] = $level; }
if ($subject !== '') { $where[] = 'f.subject_id = ?'; $params[] = $subject; }
$sql = 'SELECT f.*, s.name AS subject_name FROM files f INNER JOIN subjects s ON s.id=f.subject_id';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY f.created_at DESC LIMIT 100';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Search - University IT Library</title>
  <style>
    :root{--yellow:#FFD400;--blue:#0057B8;--white:#ffffff;--bg:#f8fbff;--text:#0a2540}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial;background:var(--bg);color:var(--text)}
    header{background:linear-gradient(90deg,var(--blue),#0b4aa0);color:var(--white);padding:14px 20px}
    .container{max-width:1100px;margin:24px auto;padding:0 16px}
    .item{display:flex;align-items:flex-start;justify-content:space-between;padding:12px;border:1px solid #e6eef8;border-radius:12px;background:var(--white);margin-bottom:12px}
    .btn{display:inline-block;background:var(--yellow);color:#0a2540;padding:10px 14px;border-radius:10px;font-weight:700}
    .badge{background:#eaf3ff;color:var(--blue);padding:4px 10px;border-radius:999px;font-size:12px;margin-left:6px}
  </style>
</head>
<body>
  <header><strong>Search results</strong></header>
  <div class="container">
    <?php if(!$rows): ?>
      <div>No results found.</div>
    <?php else: foreach($rows as $it): ?>
      <div class="item">
        <div>
          <div style="font-weight:700"><?= htmlspecialchars($it['title']) ?> <span class="badge"><?= htmlspecialchars($it['level']) ?></span></div>
          <div style="font-size:13px;color:#5b6b7f"><?= htmlspecialchars($it['subject_name']) ?></div>
        </div>
        <div>
          <a class="btn" href="/download.php?id=<?= $it['id'] ?>">Download</a>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</body>
</html>

