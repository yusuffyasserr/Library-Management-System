<?php
require_once __DIR__ . '/../Backend/auth/admin_auth.php';
require_once __DIR__ . '/../Backend/data/repo_db.php';

$mode = $_POST['mode'] ?? '';

if ($mode === 'add') {
  $name = $_POST['name'] ?? '';
  $phone = $_POST['phone'] ?? '';
  $address = $_POST['address'] ?? '';

  $res = db_publisher_create($name, $phone, $address);
  if ($res['ok']) {
    $_SESSION['flash'] = "Publisher added successfully (DB).";
  } else {
    $_SESSION['flash_err'] = $res['message'] ?? 'Failed to add publisher.';
  }

  header("Location: publishers.php");
  exit;
}

if ($mode === 'edit') {
  $id = (int)($_POST['id'] ?? 0);
  $name = $_POST['name'] ?? '';
  $phone = $_POST['phone'] ?? '';
  $address = $_POST['address'] ?? '';

  $res = db_publisher_update($id, $name, $phone, $address);
  if ($res['ok']) {
    $_SESSION['flash'] = "Publisher updated successfully (DB).";
    header("Location: publishers.php");
    exit;
  } else {
    $_SESSION['flash_err'] = $res['message'] ?? 'Failed to update publisher.';
    header("Location: publishers.php?edit=" . $id);
    exit;
  }
}

header("Location: publishers.php");
exit;
