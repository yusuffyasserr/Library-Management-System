<?php
// Backend/auth/auth.php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user'])) {
  $redirect = urlencode($_SERVER['REQUEST_URI']);
  header("Location: /LIBRARY%20MANAGEMENT%20SYSTEM/Frontend/public/login.php?redirect=$redirect");
  exit;
}
