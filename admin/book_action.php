<?php
require_once __DIR__ . '/../Backend/auth/admin_auth.php';
require_once __DIR__ . '/../Backend/data/repo_db.php';

$id = (int)($_POST['id'] ?? 0);
$price = (int)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);

if ($id > 0) {
  db_book_update($id, $price, $stock);
  $_SESSION['flash'] = "Book updated successfully (DB).";
}

header("Location: books.php");
exit;
