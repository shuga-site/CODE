<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

if (!isLoggedIn()) {
  redirect('/login.php');
}

?>
