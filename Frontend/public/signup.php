<?php
require_once __DIR__ . '/../../Backend/data/repo_db.php';
session_start();

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  $fullName = $_POST['full_name'] ?? '';
  $email    = $_POST['email'] ?? '';

  // create as 'customer' to match DB enum ('customer'|'admin')
  $res = db_user_create($username, $password, $fullName, $email, 'customer');

  if ($res['ok']) {
    // fetch created user and store username+role in session
    $dbu = db_user_find_by_username(trim($username));
    $role = $dbu['role'] ?? 'customer';
    $_SESSION['user'] = ['username' => trim($username), 'role' => $role];
    header("Location: index.php");
    exit;
  } else {
    $err = $res['message'] ?? 'Signup failed.';
  }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="hero">
    <h1>Sign Up</h1>
    <p>Create an account to checkout and view orders.</p>
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

      <div>
        <div class="small">Full Name (optional)</div>
        <input class="input" name="full_name">
      </div>

      <div>
        <div class="small">Email (optional)</div>
        <input class="input" name="email">
      </div>

      <button class="btn" type="submit" style="margin-top:10px;">Create Account</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
