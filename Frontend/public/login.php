<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/header.php';

$error = '';
$redirect = $_GET['redirect'] ?? '/LIBRARY%20MANAGEMENT%20SYSTEM/Frontend/public/index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');

  // Temporary demo users (until DB is connected)
  $demoUsers = [
    'chaly' => ['password' => '1234', 'role' => 'customer', 'name' => 'Chaly'],
    'admin' => ['password' => 'admin', 'role' => 'admin', 'name' => 'Admin']
  ];

  if (!isset($demoUsers[$username]) || $demoUsers[$username]['password'] !== $password) {
    $error = 'Invalid username or password.';
  } else {
    $_SESSION['user'] = [
      'username' => $username,
      'role' => $demoUsers[$username]['role'],
      'name' => $demoUsers[$username]['name'],
    ];

    // If admin, go to admin dashboard
    if ($_SESSION['user']['role'] === 'admin') {
      header("Location: /LIBRARY%20MANAGEMENT%20SYSTEM/admin/dashboard.php");
      exit;
    }

    header("Location: $redirect");
    exit;
  }
}
?>

<div class="hero">
  <h1>Login</h1>
  <p>Login to checkout and view orders.</p>
</div>

<div class="card" style="margin-top:18px; max-width:520px;">
  <?php if ($error): ?>
    <div class="card" style="border-color: rgba(255,92,122,.35); background: rgba(255,92,122,.08); margin-bottom:12px;">
      <b><?= htmlspecialchars($error) ?></b>
    </div>
  <?php endif; ?>

  <form method="post" class="form">
    <div>
      <label>Username</label>
      <input class="input" name="username" placeholder="e.g. chaly" required>
    </div>

    <div>
      <label>Password</label>
      <input class="input" name="password" type="password" placeholder="e.g. 1234" required>
    </div>

    <button class="btn" type="submit">Login</button>
    <a class="btn secondary" href="signup.php">Create account</a>

    <div class="small" style="margin-top:8px;">
      Demo users: <b>chaly/1234</b> (customer) — <b>admin/admin</b> (admin)
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
