<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/config.php';

if (isLoggedIn()) { redirect('/'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_validate($_POST['csrf'] ?? null)) {
    $error = 'Invalid request.';
  } else {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
      $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role'],
      ];
      redirect('/');
    } else {
      $error = 'Invalid email or password.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - IT Library</title>
    <style>
      body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;margin:0;background:#f8fbff;color:#0b1e5b}
      .wrap{max-width:420px;margin:50px auto;background:#fff;border:1px solid #3b82f6;border-radius:12px;box-shadow:0 4px 0 rgba(37,99,235,.08)}
      .head{padding:16px 20px;border-bottom:4px solid #f59e0b;background:linear-gradient(90deg,#0b1e5b,#1e3a8a);color:#fff}
      .body{padding:20px}
      label{font-weight:600;display:block;margin:8px 0 6px}
      input{width:100%;padding:10px;border:1px solid #3b82f6;border-radius:10px}
      .btn{display:inline-block;margin-top:14px;padding:10px 14px;border-radius:10px;background:#2563eb;color:#fff;text-decoration:none;border:none;cursor:pointer}
      .muted{color:#12306b;font-size:13px}
      .error{background:#fff3cd;border:1px solid #f59e0b;color:#7a5a00;padding:10px;border-radius:10px;margin-bottom:12px}
      .links{display:flex;justify-content:space-between;align-items:center;margin-top:10px}
    </style>
    <script>
      function check(){return true}
    </script>
  </head>
  <body>
    <div class="wrap">
      <div class="head"><h2 style="margin:0">Welcome back</h2></div>
      <div class="body">
        <?php if ($error): ?><div class="error"><?php echo e($error); ?></div><?php endif; ?>
        <form method="post" onsubmit="return check()">
          <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>" />
          <label>Email</label>
          <input type="email" name="email" required />
          <label>Password</label>
          <input type="password" name="password" required />
          <button class="btn" type="submit">Login</button>
          <div class="links">
            <span class="muted">Use your registered email and password.</span>
            <a class="muted" href="/signup.php">Create account</a>
          </div>
        </form>
      </div>
    </div>
  </body>
</html>
