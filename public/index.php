<?php
require_once __DIR__ . '/../app/db.php';
session_start();
ensure_upload_dir();
$config = load_config();

$user = $_SESSION['user'] ?? null;

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>University IT Library</title>
    <style>
        :root{--yellow:#FFD400;--blue:#0057B8;--white:#ffffff;--bg:#f8fbff;--text:#0a2540}
        *{box-sizing:border-box}
        body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial; color:var(--text); background:var(--bg)}
        a{color:var(--blue);text-decoration:none}
        header{background:linear-gradient(90deg,var(--blue),#0b4aa0);color:var(--white);padding:14px 20px;display:flex;align-items:center;justify-content:space-between}
        .brand{display:flex;align-items:center;gap:12px;font-weight:700}
        .brand .logo{width:36px;height:36px;border-radius:50%;background:var(--yellow);display:grid;place-items:center;color:#0b4aa0;font-weight:900}
        nav a{margin-left:16px;color:var(--white);opacity:.95}
        .container{max-width:1100px;margin:24px auto;padding:0 16px}
        .hero{background:var(--white);border:1px solid #e6eef8;border-radius:14px;padding:20px;display:grid;gap:14px}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        @media (max-width: 860px){.grid{grid-template-columns:1fr}}
        .card{background:var(--white);border:1px solid #e6eef8;border-radius:12px;padding:16px}
        .btn{display:inline-block;background:var(--yellow);color:#0a2540;padding:10px 14px;border-radius:10px;font-weight:700}
        .btn.blue{background:var(--blue);color:var(--white)}
        .search-bar{display:grid;grid-template-columns:1fr 180px 180px auto;gap:10px}
        @media (max-width: 800px){.search-bar{grid-template-columns:1fr 1fr}}
        input,select{width:100%;padding:10px;border:1px solid #d6e3f5;border-radius:10px}
        .list-item{display:flex;align-items:flex-start;justify-content:space-between;padding:12px;border-bottom:1px solid #eef5ff}
        .badge{background:#eaf3ff;color:var(--blue);padding:4px 10px;border-radius:999px;font-size:12px;margin-left:6px}
        footer{padding:24px;color:#6a7c93;text-align:center}
    </style>
    <script>
        function goLogin(){ window.location.href='login.php'; }
        function goSignup(){ window.location.href='signup.php'; }
    </script>
</head>
<body>
<header>
    <div class="brand">
        <div class="logo">IT</div>
        <div>University IT Library</div>
    </div>
    <nav>
        <?php if($user): ?>
            <span>Welcome, <?= htmlspecialchars($user['name']) ?></span>
            <?php if($user['role']==='admin'): ?>
                <a href="/admin.php">Admin</a>
            <?php endif; ?>
            <a href="/logout.php">Logout</a>
        <?php else: ?>
            <a href="javascript:goLogin()">Login</a>
            <a href="javascript:goSignup()">Sign up</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <section class="hero">
        <h2>Find past questions fast</h2>
        <form class="search-bar" method="get" action="/search.php">
            <input type="text" name="q" placeholder="Search by title, description, keywords" />
            <select name="level">
                <option value="">All Levels</option>
                <option>HND</option>
                <option>Diploma</option>
                <option>Bachelor</option>
            </select>
            <select name="subject" id="subjectSelect">
                <option value="">All Subjects</option>
            </select>
            <button class="btn blue" type="submit">Search</button>
        </form>
    </section>

    <div class="grid" style="margin-top:16px">
        <div class="card">
            <h3>Latest uploads</h3>
            <div id="latestList"></div>
        </div>
        <div class="card">
            <h3>Top downloads</h3>
            <div id="topList"></div>
        </div>
    </div>
</div>

<footer>© <?= date('Y') ?> University IT Library</footer>

<script>
// Inline JS to load subjects and lists
fetch('/subjects.php').then(r=>r.json()).then(data=>{
  const sel=document.getElementById('subjectSelect');
  data.forEach(s=>{const o=document.createElement('option');o.value=s.id;o.textContent=s.name;sel.appendChild(o);});
});

function renderList(elId, items){
  const el=document.getElementById(elId);
  el.innerHTML='';
  if(!items.length){ el.innerHTML='<div>No items yet.</div>'; return; }
  items.forEach(it=>{
    const div=document.createElement('div');
    div.className='list-item';
    div.innerHTML = `<div><div style="font-weight:700">${it.title} <span class="badge">${it.level}</span></div>
      <div style="font-size:13px;color:#5b6b7f">${it.subject_name || ''}</div></div>
      <div><a class="btn" href="/download.php?id=${it.id}">Download</a></div>`;
    el.appendChild(div);
  });
}

fetch('/latest.php').then(r=>r.json()).then(d=>renderList('latestList', d));
fetch('/top.php').then(r=>r.json()).then(d=>renderList('topList', d));
</script>
</body>
</html>

