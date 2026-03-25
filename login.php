<?php
session_start();
$config = require 'conf/conf.php';

if (isset($_SESSION['login'])) {
  header('Location: index.php');
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($_POST['username'] === $config['username'] &&
      password_verify($_POST['password'], $config['password'])) {

    $_SESSION['login'] = true;
    $_SESSION['last'] = time();

    header('Location: index.php');
    exit;
  } else {
    $error = "Login salah";
  }
}
?>

<form method="POST">
  <h2>Server Login</h2>
  <?php if($error) echo "<p>$error</p>"; ?>
  <input name="username" placeholder="Username" required>
  <input name="password" type="password" placeholder="Password" required>
  <button>Login</button>
</form>
