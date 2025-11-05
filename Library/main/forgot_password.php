<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Forgot Password</title>
    
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
<body class="theme-dark"> <header class="site-header">
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
            
            <h2 class="form-title">Forgot Password</h2>
            <p class="form-subtitle">Enter your email or username to get a reset link.</p>
            
            <form class="signin-form" id="forgot-password-form">
                <div class="form-group">
                    <label for="recovery_identifier">Username or Email</label>
                    <input type="text" id="recovery_identifier" name="recovery_identifier" placeholder="e.g., 20240001-N or user@email.com" required>
                </div>
                
                <button type="submit" class="signin-button">Send Reset Link</button>
                
                <a href="login.php" class="signin-link">
                    Back to Sign In
                </a>
            </form>

        </div>
    </main>
    <footer class="site-footer-main">
        <p>Library Management System - For Educational Purposes</p>
        <div class="footer-links">
            <a href="#" class="modal-trigger-link" data-modal-target="privacy-modal">Privacy Policy</a>
            <a href="#" class="modal-trigger-link" data-modal-target="terms-modal">Terms of Use</a>
        </div>
    </footer>

    <div id="privacy-modal" class="modal-overlay">
        <div class="modal-content text-modal-content">
            <div class="modal-header">
                <h2>Privacy Policy</h2>
                <button class="modal-close-btn-icon"><span class="material-icons-round">close</span></button>
            </div>
            <div class="modal-body">
                <h3>Our Commitment to Your Privacy</h3>
                <p>This system is for educational purposes. All personal data, including Student ID, name, and borrowing history, is stored securely within our local database. This information is used solely for the function of the library management system, such as tracking checkouts and managing reservations.</p>
                
                <h3>Data Usage</h3>
                <p>Your data will not be shared with any third parties. It is protected and will only be used to:</p>
                <ul>
                    <li>Manage your library account and borrowed items.</li>
                    <li>Notify you of due dates or available reservations.</li>
                    <li>Ensure accountability for library resources.</li>
                </ul>
            </div>
        </div>
    </div>
    <div id="terms-modal" class="modal-overlay">
        <div class="modal-content text-modal-content">
            <div class="modal-header">
                <h2>Terms of Use</h2>
                <button class="modal-close-btn-icon"><span class="material-icons-round">close</span></button>
            </div>
            <div class="modal-body">
                <h3>Library Rules & Regulations</h3>
                <p>By using this system and borrowing books, you agree to the following terms:</p>
                <ul>
                    <li>All borrowed items must be returned on or before the specified due date.</li>
                    <li>Failure to return a book on time will result in a temporary suspension of borrowing privileges and/or a fine, as per university policy.</li>
                    <li>You are responsible for the condition of the books you borrow. Any damage (e.g., writing, torn pages, water damage) must be reported and may incur a replacement fee.</li>
                    <li>Do not share your Student ID or account with others. You are solely responsible for all items checked out to your account.</li>
                    <li>Violations of these terms may lead to disciplinary action.</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script type="module" src="../js/pages/auth.js"></script>
    
    <script type="module" src="../js/pages/visitor.js"></script> 
</body>
</html>