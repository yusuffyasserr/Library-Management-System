<?php
require_once __DIR__ . '/../../Backend/data/repo_db.php';
session_start();

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  $u = db_user_find_by_username($username);

  if (!$u) {
    $err = "Invalid username or password.";
  } else {
    // if your DB column is password_hash instead of password, change this line accordingly
    $hash = $u['password_hash'] ?? '';

    if (!password_verify($password, $hash)) {
      $err = "Invalid username or password.";
    } else {
      // store role so header can detect admin users
      $role = $u['role'] ?? 'customer';
      $_SESSION['user'] = ['username' => $username, 'role' => $role];
      header("Location: index.php");
      exit;
    }
  }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="hero">
    <h1>Login</h1>
    <p>Login to checkout and view your orders.</p>
  </div>

  <?php if ($err): ?>
    <div class="card" style="margin-top:18px; border-color: rgba(255,92,122,.35); background: rgba(255,92,122,.08);">
      <b><?= htmlspecialchars($err) ?></b>
    </div>
  <?php endif; ?>

  <div class="card" style="margin-top:18px; max-width:520px;">
    <form class="form" method="post">
      <div>
        <div class="small">Username</div>
        <input class="input" name="username" required>
      </div>

      <div>
        <div class="small">Password</div>
        <input class="input" type="password" name="password" required>
      </div>

      <button class="btn" type="submit" style="margin-top:10px;">Login</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
