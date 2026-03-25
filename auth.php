<?php
session_start();
if (!isset($_SESSION['bossku'])) { header('Location: /login.php'); exit;}if (time() - $_SESSION['bossku'] > 1800) { session_destroy(); header('Location: /login.php'); exit;}
$_SESSION['last'] = time();
