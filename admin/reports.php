<?php
require_once __DIR__ . '/../Backend/auth/admin_auth.php';
require_once __DIR__ . '/../Backend/data/repo_db.php';

require_once __DIR__ . '/../Frontend/includes/header.php';

// Inputs
$salesDate = $_GET['sales_date'] ?? date('Y-m-d');
$bookId = (int)($_GET['book_id'] ?? 0);

// Required Reports
$prevMonthSales = db_report_sales_previous_month();
$salesOnDate = db_report_sales_on_date($salesDate);
$topCustomers = db_report_top_customers_last_3_months(5);
$topBooks3m = db_report_top_selling_books_last_3_months(10);

// For dropdowns
$books = db_books_list();
$replStats = null;
if ($bookId > 0) {
  $replStats = db_report_replenishment_count_for_book($bookId);
}

// Extra (already in your project)
$totals = db_report_totals();
$lowStock = db_report_low_stock(2);
$recentOrders = db_report_recent_orders(10);
?>

<div class="container">
  <div class="hero">
    <h1>Reports</h1>
    <p>Admin-only reports from MySQL (Orders, Order Items, Replenishments).</p>
  </div>

  <!-- =========================
       REQUIRED REPORTS (a-e)
       ========================= -->

  <div class="card" style="margin-top:18px;">
    <h2 style="margin:0 0 8px 0;"> Total Sales - Previous Month</h2>
    <div style="display:flex; gap:14px; flex-wrap:wrap; align-items:center;">
      <div class="price" style="font-size:28px;">
        <?= (int)($prevMonthSales['total_sales'] ?? 0) ?> EGP
      </div>
      <div class="small" style="color:var(--muted);">
        Sum of all orders in the month before the current month.
      </div>
    </div>
  </div>

  <div class="card" style="margin-top:18px;">
    <h2 style="margin:0 0 10px 0;"> Total Sales - On a Certain Day</h2>
    <form method="get" style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
      <div>
        <div class="small">Choose Date</div>
        <input class="input" type="date" name="sales_date" value="<?= htmlspecialchars($salesDate) ?>" required>
      </div>
      <input type="hidden" name="book_id" value="<?= (int)$bookId ?>">
      <button class="btn" type="submit">Generate</button>
    </form>

    <div style="margin-top:12px;">
      <div class="small">Total Sales on <?= htmlspecialchars($salesDate) ?></div>
      <div class="price" style="font-size:26px;"><?= (int)($salesOnDate['total_sales'] ?? 0) ?> EGP</div>
    </div>
  </div>

  <div class="card" style="margin-top:18px; overflow:auto;">
    <h2 style="margin:0 0 10px 0;">Top 5 Customers - Last 3 Months</h2>
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>User</th>
          <th>Full Name</th>
          <th>Orders</th>
          <th>Total Spent</th>
        </tr>
      </thead>
      <tbody>
        <?php $i=1; foreach ($topCustomers as $c): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td style="font-weight:900;"><?= htmlspecialchars($c['username']) ?></td>
            <td><?= htmlspecialchars($c['full_name'] ?? '') ?></td>
            <td><?= (int)$c['orders_count'] ?></td>
            <td><b><?= (int)$c['total_spent'] ?> EGP</b></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$topCustomers): ?>
          <tr><td colspan="5" style="color:var(--muted);">No orders in the last 3 months.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card" style="margin-top:18px; overflow:auto;">
    <h2 style="margin:0 0 10px 0;">Top 10 Selling Books - Last 3 Months</h2>
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
        <?php foreach ($topBooks3m as $b): ?>
          <tr>
            <td style="font-weight:900;"><?= htmlspecialchars($b['title']) ?></td>
            <td><?= htmlspecialchars($b['isbn']) ?></td>
            <td><?= (int)$b['sold_qty'] ?></td>
            <td><b><?= (int)$b['sold_value'] ?> EGP</b></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$topBooks3m): ?>
          <tr><td colspan="4" style="color:var(--muted);">No sales in the last 3 months.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card" style="margin-top:18px;">
    <h2 style="margin:0 0 10px 0;"> Replenishment Orders Count for a Specific Book</h2>
    <form method="get" style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
      <div style="min-width:280px;">
        <div class="small">Select Book</div>
        <select class="input" name="book_id" required>
          <option value="">Choose...</option>
          <?php foreach ($books as $bk): ?>
            <option value="<?= (int)$bk['id'] ?>" <?= ((int)$bk['id'] === $bookId) ? 'selected' : '' ?>>
              <?= htmlspecialchars($bk['title']) ?> (ID: <?= (int)$bk['id'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <input type="hidden" name="sales_date" value="<?= htmlspecialchars($salesDate) ?>">
      <button class="btn" type="submit">Generate</button>
    </form>

    <?php if ($bookId > 0 && $replStats): ?>
      <div style="margin-top:14px; display:flex; gap:16px; flex-wrap:wrap;">
        <div class="card" style="padding:14px; min-width:220px;">
          <div class="small">Times Ordered (Replenishments)</div>
          <div class="price" style="font-size:26px;"><?= (int)$replStats['times_ordered'] ?></div>
        </div>
        <div class="card" style="padding:14px; min-width:220px;">
          <div class="small">Total Replenished Qty</div>
          <div class="price" style="font-size:26px;"><?= (int)$replStats['total_qty'] ?></div>
        </div>
        <div class="card" style="padding:14px; min-width:220px;">
          <div class="small">Pending / Confirmed</div>
          <div class="price" style="font-size:22px;">
            <?= (int)$replStats['pending_count'] ?> / <?= (int)$replStats['confirmed_count'] ?>
          </div>
        </div>
      </div>
    <?php elseif ($bookId > 0): ?>
      <div style="margin-top:12px; color:var(--muted);">No replenishments found for this book.</div>
    <?php endif; ?>
  </div>

  <!-- =========================
       EXTRA (not required but nice)
       ========================= -->

  <div class="card" style="margin-top:22px;">
    <h2 style="margin:0 0 10px 0;"> Overall Totals</h2>
    <div style="display:grid; grid-template-columns: repeat(3, minmax(200px, 1fr)); gap:14px;">
      <div class="card" style="padding:14px;">
        <div class="small">Total Orders</div>
        <div class="price" style="font-size:26px;"><?= (int)$totals['total_orders'] ?></div>
      </div>
      <div class="card" style="padding:14px;">
        <div class="small">Total Revenue</div>
        <div class="price" style="font-size:26px;"><?= (int)$totals['total_revenue'] ?> EGP</div>
      </div>
      <div class="card" style="padding:14px;">
        <div class="small">Total Items Sold</div>
        <div class="price" style="font-size:26px;"><?= (int)$totals['total_items_sold'] ?></div>
      </div>
    </div>
  </div>

  <div class="card" style="margin-top:18px; overflow:auto;">
    <h2 style="margin:0 0 10px 0;"> Low Stock (≤ 2)</h2>
    <table class="table">
      <thead>
        <tr><th>Book</th><th>Publisher</th><th>Stock</th><th>Price</th></tr>
      </thead>
      <tbody>
        <?php foreach ($lowStock as $ls): ?>
          <tr>
            <td style="font-weight:900;"><?= htmlspecialchars($ls['title']) ?></td>
            <td><?= htmlspecialchars($ls['publisher']) ?></td>
            <td><span class="badge warn">Low</span> <?= (int)$ls['stock'] ?></td>
            <td><?= (int)$ls['price'] ?> EGP</td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$lowStock): ?>
          <tr><td colspan="4" style="color:var(--muted);">No low stock books.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card" style="margin-top:18px; overflow:auto;">
    <h2 style="margin:0 0 10px 0;">Recent Orders</h2>
    <table class="table">
      <thead>
        <tr><th>Order #</th><th>User</th><th>Total</th><th>Date</th></tr>
      </thead>
      <tbody>
        <?php foreach ($recentOrders as $o): ?>
          <tr>
            <td style="font-weight:900;">#<?= (int)$o['id'] ?></td>
            <td><?= htmlspecialchars($o['username']) ?> (<?= htmlspecialchars($o['full_name'] ?? '') ?>)</td>
            <td><b><?= (int)$o['total'] ?> EGP</b></td>
            <td><?= htmlspecialchars($o['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recentOrders): ?>
          <tr><td colspan="4" style="color:var(--muted);">No orders found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div style="margin-top:14px;">
    <a class="btn secondary" href="dashboard.php">Back to Dashboard</a>
  </div>
</div>

<?php require_once __DIR__ . '/../Frontend/includes/footer.php'; ?>
