<?php
require_once __DIR__ . '/../Backend/auth/admin_auth.php';
require_once __DIR__ . '/../Frontend/includes/header.php';
?>

<div class="hero">
  <h1>Admin Dashboard</h1>
  <p>Manage books, publishers, stock replenishment, and reports.</p>
</div>

<div class="grid">
  <div class="col-4">
    <div class="card">
      <h3>Books</h3>
      <p class="small">Add, edit, and manage book inventory.</p>
      <a class="btn" href="books.php">Manage Books</a>
    </div>
  </div>

  <div class="col-4">
    <div class="card">
      <h3>Publishers</h3>
      <p class="small">Manage publishers list.</p>
      <a class="btn secondary" href="publishers.php">Manage Publishers</a>
    </div>
  </div>

  <div class="col-4">
    <div class="card">
      <h3>Replenishments</h3>
      <p class="small">Confirm replenishment orders to add stock.</p>
      <a class="btn secondary" href="replenishments.php">View Replenishments</a>
    </div>
  </div>

  <div class="col-4">
    <div class="card">
      <h3>Reports</h3>
      <p class="small">Sales & performance reports.</p>
      <a class="btn secondary" href="reports.php">View Reports</a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../Frontend/includes/footer.php'; ?>
