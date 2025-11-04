<?php
session_start();
$email = $_POST['email'] ?? null;
$_SESSION['email'] = $email;
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../css/pages/auth.css">
  <title>Forgot Password</title>
  </style>
</head>
<body>
  
    <div class="form-wrapper" id="forgot-password-form">
      <div class="forgot-password-card">
      <h2 class="form-title">Forgot Password</h2>
      <br>
      <form class="form-group" method="POST">
        <label for="email">Enter your email address</label>
        <input type="email" id="email" name="email" required />
        <br>
        <br>
        <button class="signin-button" type="submit">Send Reset Link</button>
      </form>
      <a class="forgot-password" href="login.php" class="back-link">Back to Login</a>
    </div>
  </div>

  <script type="module" src="../js/pages/auth.js"></script>
</body>