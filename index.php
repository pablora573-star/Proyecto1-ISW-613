<?php
  //check if user is logged in
  //if not, redirect to login page
  session_start();
  if (isset($_SESSION['username'])) {
      header("Location: /pages/dashboard.php");
      exit();
  }

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
</head>
<body>
  <h1>Login</h1>
  <form action="actions/login.php" method="post">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>
    <br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    <br>
    <button type="submit">Login</button>

    <p>¿No tienes una cuenta? <a href="pages/registration.php">Register here</a></p>
  </form>
</body>
</html>