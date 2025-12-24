<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$user = $_SESSION['user'] ?? null;
$isAdmin = $user && (($user['role'] ?? '') === 'admin');

$cartCount = 0;
if (!empty($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $q) $cartCount += (int)$q;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BookStore</title>

  <!-- ✅ ABSOLUTE PATH (works for Frontend + Admin) -->
  <link rel="stylesheet" href="/LIBRARY%20MANAGEMENT%20SYSTEM/Frontend/assets/css/style.css">
</head>
<body>

<div class="nav">
  <div class="nav-inner">

    <a class="brand" href="/LIBRARY%20MANAGEMENT%20SYSTEM/Frontend/public/index.php">
      <span class="logo"></span>
      <span>BookStore</span>
    </a>

    <form class="search" action="/LIBRARY%20MANAGEMENT%20SYSTEM/Frontend/public/index.php" method="get">
      <input name="q" placeholder="Search by ISBN / Title / Author / Publisher..."
             value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    </form>

    <div class="nav-links">
      <a class="pill" href="/LIBRARY%20MANAGEMENT%20SYSTEM/Frontend/public/index.php">Browse</a>
      <a class="pill" href="/LIBRARY%20MANAGEMENT%20SYSTEM/Frontend/public/cart.php">
        Cart (<?= $cartCount ?>)
      </a>

      <?php if (!$user): ?>
        <a class="pill" href="/LIBRARY%20MANAGEMENT%20SYSTEM/Frontend/public/login.php">Login</a>
        <a class="pill" href="/LIBRARY%20MANAGEMENT%20SYSTEM/Frontend/public/signup.php">Sign Up</a>

      <?php else: ?>

        <?php if ($isAdmin): ?>
          <a class="pill" href="/LIBRARY%20MANAGEMENT%20SYSTEM/admin/dashboard.php">Admin</a>
        <?php else: ?>
          <a class="pill" href="/LIBRARY%20MANAGEMENT%20SYSTEM/Frontend/public/orders.php">My Orders</a>
        <?php endif; ?>

        <a class="pill" href="/LIBRARY%20MANAGEMENT%20SYSTEM/Frontend/public/logout.php">Logout</a>
      <?php endif; ?>
    </div>

  </div>
</div>

<div class="container">
