<?php
session_start();

require_once __DIR__ . '/../../Backend/data/repo_db.php';

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

function go($to = 'cart.php') {
  header("Location: $to");
  exit;
}

if ($action === 'add' && $id > 0) {
  $book = db_book_find($id);
  if (!$book) go('index.php');

  $stock = (int)$book['stock'];
  if ($stock <= 0) go('index.php');

  $current = (int)($_SESSION['cart'][$id] ?? 0);
  $newQty = $current + 1;

  if ($newQty > $stock) $newQty = $stock; // clamp to DB stock

  $_SESSION['cart'][$id] = $newQty;
  go('cart.php');
}

if ($action === 'remove' && $id > 0) {
  unset($_SESSION['cart'][$id]);
  go('cart.php');
}

if ($action === 'clear') {
  $_SESSION['cart'] = [];
  go('cart.php');
}

// Update quantities from cart page
if ($action === 'update') {
  foreach ($_POST['qty'] ?? [] as $bookId => $qty) {
    $bookId = (int)$bookId;
    $qty = (int)$qty;

    if ($qty <= 0) {
      unset($_SESSION['cart'][$bookId]);
      continue;
    }

    $book = db_book_find($bookId);
    if (!$book) {
      unset($_SESSION['cart'][$bookId]);
      continue;
    }

    $stock = (int)$book['stock'];
    if ($stock <= 0) {
      unset($_SESSION['cart'][$bookId]);
      continue;
    }

    if ($qty > $stock) $qty = $stock;
    $_SESSION['cart'][$bookId] = $qty;
  }

  go('cart.php');
}

go('cart.php');
