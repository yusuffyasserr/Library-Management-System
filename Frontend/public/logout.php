<?php
if (session_status() === PHP_SESSION_NONE) session_start();

unset($_SESSION['user']);
unset($_SESSION['cart']); // as required: cart cleared on logout

header("Location: index.php");
exit;
