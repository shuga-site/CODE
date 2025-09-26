<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

$user = get_current_user_record();

// Colors: Yellow (#FFD400), Blue (#0057B8), White (#FFFFFF)
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(APP_NAME) ?></title>
    <style>
        :root { --yellow:#FFD400; --blue:#0057B8; --white:#FFFFFF; --text:#0b1b2b; }
        *{ box-sizing:border-box; }
        body{ margin:0; font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu; background:var(--white); color:var(--text); }
        header{ background:linear-gradient(135deg,var(--blue),#0a66c2); color:var(--white); padding:16px 20px; position:sticky; top:0; z-index:10; }
        .brand{ display:flex; align-items:center; gap:12px; font-weight:700; }
        .brand .logo{ width:36px; height:36px; border-radius:8px; background:var(--yellow); display:grid; place-items:center; color:#1a2d4a; font-weight:900; }
        nav a{ color:var(--white); text-decoration:none; margin-left:16px; padding:8px 12px; border-radius:8px; }
        nav a.cta{ background:var(--yellow); color:#1a2d4a; font-weight:700; }
        .container{ max-width:1100px; margin:0 auto; padding:24px 20px; }
        .hero{ background:linear-gradient(180deg,#f7fbff,transparent); border:1px solid #e7eef7; border-radius:14px; padding:24px; display:grid; gap:14px; }
        .grid{ display:grid; grid-template-columns:repeat(12,1fr); gap:14px; }
        .card{ border:1px solid #e7eef7; border-radius:12px; padding:16px; background:#fff; }
        .filters{ display:flex; flex-wrap:wrap; gap:12px; }
        select,input[type="search"],button{ padding:10px 12px; border:1px solid #cfe0f3; border-radius:10px; }
        button{ background:var(--blue); color:#fff; border:none; cursor:pointer; }
        button.alt{ background:var(--yellow); color:#1a2d4a; font-weight:700; }
        .file-list{ display:grid; gap:10px; }
        .file-item{ display:flex; justify-content:space-between; align-items:center; padding:12px; border:1px solid #e7eef7; border-radius:10px; }
        footer{ margin-top:40px; padding:20px; text-align:center; color:#3c5676; }
        .tag{ display:inline-block; padding:4px 8px; border-radius:999px; border:1px dashed #cfe0f3; color:#0b2f57; background:#f7fbff; font-size:12px; }
    </style>
</head>
<body>
<header>
    <div class="container" style="display:flex; align-items:center; justify-content:space-between;">
        <div class="brand">
            <div class="logo">IT</div>
            <div><?= htmlspecialchars(APP_NAME) ?></div>
        </div>
        <nav>
            <a href="index.php">Home</a>
            <?php if ($user): ?>
                <?php if (is_admin()): ?><a href="admin.php" class="cta">Admin</a><?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="signup.php" class="cta">Sign up</a>
            <?php endif; ?>
        </nav>
    </div>
    </header>
<main class="container">
    <section class="hero">
        <h1 style="margin:0;">Find past questions fast</h1>
        <p style="margin:0; color:#3c5676;">Search by program level and subject. Admins can upload and curate resources.</p>
        <form class="filters" method="get" action="search.php">
            <select name="level" aria-label="Program level">
                <option value="">All Levels</option>
                <option>HND</option>
                <option>Diploma</option>
                <option>Bachelor</option>
            </select>
            <input type="search" name="q" placeholder="Search title or description..." />
            <input type="text" name="subject" placeholder="Subject (e.g., Data Structures)" />
            <button type="submit" class="alt">Search</button>
        </form>
    </section>

    <section class="grid" style="margin-top:16px;">
        <div class="card" style="grid-column: span 12;">
            <div class="file-list" id="recent">
                <div class="file-item">
                    <div>
                        <strong>Getting started</strong>
                        <span class="tag">HND</span>
                        <span class="tag">Sample</span>
                    </div>
                    <div>
                        <a href="search.php" style="text-decoration:none;"><button>Browse Library</button></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <footer>
        © <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?>
    </footer>
</main>
</body>
</html>
