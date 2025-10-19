<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard | Beauty Cosmetics</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> 💄</h2>
    <p>You're now logged in to your Beauty account!</p>
    <button onclick="window.location.href='logout.php'">Logout</button>
  </div>
</body>
</html>
