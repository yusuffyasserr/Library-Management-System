<?php
require_once __DIR__ . '/../Backend/auth/admin_auth.php';
require_once __DIR__ . '/../Backend/data/repo_db.php';

require_once __DIR__ . '/../Frontend/includes/header.php';

$totals = db_report_totals();
$topBooks = db_report_top_books(5);
$lowStock = db_report_low_stock(2);
$recentOrders = db_report_recent_orders(10);
?>

<div class="container">
  <div class="hero">
    <h1>Reports</h1>
    <p>Real SQL reports from MySQL orders, items, and stock.</p>
  </div>

  <!-- Totals -->
  <div class="grid" style="margin-top:18px;">
    <div class="col-4">
      <div class="card">
        <div class="small">Total Orders</div>
        <div class="price" style="font-size:28px;"><?= (int)($totals['total_orders'] ?? 0) ?></div>
      </div>
    </div>
    <div class="col-4">
      <div class="card">
        <div class="small">Total Revenue</div>
        <div class="price" style="font-size:28px;"><?= (int)($totals['total_revenue'] ?? 0) ?> EGP</div>
      </div>
    </div>
    <div class="col-4">
      <div class="card">
        <div class="small">Total Items Sold</div>
        <div class="price" style="font-size:28px;"><?= (int)($totals['total_items_sold'] ?? 0) ?></div>
      </div>
    </div>
  </div>

  <!-- Top Selling Books -->
  <div class="card" style="margin-top:18px; overflow:auto;">
    <h3 style="margin-top:0;">Top Selling Books</h3>
    <table class="table">
      <thead>
        <tr>
          <th>Book</th>
          <th>ISBN</th>
          <th>Sold Qty</th>
          <th>Sold Value</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($topBooks as $b): ?>
          <tr>
            <td style="font-weight:900;"><?= htmlspecialchars($b['title']) ?></td>
            <td><?= htmlspecialchars($b['isbn']) ?></td>
            <td><?= (int)$b['sold_qty'] ?></td>
            <td><b><?= (int)$b['sold_value'] ?> EGP</b></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Low Stock -->
  <div class="card" style="margin-top:18px; overflow:auto;">
    <h3 style="margin-top:0;">Low Stock (≤ 2)</h3>
    <?php if (count($lowStock) === 0): ?>
      <p style="color:var(--muted); margin:0;">No low stock books right now.</p>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr>
            <th>Book</th>
            <th>Publisher</th>
            <th>Stock</th>
            <th>Price</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lowStock as $b): ?>
            <tr>
              <td style="font-weight:900;"><?= htmlspecialchars($b['title']) ?></td>
              <td><?= htmlspecialchars($b['publisher']) ?></td>
              <td>
                <?php
                  $stock = (int)$b['stock'];
                  $badgeClass = ($stock <= 0) ? 'out' : 'warn';
                  $badgeText  = ($stock <= 0) ? 'Out' : 'Low';
                ?>
                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
                <b style="margin-left:8px;"><?= $stock ?></b>
              </td>
              <td><?= (int)$b['price'] ?> EGP</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <!-- Recent Orders -->
  <div class="card" style="margin-top:18px; overflow:auto;">
    <h3 style="margin-top:0;">Recent Orders</h3>
    <?php if (count($recentOrders) === 0): ?>
      <p style="color:var(--muted); margin:0;">No orders yet.</p>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr>
            <th>Order #</th>
            <th>User</th>
            <th>Total</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentOrders as $o): ?>
            <tr>
              <td style="font-weight:900;">#<?= (int)$o['id'] ?></td>
              <td><?= htmlspecialchars($o['username']) ?> (<?= htmlspecialchars($o['full_name']) ?>)</td>
              <td><b><?= (int)$o['total'] ?> EGP</b></td>
              <td><?= htmlspecialchars($o['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div style="margin-top:12px;">
    <a class="btn secondary" href="dashboard.php">Back to Dashboard</a>
  </div>
</div>

<?php require_once __DIR__ . '/../Frontend/includes/footer.php'; ?>
