<?php
    session_start();
    // If user is already logged in, send them to the portal
    if (isset($_SESSION['role'])) {
        header('Location: portal.php');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Sign In</title>
    <!-- Updated to use the modular auth CSS -->
    <link rel="stylesheet" href="../css/pages/auth.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
</head>
<body>
    <div class="signin-container">
        <div class="signin-card">
            <!-- Left Panel -->
            <div class="left-panel">
                <div class="content-wrapper">
                    <h1 class="main-title">Welcome to Library Management System</h1>
                    <p class="description">Access library resources, check book availability, and manage your borrowed items through our comprehensive student library management system.</p>
                    
                    <div class="announcement-box">
                        <h3>New Books Available!</h3>
                        <p>All New Books Published by Favorite Titles are Finally on Shelf. Hurry and Grab your read for you will fall for all the Hottest Reads.</p>
                    </div>
                </div>
            </div>
            
            <!-- Right Panel -->
            <div class="right-panel">
                <!-- UPDATED LOGO CONTAINER -->
                <div class="logo-container">
                    <!-- This is now a toggle, not a direct link -->
                    <a href="index.php" id="logo-dropdown-toggle" class="logo-link" aria-label="Open Menu">
                        <img src="../assets/icons/LibraryMS_Logo.png" alt="Library Logo">
                    </a>
                </div>
                <!-- END UPDATED LOGO CONTAINER -->
                
                <div class="form-wrapper">
                    <h2 class="form-title">Sign In</h2>
                    <p class="form-subtitle">Enter your credentials to continue</p>
                    
                    <!-- Updated form with ID and correct attributes for JS -->
                    <form class="signin-form" id="login-form">
                        <div class="form-group">
                            <label for="username">Username or Student ID</label>
                            <!-- NOTE: id and name are 'username' to match auth.js -->
                            <input type="text" id="username" name="username" placeholder="e.g., 20240001-N">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password">
                        </div>
                              
                        <div class="form-options">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" id="showPassword">
                                <label for="showPassword">Show Password</label>
                            </div>
                            <!-- Updated link to forgot_password.php (assumed) -->
                            <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                        </div>
                        
                        <button type="submit" class="signin-button">Sign in</button>
                        
                        <!-- Updated link to register.php -->
                        <a href="register.php" class="register-link">Register</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script type="module" src="../js/pages/auth.js"></script>
</body>
</html>
