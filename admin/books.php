<?php
require_once __DIR__ . '/../Backend/auth/admin_auth.php';
require_once __DIR__ . '/../Backend/data/repo_db.php';

require_once __DIR__ . '/../Frontend/includes/header.php';

$books = db_books_list();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$added = isset($_GET['added']);
?>

<div class="container">
  <div class="hero">
    <h1>Manage Books</h1>
    <p>Update book price and stock (saved in MySQL).</p>

    <!-- ✅ Add Book Button -->
    <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
      <a class="btn" href="book_create.php">+ Add Book</a>
      <a class="btn secondary" href="dashboard.php">Back to Dashboard</a>
    </div>
  </div>

  <?php if ($added): ?>
    <div class="card" style="margin-top:18px; border-color: rgba(64,243,154,.35); background: rgba(64,243,154,.08);">
      <b>Book added successfully.</b>
    </div>
  <?php endif; ?>

  <?php if ($flash): ?>
    <div class="card" style="margin-top:18px; border-color: rgba(64,243,154,.35); background: rgba(64,243,154,.08);">
      <b><?= htmlspecialchars($flash) ?></b>
    </div>
  <?php endif; ?>

  <div class="card" style="margin-top:18px; overflow:auto;">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>ISBN</th>
          <th>Publisher</th>
          <th>Price</th>
          <th style="width:140px;">Stock</th>
          <th style="width:220px;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($books as $b): ?>
          <?php
            $stock = (int)$b['stock'];
            $badgeClass = ($stock <= 0) ? 'out' : (($stock <= 2) ? 'warn' : 'ok');
            $badgeText  = ($stock <= 0) ? 'Out' : (($stock <= 2) ? 'Low' : 'OK');
          ?>
          <tr>
            <td><?= (int)$b['id'] ?></td>
            <td style="font-weight:900;"><?= htmlspecialchars($b['title']) ?></td>
            <td><?= htmlspecialchars($b['isbn']) ?></td>
            <td><?= htmlspecialchars($b['publisher']) ?></td>

            <td>
              <form method="post" action="book_action.php" style="display:flex; gap:8px; align-items:center;">
                <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                <input class="input" type="number" min="0" name="price" value="<?= (int)$b['price'] ?>" style="width:110px;">
            </td>

            <td>
                <input class="input" type="number" min="0" name="stock" value="<?= (int)$b['stock'] ?>" style="width:110px;">
                <span class="badge <?= $badgeClass ?>" style="margin-left:8px;"><?= htmlspecialchars($badgeText) ?></span>
            </td>

            <td>
                <button class="btn" type="submit">Save</button>
                <a class="btn secondary" href="/LIBRARY%20MANAGEMENT%20SYSTEM/Frontend/public/book.php?id=<?= (int)$b['id'] ?>">View</a>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../Frontend/includes/footer.php'; ?>
