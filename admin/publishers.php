<?php
require_once __DIR__ . '/../Backend/auth/admin_auth.php';
require_once __DIR__ . '/../Backend/data/repo_db.php';

require_once __DIR__ . '/../Frontend/includes/header.php';

$publishers = db_publishers_list();

$editId = (int)($_GET['edit'] ?? 0);
$editPublisher = $editId ? db_publisher_find($editId) : null;

$flash = $_SESSION['flash'] ?? null;
$flash_err = $_SESSION['flash_err'] ?? null;
unset($_SESSION['flash'], $_SESSION['flash_err']);
?>

<div class="container">
  <div class="hero">
    <h1>Manage Publishers</h1>
    <p>Add and edit publishers (saved in MySQL).</p>
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
    <!-- LEFT: List -->
    <div class="col-8">
      <div class="card" style="overflow:auto;">
        <h3 style="margin-top:0;">Publishers List</h3>

        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Phone</th>
              <th>Address</th>
              <th style="width:120px;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($publishers as $p): ?>
              <tr>
                <td><?= (int)$p['id'] ?></td>
                <td style="font-weight:900;"><?= htmlspecialchars($p['name']) ?></td>
                <td><?= htmlspecialchars($p['phone'] ?? '') ?></td>
                <td><?= htmlspecialchars($p['address'] ?? '') ?></td>
                <td>
                  <a class="btn secondary" href="publishers.php?edit=<?= (int)$p['id'] ?>">Edit</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

      </div>
    </div>

    <!-- RIGHT: Form -->
    <div class="col-4">
      <div class="card">
        <?php if ($editPublisher): ?>
          <h3 style="margin-top:0;">Edit Publisher</h3>
          <form class="form" method="post" action="publisher_action.php">
            <input type="hidden" name="mode" value="edit">
            <input type="hidden" name="id" value="<?= (int)$editPublisher['id'] ?>">

            <div>
              <div class="small">Name</div>
              <input class="input" name="name" value="<?= htmlspecialchars($editPublisher['name']) ?>" required>
            </div>

            <div>
              <div class="small">Phone</div>
              <input class="input" name="phone" value="<?= htmlspecialchars($editPublisher['phone'] ?? '') ?>">
            </div>

            <div>
              <div class="small">Address</div>
              <input class="input" name="address" value="<?= htmlspecialchars($editPublisher['address'] ?? '') ?>">
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:6px;">
              <button class="btn" type="submit">Save Changes</button>
              <a class="btn secondary" href="publishers.php">Cancel</a>
            </div>
          </form>

        <?php else: ?>
          <h3 style="margin-top:0;">Add Publisher</h3>
          <form class="form" method="post" action="publisher_action.php">
            <input type="hidden" name="mode" value="add">

            <div>
              <div class="small">Name</div>
              <input class="input" name="name" placeholder="Publisher name" required>
            </div>

            <div>
              <div class="small">Phone</div>
              <input class="input" name="phone" placeholder="Optional">
            </div>

            <div>
              <div class="small">Address</div>
              <input class="input" name="address" placeholder="Optional">
            </div>

            <button class="btn" type="submit" style="margin-top:6px;">Add Publisher</button>
          </form>
        <?php endif; ?>
      </div>

      <div style="margin-top:12px;">
        <a class="btn secondary" href="dashboard.php">Back to Dashboard</a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../Frontend/includes/footer.php'; ?>
