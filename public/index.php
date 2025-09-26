<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/views/header.php';

$q = trim($_GET['q'] ?? '');
$level = $_GET['level'] ?? '';
$subject = trim($_GET['subject'] ?? '');
$year = isset($_GET['year']) && $_GET['year'] !== '' ? (int)$_GET['year'] : null;

$sql = 'SELECT * FROM files WHERE 1=1';
$params = [];
if ($q !== '') { $sql .= ' AND (title LIKE :q OR description LIKE :q)'; $params[':q'] = "%$q%"; }
if ($level !== '') { $sql .= ' AND level = :level'; $params[':level'] = $level; }
if ($subject !== '') { $sql .= ' AND subject LIKE :subject'; $params[':subject'] = "%$subject%"; }
if ($year !== null) { $sql .= ' AND year = :year'; $params[':year'] = $year; }
$sql .= ' ORDER BY created_at DESC LIMIT 200';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$files = $stmt->fetchAll();
?>

<section class="hero">
  <h1>University IT Library</h1>
  <p>Find past questions for HND, Diploma, and Bachelor programs.</p>
</section>

<form class="search-panel" method="get" action="/">
  <input type="text" name="q" placeholder="Search titles/descriptions" value="<?php echo e($q); ?>" />
  <select name="level">
    <option value="">All Levels</option>
    <?php foreach (["HND","Diploma","Bachelor"] as $lvl): ?>
      <option value="<?php echo e($lvl); ?>" <?php echo $level===$lvl?'selected':''; ?>><?php echo e($lvl); ?></option>
    <?php endforeach; ?>
  </select>
  <input type="text" name="subject" placeholder="Subject" value="<?php echo e($subject); ?>" />
  <input type="number" name="year" placeholder="Year" value="<?php echo $year!==null?e((string)$year):''; ?>" />
  <button class="btn btn-primary" type="submit">Search</button>
  <?php if ($q||$level||$subject||$year!==null): ?>
    <a class="btn btn-outline" href="/">Reset</a>
  <?php endif; ?>
  <?php if (isAdmin()): ?>
    <a class="btn btn-accent" href="/upload.php">+ Upload</a>
  <?php endif; ?>
</form>

<div class="grid">
  <?php foreach ($files as $file): ?>
    <div class="card">
      <div class="row">
        <span class="badge"><?php echo e($file['level']); ?></span>
        <span class="muted"><?php echo e($file['subject']); ?><?php if ($file['year']): ?> • <?php echo e((string)$file['year']); ?><?php endif; ?></span>
      </div>
      <h3><?php echo e($file['title']); ?></h3>
      <?php if (!empty($file['description'])): ?>
        <div class="muted"><?php echo e($file['description']); ?></div>
      <?php endif; ?>
      <div class="spacer"></div>
      <div class="row">
        <a class="btn btn-primary" href="/download.php?id=<?php echo (int)$file['id']; ?>">Download</a>
        <span class="muted">Downloads: <?php echo (int)$file['download_count']; ?></span>
        <?php if (isAdmin()): ?>
          <a class="btn btn-outline" href="/admin.php?edit=<?php echo (int)$file['id']; ?>">Edit</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (count($files) === 0): ?>
    <div class="card">
      <h3>No files found</h3>
      <div class="muted">Try adjusting your search or filters.</div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../src/views/footer.php'; ?>
