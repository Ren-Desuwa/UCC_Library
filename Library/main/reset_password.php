<?php
$userID = $_GET['userID'] ?? null;
if ($userID && is_numeric($userID)) {
    $_SESSION['userID'] = $userID;
}
var_dump($userID);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../css/pages/auth.css">
  <title>Reset Password</title>
  </style>
</head>
<body>
  
    <div class="form-wrapper" id="reset-password-form">
        
      <div class="forgot-password-card">
      <h2 class="form-title">Reset Password</h2>
      <p class="form-subtitle">Enter the new password</p>
      <form class="form-group" method="POST">
        <input type="hidden" id="userID" name="userID" value="<?= htmlspecialchars($userID) ?>">
        <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password">
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm Password</label>
                                <input type="password" id="confirmPassword" name="confirmPassword">
                            </div>
        <button class="signin-button" type="submit">Reset Password</button>
                            
      </form>
      <br>
      <div class="checkbox-wrapper">
            <input type="checkbox" id="showPassword">
            <label for="showPassword">Show Password</label>
        </div>
      <a class="forgot-password" href="login.php" class="back-link">Back to Login</a>
    </div>
  </div>

  <script type="module" src="../js/pages/auth.js"></script>
</body>