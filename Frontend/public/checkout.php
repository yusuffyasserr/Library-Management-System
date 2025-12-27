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

// ---- FIX (Step C): map session user -> DB user_id ----
$username = is_array($user) ? ($user['username'] ?? '') : (string)$user;
$username = trim($username);

$dbUser = db_user_find_by_username($username);
if (!$dbUser) {
  // session has user but DB doesn't (not a real account)
  header("Location: login.php?redirect=checkout.php");
  exit;
}

$userId = (int)$dbUser['id'];
// ------------------------------------------------------

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

// ------------------------------
// Payment Validation (FORMAT ONLY)
// - Card number: digits only, length 13-19 (spaces/hyphens allowed in input)
// - Expiry: MM/YY or MM/YYYY, month 01-12, not expired
// ------------------------------
function normalize_card_number(string $s): string {
  return preg_replace('/[\s-]+/', '', trim($s));
}

function validate_card_number(string $number): bool {
  return ctype_digit($number) && strlen($number) >= 13 && strlen($number) <= 19;
}

function validate_expiry(string $exp): bool {
  $exp = trim($exp);

  // Accept MM/YY or MM/YYYY
  if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2}|\d{4})$/', $exp, $m)) return false;

  $month = (int)$m[1];
  $year  = (int)$m[2];

  // YY -> YYYY
  if ($year < 100) $year += 2000;

  $expiry = DateTime::createFromFormat('Y-n', "$year-$month");
  if (!$expiry) return false;

  // valid through end of month
  $expiry->modify('last day of this month');
  $now = new DateTime('today');

  return $expiry >= $now;
}
// ------------------------------

// Place order (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (count($items) === 0) {
    $flash = ['ok'=>false, 'message'=>'Your cart is empty or items are unavailable.'];
  } else {

    // ---- NEW: validate payment fields ----
    $cardNumberRaw = $_POST['card_number'] ?? '';
    $expiryRaw     = $_POST['expiry'] ?? '';

    $cardNumber = normalize_card_number($cardNumberRaw);

    if ($cardNumber === '') {
      $flash = ['ok'=>false, 'message'=>'Credit card number is required.'];
    } elseif (!validate_card_number($cardNumber)) {
      $flash = ['ok'=>false, 'message'=>'Invalid credit card number format.'];
    } elseif (!validate_expiry($expiryRaw)) {
      $flash = ['ok'=>false, 'message'=>'Invalid expiry date.'];
    } else {
      // ---- FIX: use userId (DB) not username ----
      $res = db_order_create($userId, $cart);

      if ($res['ok']) {
        $_SESSION['cart'] = []; // clear cart
        header("Location: orders.php?placed=" . (int)$res['order_id']);
        exit;
      } else {
        $flash = ['ok'=>false, 'message'=>$res['message'] ?? 'Checkout failed.'];
      }
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

      <div style="margin-top:18px; padding-top:14px; border-top:1px solid rgba(255,255,255,.08);">
        <div class="small" style="margin-bottom:10px;">Payment (Simulated)</div>

        <form method="post" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
          <div style="min-width:260px;">
            <div class="small">Credit Card Number</div>
            <input
              class="input"
              name="card_number"
              placeholder="1234 5678 9012 3456"
              required
              value="<?= htmlspecialchars($_POST['card_number'] ?? '') ?>"
            >
          </div>

          <div style="min-width:160px;">
            <div class="small">Expiry Date</div>
            <input
              class="input"
              name="expiry"
              placeholder="MM/YY"
              required
              value="<?= htmlspecialchars($_POST['expiry'] ?? '') ?>"
            >
          </div>

          <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button class="btn" type="submit">Place Order</button>
            <a class="btn secondary" href="cart.php">Back to Cart</a>
          </div>
        </form>
      </div>

      <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-top:14px;">
        <div>
          <div class="small">Total</div>
          <div class="price" style="font-size:26px;"><?= (int)$total ?> EGP</div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
