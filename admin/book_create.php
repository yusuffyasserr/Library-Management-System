<?php
session_start();

require_once __DIR__ . '/../Backend/auth/admin_auth.php';
require_once __DIR__ . '/../Backend/data/repo_db.php';
require_once __DIR__ . '/../Frontend/includes/header.php';

$flash = null;
$publishers = db_publishers_list();

/* ✅ Allowed categories (STRICT – from sheet) */
$ALLOWED_CATEGORIES = ['Science', 'Art', 'Religion', 'History', 'Geography'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $isbn        = $_POST['isbn'] ?? '';
  $title       = $_POST['title'] ?? '';
  $authors     = $_POST['authors'] ?? '';
  $category    = $_POST['category'] ?? '';
  $year        = (int)($_POST['published_year'] ?? 0);
  $price       = (int)($_POST['price'] ?? 0);
  $stock       = (int)($_POST['stock'] ?? 0);
  $publisherId = (int)($_POST['publisher_id'] ?? 0);

  $res = db_book_create(
    $isbn,
    $title,
    $authors,
    $category,
    $year,
    $price,
    $stock,
    $publisherId
  );

  if ($res['ok']) {
    header("Location: books.php?added=1");
    exit;
  } else {
    $flash = ['ok'=>false, 'message'=>$res['message'] ?? 'Failed to add book.'];
  }
}
?>

<div class="container">
  <div class="hero">
    <h1>Add Book</h1>
    <p>Create a new book in the database.</p>
  </div>

  <?php if ($flash): ?>
    <div class="card" style="margin-top:18px; border-color: rgba(255,92,122,.35); background: rgba(255,92,122,.08);">
      <b><?= htmlspecialchars($flash['message']) ?></b>
    </div>
  <?php endif; ?>

  <div class="card" style="margin-top:18px; max-width:720px;">
    <form class="form" method="post">

      <div>
        <div class="small">ISBN</div>
        <input
          class="input"
          name="isbn"
          pattern="[0-9]{10}|[0-9]{13}"
          title="ISBN must be 10 or 13 digits"
          required
        >
      </div>

      <div>
        <div class="small">Title</div>
        <input class="input" name="title" required>
      </div>

      <div>
        <div class="small">Authors</div>
        <input class="input" name="authors" required>
      </div>

      <div>
        <div class="small">Category</div>
        <select class="input" name="category" required>
          <option value="">Select category...</option>
          <?php foreach ($ALLOWED_CATEGORIES as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>">
              <?= htmlspecialchars($cat) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:12px;">
        <div>
          <div class="small">Published Year</div>
          <input
            class="input"
            type="number"
            name="published_year"
            min="1900"
            max="<?= (int)date('Y') ?>"
            required
          >
        </div>

        <div>
          <div class="small">Price (EGP)</div>
          <input
            class="input"
            type="number"
            name="price"
            min="1"
            required
          >
        </div>

        <div>
          <div class="small">Stock</div>
          <input
            class="input"
            type="number"
            name="stock"
            min="0"
            required
          >
        </div>
      </div>

      <div>
        <div class="small">Publisher</div>
        <select class="input" name="publisher_id" required>
          <option value="">Select publisher...</option>
          <?php foreach ($publishers as $p): ?>
            <option value="<?= (int)$p['id'] ?>">
              <?= htmlspecialchars($p['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;">
        <button class="btn" type="submit">Add Book</button>
        <a class="btn secondary" href="books.php">Back</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../Frontend/includes/footer.php'; ?>
