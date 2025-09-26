<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>University IT Library</title>
    <link rel="stylesheet" href="/assets/css/styles.css" />
  </head>
  <body>
    <header class="site-header">
      <div class="container header-inner">
        <a class="brand" href="/">IT Library</a>
        <nav class="nav">
          <a href="/">Browse</a>
          <?php if (isAdmin()): ?>
            <a href="/upload.php">Upload</a>
            <a href="/admin.php">Admin</a>
          <?php endif; ?>
          <?php if (!isLoggedIn()): ?>
            <a href="/login.php" class="btn btn-outline">Login</a>
            <a href="/signup.php" class="btn btn-accent">Sign up</a>
          <?php else: ?>
            <span class="welcome">Hello, <?php echo e(currentUser()['full_name'] ?? 'User'); ?></span>
            <a href="/logout.php" class="btn btn-outline">Logout</a>
          <?php endif; ?>
        </nav>
      </div>
    </header>
    <main class="container main-content">
