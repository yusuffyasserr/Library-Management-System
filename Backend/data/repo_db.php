<?php
// Backend/data/repo_db.php
require_once __DIR__ . '/../config/db.php';   // provides $pdo

/* =========================
   USERS (DB)
   ========================= */

function db_user_find_by_username(string $username): ?array {
  global $pdo;
  // include password_hash so login can verify credentials
  $stmt = $pdo->prepare("SELECT id, username, role, password_hash FROM users WHERE username = ? LIMIT 1");
  $stmt->execute([$username]);
  $row = $stmt->fetch();
  return $row ?: null;
}

/* =========================
   BOOKS (DB)
   ========================= */

function db_books_list(): array {
  global $pdo;

  $sql = "
    SELECT
      b.id, b.isbn, b.title, b.authors, b.category,
      b.published_year AS year,
      b.price, b.stock,
      p.name AS publisher
    FROM books b
    JOIN publishers p ON p.id = b.publisher_id
    ORDER BY b.id ASC
  ";
  $stmt = $pdo->query($sql);
  return $stmt->fetchAll();
}

function db_book_find(int $id): ?array {
  global $pdo;

  $sql = "
    SELECT
      b.id, b.isbn, b.title, b.authors, b.category,
      b.published_year AS year,
      b.price, b.stock,
      p.name AS publisher
    FROM books b
    JOIN publishers p ON p.id = b.publisher_id
    WHERE b.id = ?
    LIMIT 1
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  return $row ?: null;
}

/* =========================
   PUBLISHERS (DB)
   ========================= */

function db_publishers_list(): array {
  global $pdo;
  $stmt = $pdo->query("SELECT id, name, phone, address FROM publishers ORDER BY name ASC");
  return $stmt->fetchAll();
}

function db_publisher_find(int $id): ?array {
  global $pdo;
  $stmt = $pdo->prepare("SELECT id, name, phone, address FROM publishers WHERE id = ? LIMIT 1");
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  return $row ?: null;
}
/* =========================
   ORDERS (DB)
   ========================= */

function db_order_create(int $userId, array $cart): array {
  global $pdo;

  if ($userId <= 0) return ['ok'=>false, 'message'=>'Invalid user.'];
  if (!$cart || count($cart) === 0) return ['ok'=>false, 'message'=>'Cart is empty.'];

  try {
    $pdo->beginTransaction();

    // 1) Recalculate from DB + validate stock
    $items = [];
    $total = 0;

    foreach ($cart as $bookId => $qty) {
      $bookId = (int)$bookId;
      $qty = (int)$qty;
      if ($qty < 1) $qty = 1;

      // lock row for safe stock update
      $stmt = $pdo->prepare("SELECT id, price, stock FROM books WHERE id = ? FOR UPDATE");
      $stmt->execute([$bookId]);
      $book = $stmt->fetch();

      if (!$book) continue;

      $stock = (int)$book['stock'];
      if ($stock <= 0) continue;

      if ($qty > $stock) $qty = $stock;

      $unit = (int)$book['price'];
      $sub = $unit * $qty;

      $total += $sub;
      $items[] = [
        'book_id'=>$bookId,
        'qty'=>$qty,
        'unit_price'=>$unit,
        'subtotal'=>$sub,
        'stock'=>$stock
      ];
    }

    if (count($items) === 0) {
      $pdo->rollBack();
      return ['ok'=>false, 'message'=>'No available items to order.'];
    }

    // 2) Insert order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
    $stmt->execute([$userId, $total]);
    $orderId = (int)$pdo->lastInsertId();

    // 3) Insert order_items + update stock
    $ins = $pdo->prepare(
      "INSERT INTO order_items (order_id, book_id, qty, unit_price, subtotal)
       VALUES (?, ?, ?, ?, ?)"
    );
    $upd = $pdo->prepare("UPDATE books SET stock = stock - ? WHERE id = ?");

    foreach ($items as $it) {
      $ins->execute([
        $orderId,
        $it['book_id'],
        $it['qty'],
        $it['unit_price'],
        $it['subtotal']
      ]);
      $upd->execute([$it['qty'], $it['book_id']]);
    }

    $pdo->commit();
    return ['ok'=>true, 'order_id'=>$orderId];

  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    return ['ok'=>false, 'message'=>'Checkout failed: ' . $e->getMessage()];
  }
}

function db_orders_for_user(string $username): array {
  global $pdo;
  $user = db_user_find_by_username($username);
  if (!$user) return [];

  $stmt = $pdo->prepare("
    SELECT id, total, created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
  ");
  $stmt->execute([(int)$user['id']]);
  return $stmt->fetchAll();
}

function db_order_items(int $orderId): array {
  global $pdo;

  $stmt = $pdo->prepare("
    SELECT
      oi.book_id, oi.qty, oi.unit_price, oi.subtotal,
      b.title, b.isbn
    FROM order_items oi
    JOIN books b ON b.id = oi.book_id
    WHERE oi.order_id = ?
    ORDER BY oi.id ASC
  ");
  $stmt->execute([$orderId]);
  return $stmt->fetchAll();
}
/* =========================
   ADMIN - BOOKS (DB)
   ========================= */

function db_book_create(
  string $isbn,
  string $title,
  string $authors,
  string $category,
  int $publishedYear,
  int $price,
  int $stock,
  int $publisherId
): array {
  global $pdo;

  // Allowed categories
  $ALLOWED_CATEGORIES = ['Science', 'History', 'Geography', 'Art', 'Novel'];

  $isbn = trim($isbn);
  $title = trim($title);
  $authors = trim($authors);
  $category = trim($category);

  if ($isbn === '' || $title === '' || $authors === '' || $category === '') {
    return ['ok'=>false, 'message'=>'All fields are required.'];
  }

  if (!in_array($category, $ALLOWED_CATEGORIES, true)) {
    return ['ok'=>false, 'message'=>'Invalid category.'];
  }

  $isbnDigits = preg_replace('/\D+/', '', $isbn);
  if ($isbnDigits !== $isbn || (strlen($isbn) !== 10 && strlen($isbn) !== 13)) {
    return ['ok'=>false, 'message'=>'ISBN must be 10 or 13 digits.'];
  }

  $currentYear = (int)date('Y');
  if ($publishedYear < 1900 || $publishedYear > $currentYear) {
    return ['ok'=>false, 'message'=>'Invalid published year.'];
  }

  if ($price <= 0) return ['ok'=>false, 'message'=>'Price must be greater than 0.'];
  if ($stock < 0) return ['ok'=>false, 'message'=>'Stock cannot be negative.'];
  if ($publisherId <= 0) return ['ok'=>false, 'message'=>'Publisher is required.'];

  $chkPub = $pdo->prepare("SELECT id FROM publishers WHERE id = ? LIMIT 1");
  $chkPub->execute([$publisherId]);
  if (!$chkPub->fetch()) {
    return ['ok'=>false, 'message'=>'Publisher not found.'];
  }

  $chk = $pdo->prepare("SELECT id FROM books WHERE isbn = ? LIMIT 1");
  $chk->execute([$isbn]);
  if ($chk->fetch()) {
    return ['ok'=>false, 'message'=>'ISBN already exists.'];
  }

  $stmt = $pdo->prepare("
    INSERT INTO books
      (isbn, title, authors, category, published_year, price, stock, publisher_id)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
  ");
  $stmt->execute([
    $isbn,
    $title,
    $authors,
    $category,
    $publishedYear,
    $price,
    $stock,
    $publisherId
  ]);

  return ['ok'=>true];
}

function db_book_update(int $id, int $price, int $stock): array {
  global $pdo;

  if ($id <= 0) return ['ok'=>false, 'message'=>'Invalid book id.'];
  if ($price < 1) return ['ok'=>false, 'message'=>'Price must be >= 1.'];
  if ($stock < 0) return ['ok'=>false, 'message'=>'Stock cannot be negative.'];

  try {
    $stmt = $pdo->prepare("UPDATE books SET price = ?, stock = ? WHERE id = ? LIMIT 1");
    $stmt->execute([$price, $stock, $id]);
    return ['ok'=>true];
  } catch (PDOException $e) {
    return ['ok'=>false, 'message'=>$e->getMessage()];
  }
}
/* =========================
   ADMIN - PUBLISHERS (DB)
   ========================= */

function db_publisher_create(string $name, string $phone, string $address): array {
  global $pdo;

  $name = trim($name);
  $phone = trim($phone);
  $address = trim($address);

  if ($name === '') return ['ok'=>false, 'message'=>'Publisher name is required.'];

  $chk = $pdo->prepare("SELECT id FROM publishers WHERE name = ? LIMIT 1");
  $chk->execute([$name]);
  if ($chk->fetch()) return ['ok'=>false, 'message'=>'Publisher already exists.'];

  $stmt = $pdo->prepare("INSERT INTO publishers (name, phone, address) VALUES (?, ?, ?)");
  $stmt->execute([$name, $phone, $address]);

  return ['ok'=>true];
}

function db_publisher_update(int $id, string $name, string $phone, string $address): array {
  global $pdo;

  $name = trim($name);
  $phone = trim($phone);
  $address = trim($address);

  if ($id <= 0) return ['ok'=>false, 'message'=>'Invalid publisher id.'];
  if ($name === '') return ['ok'=>false, 'message'=>'Publisher name is required.'];

  $chk = $pdo->prepare("SELECT id FROM publishers WHERE name = ? AND id <> ? LIMIT 1");
  $chk->execute([$name, $id]);
  if ($chk->fetch()) return ['ok'=>false, 'message'=>'Publisher name already used.'];

  $stmt = $pdo->prepare("UPDATE publishers SET name=?, phone=?, address=? WHERE id=? LIMIT 1");
  $stmt->execute([$name, $phone, $address, $id]);

  return ['ok'=>true];
}

/* =========================
   ADMIN - REPLENISHMENTS (DB)
   ========================= */

function db_replenishments_list(string $status = 'Pending'): array {
  global $pdo;

  $stmt = $pdo->prepare("
    SELECT
      r.id, r.book_id, r.qty, r.status, r.created_at, r.confirmed_at,
      b.title, b.isbn, b.stock
    FROM replenishments r
    JOIN books b ON b.id = r.book_id
    WHERE r.status = ?
    ORDER BY r.created_at DESC
  ");
  $stmt->execute([$status]);
  return $stmt->fetchAll();
}

function db_replenishment_create(int $bookId, int $qty): array {
  global $pdo;

  if ($bookId <= 0) return ['ok'=>false, 'message'=>'Invalid book.'];
  if ($qty <= 0) return ['ok'=>false, 'message'=>'Quantity must be > 0.'];

  $chk = $pdo->prepare("SELECT id FROM books WHERE id = ? LIMIT 1");
  $chk->execute([$bookId]);
  if (!$chk->fetch()) return ['ok'=>false, 'message'=>'Book not found.'];

  $stmt = $pdo->prepare("INSERT INTO replenishments (book_id, qty, status) VALUES (?, ?, 'Pending')");
  $stmt->execute([$bookId, $qty]);

  return ['ok'=>true];
}

function db_replenishment_confirm(int $replId): array {
  global $pdo;

  if ($replId <= 0) return ['ok'=>false, 'message'=>'Invalid replenishment id.'];

  try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id, book_id, qty, status FROM replenishments WHERE id = ? FOR UPDATE");
    $stmt->execute([$replId]);
    $r = $stmt->fetch();

    if (!$r) {
      $pdo->rollBack();
      return ['ok'=>false, 'message'=>'Replenishment not found.'];
    }

    if ($r['status'] !== 'Pending') {
      $pdo->rollBack();
      return ['ok'=>false, 'message'=>'Replenishment already confirmed.'];
    }

    $bookId = (int)$r['book_id'];
    $qty = (int)$r['qty'];

    $upd = $pdo->prepare("UPDATE books SET stock = stock + ? WHERE id = ? LIMIT 1");
    $upd->execute([$qty, $bookId]);

    $upd2 = $pdo->prepare("UPDATE replenishments SET status='Confirmed', confirmed_at=NOW() WHERE id = ? LIMIT 1");
    $upd2->execute([$replId]);

    $pdo->commit();
    return ['ok'=>true];

  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    return ['ok'=>false, 'message'=>'Confirm failed: ' . $e->getMessage()];
  }
}
/* =========================
   REPORTS (DB)
   ========================= */

function db_report_totals(): array {
  global $pdo;

  $sql = "
    SELECT
      (SELECT COUNT(*) FROM orders) AS total_orders,
      (SELECT COALESCE(SUM(total),0) FROM orders) AS total_revenue,
      (SELECT COALESCE(SUM(qty),0) FROM order_items) AS total_items_sold
  ";
  return $pdo->query($sql)->fetch();
}

function db_report_top_books(int $limit = 5): array {
  global $pdo;

  $limit = max(1, min($limit, 50));

  $stmt = $pdo->prepare("
    SELECT
      b.id,
      b.title,
      b.isbn,
      COALESCE(SUM(oi.qty),0) AS sold_qty,
      COALESCE(SUM(oi.subtotal),0) AS sold_value
    FROM books b
    LEFT JOIN order_items oi ON oi.book_id = b.id
    GROUP BY b.id, b.title, b.isbn
    ORDER BY sold_qty DESC
    LIMIT $limit
  ");
  $stmt->execute();
  return $stmt->fetchAll();
}

function db_report_low_stock(int $threshold = 2): array {
  global $pdo;

  $threshold = max(0, $threshold);

  $stmt = $pdo->prepare("
    SELECT
      b.id, b.title, b.isbn, b.stock, b.price,
      p.name AS publisher
    FROM books b
    JOIN publishers p ON p.id = b.publisher_id
    WHERE b.stock <= ?
    ORDER BY b.stock ASC, b.id ASC
  ");
  $stmt->execute([$threshold]);
  return $stmt->fetchAll();
}

function db_report_sales_previous_month(): array {
  global $pdo;

  $sql = "
    SELECT COALESCE(SUM(total), 0) AS total_sales
    FROM orders
    WHERE created_at >= DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y-%m-01')
      AND created_at <  DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')
  ";
  return $pdo->query($sql)->fetch();
}

function db_report_sales_on_date(string $date): array {
  global $pdo;

  $stmt = $pdo->prepare("
    SELECT COALESCE(SUM(total), 0) AS total_sales
    FROM orders
    WHERE DATE(created_at) = ?
  ");
  $stmt->execute([$date]);
  return $stmt->fetch();
}

function db_report_top_customers_last_3_months(int $limit = 5): array {
  global $pdo;

  $stmt = $pdo->prepare("
    SELECT
      u.id,
      u.username,
      u.full_name,
      COALESCE(SUM(o.total), 0) AS total_spent,
      COUNT(o.id) AS orders_count
    FROM orders o
    JOIN users u ON u.id = o.user_id
    WHERE o.created_at >= (NOW() - INTERVAL 3 MONTH)
    GROUP BY u.id, u.username, u.full_name
    ORDER BY total_spent DESC
    LIMIT $limit
  ");
  $stmt->execute();
  return $stmt->fetchAll();
}

function db_report_top_selling_books_last_3_months(int $limit = 10): array {
  global $pdo;

  $stmt = $pdo->prepare("
    SELECT
      b.id,
      b.title,
      b.isbn,
      COALESCE(SUM(oi.qty), 0) AS sold_qty,
      COALESCE(SUM(oi.subtotal), 0) AS sold_value
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN books b ON b.id = oi.book_id
    WHERE o.created_at >= (NOW() - INTERVAL 3 MONTH)
    GROUP BY b.id, b.title, b.isbn
    ORDER BY sold_qty DESC
    LIMIT $limit
  ");
  $stmt->execute();
  return $stmt->fetchAll();
}

function db_report_replenishment_count_for_book(int $bookId): array {
  global $pdo;

  $stmt = $pdo->prepare("
    SELECT
      COUNT(*) AS times_ordered,
      COALESCE(SUM(qty), 0) AS total_qty,
      SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) AS pending_count,
      SUM(CASE WHEN status='Confirmed' THEN 1 ELSE 0 END) AS confirmed_count
    FROM replenishments
    WHERE book_id = ?
  ");
  $stmt->execute([$bookId]);
  return $stmt->fetch();
}



function db_report_recent_orders(int $limit = 10): array {
  global $pdo;

  $limit = max(1, min($limit, 100));

  $stmt = $pdo->prepare("
    SELECT
      o.id, o.total, o.created_at,
      u.username, u.full_name
    FROM orders o
    JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC
    LIMIT $limit
  ");
  $stmt->execute();
  return $stmt->fetchAll();
}

/* =========================
   USERS - CREATE
   ========================= */

function db_user_create(
  string $username,
  string $password,
  string $fullName = '',
  string $email = '',
  string $role = 'customer'
): array {
  global $pdo;

  $username = trim($username);
  $fullName = trim($fullName);
  $email    = trim($email);

  if ($username === '' || $password === '') {
    return ['ok'=>false, 'message'=>'Username and password are required.'];
  }

  $chk = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
  $chk->execute([$username]);
  if ($chk->fetch()) {
    return ['ok'=>false, 'message'=>'Username already exists.'];
  }

  $hash = password_hash($password, PASSWORD_DEFAULT);

  $stmt = $pdo->prepare(
    "INSERT INTO users (username, password_hash, full_name, email, role)
     VALUES (?, ?, ?, ?, ?)"
  );
  $stmt->execute([$username, $hash, $fullName, $email, $role]);

  return ['ok'=>true];
}


