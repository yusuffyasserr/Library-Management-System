<?php
session_start();

require_once __DIR__ . '/../../Backend/data/repo_db.php';
require_once __DIR__ . '/../includes/header.php';

// Require login
$user = $_SESSION['user'] ?? null;
if (!$user) {
  header("Location: login.php?redirect=checkout.php");
  exit;
}

$cart = $_SESSION['cart'] ?? [];

$items = [];
$total = 0;

// Build checkout items from DB
foreach ($cart as $bookId => $qty) {
  $bookId = (int)$bookId;
  $qty = (int)$qty;

  $book = db_book_find($bookId);
  if (!$book) continue;

  $stock = (int)$book['stock'];
  if ($stock <= 0) continue;

  if ($qty < 1) $qty = 1;
  if ($qty > $stock) $qty = $stock;

  $sub = (int)$book['price'] * $qty;
  $total += $sub;

  $items[] = [
    'id' => $bookId,
    'title' => $book['title'],
    'price' => (int)$book['price'],
    'qty' => $qty,
    'subtotal' => $sub
  ];
}

$flash = null;

// Place order (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (count($items) === 0) {
    $flash = ['ok'=>false, 'message'=>'Your cart is empty or items are unavailable.'];
  } else {
    $username = is_array($user) ? ($user['username'] ?? '') : (string)$user;

    $res = db_order_create($username, $cart);
    if ($res['ok']) {
      $_SESSION['cart'] = []; // clear cart
      header("Location: orders.php?placed=" . (int)$res['order_id']);
      exit;
    } else {
      $flash = ['ok'=>false, 'message'=>$res['message'] ?? 'Checkout failed.'];
    }
  }
}
?>

<div class="container">
  <div class="hero">
    <h1>Checkout</h1>
    <p>Confirm your order. Payment is simulated.</p>
  </div>

  <?php if ($flash): ?>
    <div class="card" style="margin-top:18px; border-color: rgba(255,92,122,.35); background: rgba(255,92,122,.08);">
      <b><?= htmlspecialchars($flash['message']) ?></b>
    </div>
  <?php endif; ?>

  <div class="card" style="margin-top:18px;">
    <?php if (count($items) === 0): ?>
      <p style="color:var(--muted); margin:0;">Your cart is empty.</p>
      <div style="margin-top:12px;">
        <a class="btn secondary" href="index.php">Browse</a>
      </div>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr><th>Book</th><th>Qty</th><th>Unit</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td style="font-weight:900;"><?= htmlspecialchars($it['title']) ?></td>
              <td><?= (int)$it['qty'] ?></td>
              <td><?= (int)$it['price'] ?> EGP</td>
              <td><b><?= (int)$it['subtotal'] ?> EGP</b></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-top:14px;">
        <div>
          <div class="small">Total</div>
          <div class="price" style="font-size:26px;"><?= (int)$total ?> EGP</div>
        </div>

        <form method="post" style="display:flex; gap:10px; flex-wrap:wrap;">
          <button class="btn" type="submit">Place Order</button>
          <a class="btn secondary" href="cart.php">Back to Cart</a>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
