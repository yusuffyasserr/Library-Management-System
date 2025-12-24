<?php
require_once __DIR__ . '/../Backend/auth/admin_auth.php';
require_once __DIR__ . '/../Backend/data/repo_db.php';

$mode = $_POST['mode'] ?? '';

if ($mode === 'create') {
  $bookId = (int)($_POST['book_id'] ?? 0);
  $qty = (int)($_POST['qty'] ?? 0);

  $res = db_replenishment_create($bookId, $qty);
  if ($res['ok']) $_SESSION['flash'] = "Replenishment created (DB).";
  else $_SESSION['flash_err'] = $res['message'] ?? 'Failed to create replenishment.';

  header("Location: replenishments.php");
  exit;
}

if ($mode === 'confirm') {
  $id = (int)($_POST['id'] ?? 0);

  $res = db_replenishment_confirm($id);
  if ($res['ok']) $_SESSION['flash'] = "Replenishment confirmed. Stock updated.";
  else $_SESSION['flash_err'] = $res['message'] ?? 'Failed to confirm replenishment.';

  header("Location: replenishments.php");
  exit;
}

header("Location: replenishments.php");
exit;
