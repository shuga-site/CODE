<?php
require_once __DIR__ . '/../app/db.php';
session_start();
if (!empty($_SESSION['user'])) { header('Location: /'); exit; }
$pdo = get_pdo();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = [ 'id'=>$user['id'], 'name'=>$user['name'], 'email'=>$user['email'], 'role'=>$user['role'] ];
            header('Location: /'); exit;
        } else { $error = 'Invalid credentials'; }
    } else { $error = 'Please enter email and password'; }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - University IT Library</title>
  <style>
    :root{--yellow:#FFD400;--blue:#0057B8;--white:#ffffff;--bg:#f8fbff;--text:#0a2540}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial;background:var(--bg);color:var(--text)}
    .wrap{max-width:420px;margin:40px auto;background:var(--white);border:1px solid #e6eef8;border-radius:14px;padding:18px}
    h2{margin-top:0}
    label{display:block;margin:10px 0 6px}
    input{width:100%;padding:10px;border:1px solid #d6e3f5;border-radius:10px}
    .btn{margin-top:12px;display:inline-block;background:var(--blue);color:#fff;border:none;padding:10px 14px;border-radius:10px;font-weight:700}
    .muted{font-size:13px;color:#5b6b7f}
    .error{color:#b00020;margin-top:10px}
    a{color:var(--blue);text-decoration:none}
  </style>
</head>
<body>
  <div class="wrap">
    <h2>Login</h2>
    <?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
      <label>Email</label>
      <input type="email" name="email" required />
      <label>Password</label>
      <input type="password" name="password" required />
      <button class="btn" type="submit">Login</button>
    </form>
    <p class="muted">No account? <a href="/signup.php">Sign up</a></p>
  </div>
</body>
</html>

