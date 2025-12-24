<?php
session_start();

require_once __DIR__ . '/../../Backend/data/repo_db.php';
require_once __DIR__ . '/../includes/header.php';

$cart = $_SESSION['cart'] ?? [];
$items = [];
$total = 0;

foreach ($cart as $bookId => $qty) {
  $bookId = (int)$bookId;
  $qty = (int)$qty;

  $book = db_book_find($bookId);
  if (!$book) continue;

  $stock = (int)$book['stock'];
  if ($stock <= 0) continue;

  if ($qty > $stock) $qty = $stock;

  $sub = (int)$book['price'] * $qty;
  $total += $sub;

  $items[] = [
    'id' => $bookId,
    'title' => $book['title'],
    'price' => (int)$book['price'],
    'stock' => $stock,
    'qty' => $qty,
    'subtotal' => $sub
  ];
}
?>

<div class="container">
  <div class="hero">
    <h1>Your Cart</h1>
    <p>Update quantities, remove items, then proceed to checkout.</p>
  </div>

  <div class="card" style="margin-top:18px;">
    <?php if (count($items) === 0): ?>
      <p style="color:var(--muted); margin:0;">Your cart is empty.</p>
      <div style="margin-top:12px;">
        <a class="btn secondary" href="index.php">Browse</a>
      </div>
    <?php else: ?>
      <form method="post" action="cart_action.php?action=update">
        <table class="table">
          <thead>
            <tr>
              <th>Book</th>
              <th>Price</th>
              <th style="width:150px;">Qty</th>
              <th>Subtotal</th>
              <th style="width:120px;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $it): ?>
              <tr>
                <td style="font-weight:900;"><?= htmlspecialchars($it['title']) ?></td>
                <td><?= (int)$it['price'] ?> EGP</td>
                <td>
                  <input class="input" type="number" min="1" max="<?= (int)$it['stock'] ?>"
                         name="qty[<?= (int)$it['id'] ?>]" value="<?= (int)$it['qty'] ?>">
                  <div class="small">Stock: <?= (int)$it['stock'] ?></div>
                </td>
                <td><b><?= (int)$it['subtotal'] ?> EGP</b></td>
                <td>
                  <a class="btn secondary" href="cart_action.php?action=remove&id=<?= (int)$it['id'] ?>">Remove</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-top:14px;">
          <div>
            <div class="small">Total</div>
            <div class="price" style="font-size:26px;"><?= (int)$total ?> EGP</div>
          </div>

          <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button class="btn" type="submit">Update Cart</button>
            <a class="btn secondary" href="cart_action.php?action=clear">Clear Cart</a>
            <a class="btn" href="checkout.php">Proceed to Checkout</a>
          </div>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
