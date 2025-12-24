<?php
require_once __DIR__ . '/../Backend/auth/admin_auth.php';
require_once __DIR__ . '/../Backend/data/repo_db.php';

require_once __DIR__ . '/../Frontend/includes/header.php';

$pending = db_replenishments_list('Pending');
$confirmed = db_replenishments_list('Confirmed');

$books = db_books_list();

$flash = $_SESSION['flash'] ?? null;
$flash_err = $_SESSION['flash_err'] ?? null;
unset($_SESSION['flash'], $_SESSION['flash_err']);
?>

<div class="container">
  <div class="hero">
    <h1>Stock Replenishment</h1>
    <p>Create replenishment requests and confirm them to add stock.</p>
  </div>

  <?php if ($flash): ?>
    <div class="card" style="margin-top:18px; border-color: rgba(64,243,154,.35); background: rgba(64,243,154,.08);">
      <b><?= htmlspecialchars($flash) ?></b>
    </div>
  <?php endif; ?>

  <?php if ($flash_err): ?>
    <div class="card" style="margin-top:18px; border-color: rgba(255,92,122,.35); background: rgba(255,92,122,.08);">
      <b><?= htmlspecialchars($flash_err) ?></b>
    </div>
  <?php endif; ?>

  <div class="grid" style="margin-top:18px;">
    <!-- LEFT: Create -->
    <div class="col-4">
      <div class="card">
        <h3 style="margin-top:0;">Create Replenishment</h3>

        <form class="form" method="post" action="repl_action.php">
          <input type="hidden" name="mode" value="create">

          <div>
            <div class="small">Book</div>
            <select class="input" name="book_id" required>
              <?php foreach ($books as $b): ?>
                <option value="<?= (int)$b['id'] ?>">
                  #<?= (int)$b['id'] ?> — <?= htmlspecialchars($b['title']) ?> (Stock: <?= (int)$b['stock'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <div class="small">Quantity</div>
            <input class="input" type="number" min="1" name="qty" value="10" required>
          </div>

          <button class="btn" type="submit" style="margin-top:6px;">Create</button>
        </form>
      </div>

      <div style="margin-top:12px;">
        <a class="btn secondary" href="dashboard.php">Back to Dashboard</a>
      </div>
    </div>

    <!-- RIGHT: Pending + Confirmed -->
    <div class="col-8">
      <div class="card" style="overflow:auto;">
        <h3 style="margin-top:0;">Pending Replenishments</h3>

        <?php if (count($pending) === 0): ?>
          <p style="color:var(--muted); margin:0;">No pending replenishments.</p>
        <?php else: ?>
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Book</th>
                <th>Qty</th>
                <th>Current Stock</th>
                <th>Created</th>
                <th style="width:160px;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pending as $r): ?>
                <tr>
                  <td>#<?= (int)$r['id'] ?></td>
                  <td style="font-weight:900;"><?= htmlspecialchars($r['title']) ?></td>
                  <td><?= (int)$r['qty'] ?></td>
                  <td><?= (int)$r['stock'] ?></td>
                  <td><?= htmlspecialchars($r['created_at']) ?></td>
                  <td>
                    <form method="post" action="repl_action.php">
                      <input type="hidden" name="mode" value="confirm">
                      <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                      <button class="btn" type="submit">Confirm</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <div class="card" style="overflow:auto; margin-top:14px;">
        <h3 style="margin-top:0;">Confirmed Replenishments</h3>

        <?php if (count($confirmed) === 0): ?>
          <p style="color:var(--muted); margin:0;">No confirmed replenishments yet.</p>
        <?php else: ?>
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Book</th>
                <th>Qty</th>
                <th>Created</th>
                <th>Confirmed</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($confirmed as $r): ?>
                <tr>
                  <td>#<?= (int)$r['id'] ?></td>
                  <td style="font-weight:900;"><?= htmlspecialchars($r['title']) ?></td>
                  <td><?= (int)$r['qty'] ?></td>
                  <td><?= htmlspecialchars($r['created_at']) ?></td>
                  <td><?= htmlspecialchars($r['confirmed_at'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../Frontend/includes/footer.php'; ?>
