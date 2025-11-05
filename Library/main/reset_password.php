<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Reset Password</title>
    
    <link rel="stylesheet" href="../css/shared/base.css">
    <link rel="stylesheet" href="../css/shared/main.css">
    <link rel="stylesheet" href="../css/shared/tables.css">
    <link rel="stylesheet" href="../css/shared/modals.css">
    <link rel="stylesheet" href="../css/shared/forms.css">
    <link rel="stylesheet" href="../css/shared/responsive.css">
    <link rel="stylesheet" href="../css/pages/visitor.css">
    <link rel="stylesheet" href="../css/pages/visitor-redesign.css">
    <link rel="stylesheet" href="../css/pages/auth.css">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
</head>
<body class="theme-dark">

    <header class="site-header">
        <div class="header-content">
            <div class="header-logo">
                <img src="../assets/icons/Logo.png" alt="Library MS Logo" class="library-logo">
                <span class="app-name">Library MS</span>
            </div>
            <nav class="header-nav">
                <a href="index.php" class="nav-link">Search Catalogue</a>
                <a href="about.php" class="nav-link">About Us</a>
            </nav>
            <div class="header-actions">
                <button id="theme-toggle-btn" class="icon-btn" aria-label="Toggle light/dark theme">
                    <span class="material-icons-round">light_mode</span>
                </button>
                <a href="login.php" class="action-btn primary-btn">
                    Sign In
                    <span class="material-icons-round">login</span>
                </a>
            </div>
            <button class="mobile-nav-toggle icon-btn" aria-label="Toggle navigation">
                <span class="material-icons-round">menu</span>
            </button>
        </div>
    </header>

    <nav class="mobile-nav">
        <a href="index.php" class="nav-link">Search Catalogue</a>
        <a href="about.php" class="nav-link">About Us</a>
        <a href="login.php" class="nav-link">Sign In / Log In</a>
    </nav>

    <main class="main-content auth-main">
        <div class="auth-card">
            
            <h2 class="form-title">Reset Your Password</h2>
            <p class="form-subtitle">Enter your email, the OTP we "sent" you, and a new password.</p>
            
            <form class="signin-form" id="reset-password-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="The email you used to register" required>
                </div>

                <div class="form-group">
                    <label for="otp">One-Time Password (OTP)</label>
                    <input type="text" id="otp" name="otp" placeholder="The 6-digit code" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="form-options">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="showPassword">
                        <label for="showPassword">Show Passwords</label>
                    </div>
                </div>
                
                <button type="submit" class="signin-button">Set New Password</button>
                
                <a href="login.php" class="signin-link">
                    Back to Sign In
                </a>
            </form>

        </div>
    </main>
    <footer class="site-footer-main">
        </footer>

    <script type="module" src="../js/pages/auth.js"></script>
    
    <script type="module" src="../js/pages/visitor.js"></script> 
</body>
</html>