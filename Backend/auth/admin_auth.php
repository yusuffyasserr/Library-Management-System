<?php
// Backend/auth/admin_auth.php
session_start();

require_once __DIR__ . '/../data/repo_db.php';

$user = $_SESSION['user'] ?? null;

if (!$user) {
  header("Location: /LIBRARY%20MANAGEMENT%20SYSTEM/Frontend/public/login.php");
  exit;
}

$username = is_array($user) ? ($user['username'] ?? '') : (string)$user;
if ($username === '') {
  header("Location: /LIBRARY%20MANAGEMENT%20SYSTEM/Frontend/public/login.php");
  exit;
}

$dbUser = db_user_find_by_username($username);
if (!$dbUser || ($dbUser['role'] ?? '') !== 'admin') {
  // Not admin -> block
  header("Location: /LIBRARY%20MANAGEMENT%20SYSTEM/Frontend/public/index.php");
  exit;
}
