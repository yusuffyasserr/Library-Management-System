<?php
session_start();

require_once __DIR__ . '/../../Backend/data/repo_db.php';
require_once __DIR__ . '/../includes/header.php';

// Require login
$user = $_SESSION['user'] ?? null;
if (!$user) {
  header("Location: login.php?redirect=orders.php");
  exit;
}

$username = is_array($user) ? ($user['username'] ?? '') : (string)$user;

$orders = db_orders_for_user($username);
$placedId = (int)($_GET['placed'] ?? 0);
?>

<div class="container">
  <div class="hero">
    <h1>My Orders</h1>
    <p>Your orders are now saved in the database.</p>
  </div>

  <?php if ($placedId > 0): ?>
    <div class="card" style="margin-top:18px; border-color: rgba(64,243,154,.35); background: rgba(64,243,154,.08);">
      <b>Order placed successfully. Order #<?= (int)$placedId ?></b>
    </div>
  <?php endif; ?>

  <div class="card" style="margin-top:18px;">
    <?php if (count($orders) === 0): ?>
      <p style="color:var(--muted); margin:0;">No orders yet.</p>
      <div style="margin-top:12px;">
        <a class="btn secondary" href="index.php">Browse</a>
      </div>
    <?php else: ?>
      <?php foreach ($orders as $o): ?>
        <?php $items = db_order_items((int)$o['id']); ?>
        <div class="card" style="margin-bottom:14px;">
          <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
              <div style="font-weight:900; font-size:18px;">Order #<?= (int)$o['id'] ?></div>
              <div class="small">Date: <?= htmlspecialchars($o['created_at']) ?></div>
            </div>
            <div class="price"><?= (int)$o['total'] ?> EGP</div>
          </div>

          <div style="overflow:auto; margin-top:10px;">
            <table class="table">
              <thead>
                <tr><th>Book</th><th>Qty</th><th>Unit</th><th>Subtotal</th></tr>
              </thead>
              <tbody>
                <?php foreach ($items as $it): ?>
                  <tr>
                    <td><?= htmlspecialchars($it['title']) ?></td>
                    <td><?= (int)$it['qty'] ?></td>
                    <td><?= (int)$it['unit_price'] ?> EGP</td>
                    <td><b><?= (int)$it['subtotal'] ?> EGP</b></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
