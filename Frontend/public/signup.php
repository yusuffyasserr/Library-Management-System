<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $name     = trim($_POST['name'] ?? '');
  $email    = trim($_POST['email'] ?? '');

  if ($username === '' || $password === '' || $name === '' || $email === '') {
    $error = 'Please fill all fields.';
  } else {
    // For now: just auto-login as customer
    $_SESSION['user'] = [
      'username' => $username,
      'role' => 'customer',
      'name' => $name
    ];
    $success = 'Account created successfully. You are now logged in.';
  }
}
?>

<div class="hero">
  <h1>Sign Up</h1>
  <p>Create a customer account to checkout and view orders.</p>
</div>

<div class="card" style="margin-top:18px; max-width:520px;">
  <?php if ($error): ?>
    <div class="card" style="border-color: rgba(255,92,122,.35); background: rgba(255,92,122,.08); margin-bottom:12px;">
      <b><?= htmlspecialchars($error) ?></b>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="card" style="border-color: rgba(64,243,154,.35); background: rgba(64,243,154,.08); margin-bottom:12px;">
      <b><?= htmlspecialchars($success) ?></b>
    </div>
    <a class="btn" href="index.php">Go to Browse</a>
  <?php else: ?>

    <form method="post" class="form">
      <div>
        <label>Full Name</label>
        <input class="input" name="name" required>
      </div>
      <div>
        <label>Email</label>
        <input class="input" name="email" type="email" required>
      </div>
      <div>
        <label>Username</label>
        <input class="input" name="username" required>
      </div>
      <div>
        <label>Password</label>
        <input class="input" name="password" type="password" required>
      </div>

      <button class="btn" type="submit">Create Account</button>
      <a class="btn secondary" href="login.php">Back to Login</a>
    </form>

  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
