<?php
session_start();

require_once __DIR__ . '/../../Backend/config/app.php';
require_once __DIR__ . '/../../Backend/data/repo_db.php';

require_once __DIR__ . '/../includes/header.php';

$books = db_books_list();

// ----- Filters (category + search) -----
$q = trim($_GET['q'] ?? '');
$cat = trim($_GET['cat'] ?? '');

$filtered = [];
foreach ($books as $b) {
    $okCat = ($cat === '' || $cat === 'All' || strcasecmp($b['category'], $cat) === 0);

    $haystack = strtolower(
        ($b['isbn'] ?? '') . ' ' .
        ($b['title'] ?? '') . ' ' .
        ($b['authors'] ?? '') . ' ' .
        ($b['publisher'] ?? '') . ' ' .
        ($b['category'] ?? '')
    );

    $okQ = ($q === '' || strpos($haystack, strtolower($q)) !== false);

    if ($okCat && $okQ) $filtered[] = $b;
}

// Build category list
$cats = ['All'];
foreach ($books as $b) {
    $c = $b['category'] ?? '';
    if ($c && !in_array($c, $cats, true)) $cats[] = $c;
}
sort($cats);
?>

<div class="container">
  <div class="hero">
    <h1>Online Bookstore</h1>
    <p>Browse publicly, add to cart as a guest. Checkout requires login.</p>
  </div>

  <div class="card" style="margin-top:18px;">
    <form method="get" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
      <select class="input" name="cat" style="max-width:160px;">
        <?php foreach ($cats as $c): ?>
          <option value="<?= htmlspecialchars($c) ?>" <?= ($c === ($cat ?: 'All')) ? 'selected' : '' ?>>
            <?= htmlspecialchars($c) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <input class="input" type="text" name="q" placeholder="Search..." value="<?= htmlspecialchars($q) ?>" style="flex:1; min-width:220px;">

      <button class="btn" type="submit">Search</button>
      <a class="btn secondary" href="index.php">Reset</a>
    </form>
  </div>

  <div class="grid" style="margin-top:16px;">
    <?php if (count($filtered) === 0): ?>
      <div class="col-12">
        <div class="card">
          <p style="color:var(--muted); margin:0;">No results found.</p>
        </div>
      </div>
    <?php endif; ?>

    <?php foreach ($filtered as $b): ?>
      <?php
        $stock = (int)($b['stock'] ?? 0);
        $badgeClass = ($stock <= 0) ? 'out' : (($stock <= 2) ? 'warn' : 'ok');
        $badgeText  = ($stock <= 0) ? 'Out of stock' : (($stock <= 2) ? 'Low stock' : 'In stock');

        // ✅ FIX: authors can be ARRAY (old dummy) or STRING (DB)
        $authorsText = is_array($b['authors']) ? implode(', ', $b['authors']) : (string)$b['authors'];
      ?>
      <div class="col-4">
        <div class="card">
          <h3 style="margin-top:0;"><?= htmlspecialchars($b['title']) ?></h3>

          <div class="small"><b>ISBN:</b> <?= htmlspecialchars($b['isbn']) ?></div>
          <div class="small"><b>Authors:</b> <?= htmlspecialchars($authorsText) ?></div>
          <div class="small"><b>Publisher:</b> <?= htmlspecialchars($b['publisher']) ?></div>
          <div class="small"><b>Category:</b> <?= htmlspecialchars($b['category']) ?> • <b>Year:</b> <?= (int)($b['year'] ?? 0) ?></div>

          <div style="display:flex; justify-content:space-between; align-items:center; margin-top:14px;">
            <div class="price"><?= (int)$b['price'] ?> EGP</div>
            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
          </div>

          <div style="margin-top:14px; display:flex; gap:10px;">
            <a class="btn secondary" href="book.php?id=<?= (int)$b['id'] ?>">View</a>

            <?php if ($stock > 0): ?>
              <a class="btn" href="cart_action.php?action=add&id=<?= (int)$b['id'] ?>">Add to Cart</a>
            <?php else: ?>
              <button class="btn" disabled style="opacity:.6; cursor:not-allowed;">Add to Cart</button>
            <?php endif; ?>
          </div>

        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
