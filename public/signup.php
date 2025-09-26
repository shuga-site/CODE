<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/config.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_validate($_POST['csrf'] ?? null)) {
    $errors[] = 'Invalid request. Please refresh and try again.';
  } else {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($fullName === '' || $email === '' || $password === '') {
      $errors[] = 'All fields are required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = 'Enter a valid email address.';
    }
    if ($password !== $confirm) {
      $errors[] = 'Passwords do not match.';
    }
    if (strlen($password) < 8) {
      $errors[] = 'Password must be at least 8 characters.';
    }

    if (!$errors) {
      $exists = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
      $exists->execute([$email]);
      if ($exists->fetch()) {
        $errors[] = 'Email already registered.';
      } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, "user")');
        $stmt->execute([$fullName, $email, $hash]);
        $_SESSION['user'] = [
          'id' => (int)$pdo->lastInsertId(),
          'full_name' => $fullName,
          'email' => $email,
          'role' => 'user',
        ];
        redirect('/');
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sign up - IT Library</title>
    <style>
      body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;margin:0;background:#f8fbff;color:#0b1e5b}
      .wrap{max-width:520px;margin:32px auto;background:#fff;border:1px solid #3b82f6;border-radius:12px;box-shadow:0 4px 0 rgba(37,99,235,.08)}
      .head{padding:16px 20px;border-bottom:4px solid #f59e0b;background:linear-gradient(90deg,#0b1e5b,#1e3a8a);color:#fff}
      .body{padding:20px}
      label{font-weight:600;display:block;margin:8px 0 6px}
      input{width:100%;padding:10px;border:1px solid #3b82f6;border-radius:10px}
      .row{display:flex;gap:10px}
      .btn{display:inline-block;margin-top:14px;padding:10px 14px;border-radius:10px;background:#2563eb;color:#fff;text-decoration:none;border:none;cursor:pointer}
      .muted{color:#12306b;font-size:13px}
      .error{background:#fff3cd;border:1px solid #f59e0b;color:#7a5a00;padding:10px;border-radius:10px;margin-bottom:12px}
      .links{display:flex;justify-content:space-between;align-items:center;margin-top:10px}
    </style>
    <script>
      function validateForm(){
        const pwd=document.getElementById('password').value;
        const cf=document.getElementById('confirm').value;
        if(pwd!==cf){alert('Passwords do not match');return false}
        if(pwd.length<8){alert('Password must be at least 8 characters');return false}
        return true
      }
    </script>
  </head>
  <body>
    <div class="wrap">
      <div class="head"><h2 style="margin:0">Create your account</h2></div>
      <div class="body">
        <?php if ($errors): ?>
          <div class="error">
            <?php foreach ($errors as $err): ?>
              <div><?php echo e($err); ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <form method="post" onsubmit="return validateForm()">
          <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>" />
          <label>Full name</label>
          <input type="text" name="full_name" required />
          <label>Email</label>
          <input type="email" name="email" required />
          <div class="row">
            <div style="flex:1">
              <label>Password</label>
              <input id="password" type="password" name="password" required />
            </div>
            <div style="flex:1">
              <label>Confirm</label>
              <input id="confirm" type="password" name="confirm" required />
            </div>
          </div>
          <button class="btn" type="submit">Sign up</button>
          <div class="links">
            <span class="muted">By signing up you agree to our academic use policy.</span>
            <a class="muted" href="/login.php">Have an account? Login</a>
          </div>
        </form>
      </div>
    </div>
  </body>
</html>
