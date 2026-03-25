<?php
session_start();

// belum login
if (!isset($_SESSION['login'])) {
  header('Location: /login.php');
  exit;
}

// session timeout (30 menit)
if (time() - $_SESSION['last'] > 1800) {
  session_destroy();
  header('Location: /login.php');
  exit;
}

$_SESSION['last'] = time();
