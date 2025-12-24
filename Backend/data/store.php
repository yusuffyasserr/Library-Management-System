<?php
// Backend/data/store.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/dummy.php';

/**
 * Init session store once:
 * - books: editable stock (used by browsing + checkout)
 * - publishers: admin-managed list
 * - orders: customer orders
 * - repl: replenishment orders (auto-created when stock is low)
 */
function store_init(): void {
  if (!isset($_SESSION['store_initialized'])) {
    $_SESSION['store_initialized'] = true;

    // Start from dummy books as initial dataset
    $_SESSION['store_books'] = dummy_books();

    // Publishers (admin-managed) - Step 23
    $_SESSION['store_publishers'] = [
      ['id'=>1, 'name'=>'Pearson',   'phone'=>'01000000001', 'address'=>'Cairo'],
      ['id'=>2, 'name'=>'MIT Press', 'phone'=>'01000000002', 'address'=>'Alexandria'],
      ['id'=>3, 'name'=>'Penguin',   'phone'=>'01000000003', 'address'=>'Giza'],
      ['id'=>4, 'name'=>'Oxford',    'phone'=>'01000000004', 'address'=>'Cairo'],
      ['id'=>5, 'name'=>'Harper',    'phone'=>'01000000005', 'address'=>'Tanta'],
    ];
    $_SESSION['store_next_publisher_id'] = 6;

    // Orders + Replenishments
    $_SESSION['store_orders'] = [];          // customer orders
    $_SESSION['store_repl'] = [];            // replenishment orders
    $_SESSION['store_next_order_id'] = 1;
    $_SESSION['store_next_repl_id'] = 1;
  }
}

/* =========================
   BOOKS
   ========================= */

function store_books(): array {
  store_init();
  return $_SESSION['store_books'];
}

function store_find_book(int $id): ?array {
  store_init();
  foreach ($_SESSION['store_books'] as $b) {
    if ((int)$b['id'] === $id) return $b;
  }
  return null;
}

function store_update_book_stock(int $id, int $newStock): bool {
  store_init();
  foreach ($_SESSION['store_books'] as &$b) {
    if ((int)$b['id'] === $id) {
      $b['stock'] = max(0, $newStock);
      return true;
    }
  }
  return false;
}

/**
 * If stock drops below threshold, auto-create replenishment order.
 * This simulates the DB trigger you will do later.
 */
function store_auto_replenish_if_needed(int $bookId): void {
  store_init();

  $threshold = 2;     // when stock <= 2 → create replenishment
  $orderQty  = 10;    // fixed replenishment quantity

  $book = store_find_book($bookId);
  if (!$book) return;

  $stock = (int)$book['stock'];
  if ($stock > $threshold) return;

  // Avoid duplicating unconfirmed replenishments for the same book
  foreach ($_SESSION['store_repl'] as $r) {
    if ((int)$r['book_id'] === $bookId && ($r['status'] ?? '') === 'Pending') return;
  }

  $rid = (int)$_SESSION['store_next_repl_id']++;
  $_SESSION['store_repl'][] = [
    'id' => $rid,
    'book_id' => $bookId,
    'isbn' => $book['isbn'],
    'title' => $book['title'],
    'qty' => $orderQty,
    'status' => 'Pending',
    'created_at' => date('Y-m-d H:i:s'),
  ];
}

/* =========================
   ORDERS (Customer)
   ========================= */

function store_place_order(string $customerUsername, array $cart): array {
  store_init();

  // Build items + validate stock
  $items = [];
  $total = 0;

  foreach ($cart as $bookId => $qty) {
    $bookId = (int)$bookId;
    $qty = (int)$qty;

    $book = store_find_book($bookId);
    if (!$book) continue;

    $stock = (int)$book['stock'];
    if ($stock <= 0) continue;

    if ($qty < 1) $qty = 1;
    if ($qty > $stock) $qty = $stock;

    $unitPrice = (int)$book['price'];
    $sub = $unitPrice * $qty;

    $items[] = [
      'book_id' => $bookId,
      'isbn' => $book['isbn'],
      'title' => $book['title'],
      'qty' => $qty,
      'unit_price' => $unitPrice,
      'subtotal' => $sub,
    ];
    $total += $sub;
  }

  if (count($items) === 0) {
    return ['ok' => false, 'message' => 'Your cart is empty or items are unavailable.'];
  }

  // Reduce stock + simulate trigger
  foreach ($items as $it) {
    $bookId = (int)$it['book_id'];
    $qty = (int)$it['qty'];

    foreach ($_SESSION['store_books'] as &$b) {
      if ((int)$b['id'] === $bookId) {
        $b['stock'] = max(0, (int)$b['stock'] - $qty);
        break;
      }
    }

    // Trigger simulation: if stock low, create replenishment
    store_auto_replenish_if_needed($bookId);
  }

  $orderId = (int)$_SESSION['store_next_order_id']++;

  $_SESSION['store_orders'][] = [
    'id' => $orderId,
    'customer' => $customerUsername,
    'total' => $total,
    'created_at' => date('Y-m-d H:i:s'),
    'items' => $items,
  ];

  // Clear cart after successful order
  $_SESSION['cart'] = [];

  return ['ok' => true, 'order_id' => $orderId];
}

function store_customer_orders(string $customerUsername): array {
  store_init();
  $out = [];
  foreach ($_SESSION['store_orders'] as $o) {
    if (($o['customer'] ?? '') === $customerUsername) $out[] = $o;
  }
  // Latest first
  usort($out, fn($a,$b) => strcmp($b['created_at'], $a['created_at']));
  return $out;
}

/* =========================
   REPLENISHMENTS (Admin)
   ========================= */

function store_replenishments(): array {
  store_init();
  $list = $_SESSION['store_repl'];
  usort($list, fn($a,$b) => strcmp($b['created_at'], $a['created_at']));
  return $list;
}

function store_confirm_replenishment(int $replId): array {
  store_init();

  for ($i = 0; $i < count($_SESSION['store_repl']); $i++) {
    $r = $_SESSION['store_repl'][$i];

    if ((int)$r['id'] === $replId) {

      if (($r['status'] ?? '') !== 'Pending') {
        return ['ok' => false, 'message' => 'This replenishment is already confirmed.'];
      }

      $bookId = (int)$r['book_id'];
      $qty = (int)$r['qty'];

      // Add stock to book
      foreach ($_SESSION['store_books'] as &$b) {
        if ((int)$b['id'] === $bookId) {
          $b['stock'] = (int)$b['stock'] + $qty;
          break;
        }
      }

      // Mark replenishment confirmed
      $_SESSION['store_repl'][$i]['status'] = 'Confirmed';
      $_SESSION['store_repl'][$i]['confirmed_at'] = date('Y-m-d H:i:s');

      return ['ok' => true];
    }
  }

  return ['ok' => false, 'message' => 'Replenishment not found.'];
}

/* =========================
   PUBLISHERS (Admin) - Step 23
   ========================= */

function store_publishers(): array {
  store_init();
  $list = $_SESSION['store_publishers'];
  usort($list, fn($a,$b) => strcmp($a['name'], $b['name']));
  return $list;
}

function store_find_publisher(int $id): ?array {
  store_init();
  foreach ($_SESSION['store_publishers'] as $p) {
    if ((int)$p['id'] === $id) return $p;
  }
  return null;
}

function store_add_publisher(string $name, string $phone, string $address): array {
  store_init();

  $name = trim($name);
  if ($name === '') return ['ok'=>false, 'message'=>'Publisher name is required.'];

  // Unique by name (simple rule)
  foreach ($_SESSION['store_publishers'] as $p) {
    if (mb_strtolower($p['name']) === mb_strtolower($name)) {
      return ['ok'=>false, 'message'=>'Publisher name already exists.'];
    }
  }

  $id = (int)$_SESSION['store_next_publisher_id']++;
  $_SESSION['store_publishers'][] = [
    'id'=>$id,
    'name'=>$name,
    'phone'=>trim($phone),
    'address'=>trim($address)
  ];

  return ['ok'=>true, 'id'=>$id];
}

function store_update_publisher(int $id, string $name, string $phone, string $address): array {
  store_init();

  $name = trim($name);
  if ($name === '') return ['ok'=>false, 'message'=>'Publisher name is required.'];

  // Unique by name (excluding current)
  foreach ($_SESSION['store_publishers'] as $p) {
    if ((int)$p['id'] !== $id && mb_strtolower($p['name']) === mb_strtolower($name)) {
      return ['ok'=>false, 'message'=>'Publisher name already exists.'];
    }
  }

  for ($i=0; $i<count($_SESSION['store_publishers']); $i++) {
    if ((int)$_SESSION['store_publishers'][$i]['id'] === $id) {
      $_SESSION['store_publishers'][$i]['name'] = $name;
      $_SESSION['store_publishers'][$i]['phone'] = trim($phone);
      $_SESSION['store_publishers'][$i]['address'] = trim($address);
      return ['ok'=>true];
    }
  }

  return ['ok'=>false, 'message'=>'Publisher not found.'];
}
