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
  
    <div class="form-wrapper">
      <div class="forgot-password-card">
      <h2 class="form-title">Send Code</h2>
      <p class="form-subtitle">An email has been sent to your inbox</p>
      <form class="form-group" method="POST">
        <label for="code">Enter Code</label>
        <input type="number" id="code" name="code" required />
        <br>
        <br>
        <button class="signin-button" type="submit">Submit Code</button>
      </form>
      <a class="forgot-password" href="login.php" class="back-link">Back to Login</a>
    </div>
  </div>
</body>