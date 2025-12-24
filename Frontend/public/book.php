<?php
session_start();

require_once __DIR__ . '/../../Backend/config/app.php';
require_once __DIR__ . '/../../Backend/data/repo_db.php';

require_once __DIR__ . '/../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$book = $id ? db_book_find($id) : null;

if (!$book) {
  echo '<div class="container"><div class="card" style="margin-top:18px;">Book not found.</div></div>';
  require_once __DIR__ . '/../includes/footer.php';
  exit;
}

$stock = (int)($book['stock'] ?? 0);
$badgeClass = ($stock <= 0) ? 'out' : (($stock <= 2) ? 'warn' : 'ok');
$badgeText  = ($stock <= 0) ? 'Out of stock' : (($stock <= 2) ? 'Low stock' : 'In stock');

// ✅ FIX: authors can be ARRAY or STRING
$authorsText = is_array($book['authors']) ? implode(', ', $book['authors']) : (string)$book['authors'];
?>

<div class="container">
  <div class="card" style="margin-top:18px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
      <div>
        <h1 style="margin-top:0;"><?= htmlspecialchars($book['title']) ?></h1>
        <div class="small"><b>ISBN:</b> <?= htmlspecialchars($book['isbn']) ?></div>
        <div class="small"><b>Authors:</b> <?= htmlspecialchars($authorsText) ?></div>
        <div class="small"><b>Publisher:</b> <?= htmlspecialchars($book['publisher']) ?></div>
        <div class="small"><b>Category:</b> <?= htmlspecialchars($book['category']) ?> • <b>Year:</b> <?= (int)($book['year'] ?? 0) ?></div>
      </div>

      <div style="min-width:220px;">
        <div class="price" style="font-size:28px;"><?= (int)$book['price'] ?> EGP</div>
        <div style="margin-top:8px;">
          <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
        </div>

        <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
          <?php if ($stock > 0): ?>
            <a class="btn" href="cart_action.php?action=add&id=<?= (int)$book['id'] ?>">Add to Cart</a>
          <?php else: ?>
            <button class="btn" disabled style="opacity:.6; cursor:not-allowed;">Add to Cart</button>
          <?php endif; ?>

          <a class="btn secondary" href="index.php">Back</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
