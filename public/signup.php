<?php
require_once __DIR__ . '/../app/db.php';
session_start();
if (!empty($_SESSION['user'])) { header('Location: /'); exit; }
$pdo = get_pdo();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($name && $email && $password) {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,"student")');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_BCRYPT)]);
            header('Location: /login.php'); exit;
        } catch (Throwable $e) {
            $error = 'Email already registered';
        }
    } else { $error = 'Please fill all fields'; }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign up - University IT Library</title>
  <style>
    :root{--yellow:#FFD400;--blue:#0057B8;--white:#ffffff;--bg:#f8fbff;--text:#0a2540}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial;background:var(--bg);color:var(--text)}
    .wrap{max-width:480px;margin:40px auto;background:var(--white);border:1px solid #e6eef8;border-radius:14px;padding:18px}
    h2{margin-top:0}
    label{display:block;margin:10px 0 6px}
    input{width:100%;padding:10px;border:1px solid #d6e3f5;border-radius:10px}
    .btn{margin-top:12px;display:inline-block;background:var(--yellow);color:#0a2540;border:none;padding:10px 14px;border-radius:10px;font-weight:700}
    .error{color:#b00020;margin-top:10px}
    a{color:var(--blue);text-decoration:none}
  </style>
  <script>
    function validateForm(){
      const pwd=document.querySelector('input[name="password"]').value;
      if(pwd.length<6){ alert('Password must be at least 6 characters'); return false; }
      return true;
    }
  </script>
  </head>
<body>
  <div class="wrap">
    <h2>Create your account</h2>
    <?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" onsubmit="return validateForm()">
      <label>Full name</label>
      <input type="text" name="name" required />
      <label>Email</label>
      <input type="email" name="email" required />
      <label>Password</label>
      <input type="password" name="password" required />
      <button class="btn" type="submit">Sign up</button>
    </form>
    <p>Already have an account? <a href="/login.php">Login</a></p>
  </div>
</body>
</html>

