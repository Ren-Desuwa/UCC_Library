<?php
    session_start();
    $role = $_SESSION['role'] ?? 'Visitor';
    
    if ($role == 'Visitor') {
        header('Location: login.php'); // Send visitors to login
        exit;
    }
    
    // --- Get user info for the header ---
    $username = $_SESSION['username'] ?? 'User';
    $userFullName = $_SESSION['name'] ?? 'User'; // Get the user's full name
    $userIcon = ($role == 'Librarian') ? 'person_check' : 'person';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Portal</title>

    <link rel="stylesheet" href="../css/shared/base.css">
    <link rel="stylesheet" href="../css/shared/main.css">
    <link rel="stylesheet" href="../css/shared/tables.css">
    <link rel="stylesheet" href="../css/shared/modals.css">
    <link rel="stylesheet" href="../css/shared/forms.css">
    <link rel="stylesheet" href="../css/shared/responsive.css">
    <link rel="stylesheet" href="../css/shared/portal-redesign.css">
    
    <link rel="stylesheet" href="../css/pages/visitor-redesign.css">

    <?php
        if ($role == 'Student') {
            echo '<link rel="stylesheet" href="../css/pages/student.css">';
        } else if ($role == 'Librarian') {
            echo '<link rel="stylesheet" href="../css/pages/librarian.css">';
        }
    ?>
    
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
</head>
<body class="theme-dark">
    
    <header class="portal-header">
        <div class="header-content">
            
            <div class="portal-logo">
                <img src="../assets/icons/Logo.png" alt="Library MS Logo" class="library-logo">
                <div class="logo-text">
                    <span class="app-name">Library MS</span>
                </div>
            </div>

            <nav class="portal-nav">
                <?php if ($role == 'Student'): ?>
                    <a href="#dashboard" class="nav-item active" data-target="dashboard-content">Dashboard</a>
                    <a href="#search-books" class="nav-item" data-target="search-books-content">Catalogue</a>
                    <a href="#history" class="nav-item" data-target="history-content">History</a>
                    <a href="#settings" class="nav-item" data-target="settings-content">Settings</a>
                <?php elseif ($role == 'Librarian'): ?>
                    <a href="#dashboard" class="nav-item active" data-target="librarian-dashboard-content">Dashboard</a>
                    <a href="#circulation" class="nav-item" data-target="librarian-circulation-content">Circulation</a>
                    <a href="#catalog" class="nav-item" data-target="librarian-catalog-content">Catalog</a>
                    <a href="#users" class="nav-item" data-target="librarian-users-content">Users</a>
                    <a href="#reports" class="nav-item" data-target="librarian-reports-content">Reports</a>
                <?php endif; ?>
            </nav>

            <div class="portal-user">
                <button id="user-menu-toggle" class="user-menu-toggle">
                    <span class="material-icons-round user-icon"><?php echo $userIcon; ?></span>
                    <span class="user-name-toggle"><?php echo htmlspecialchars($userFullName); ?></span>
                    <span class="material-icons-round dropdown-icon">arrow_drop_down</span>
                </button>

                <div id="user-menu-dropdown" class="user-menu-dropdown">
                    <div class="user-info-dropdown">
                        <span class="user-info-label">Role:</span>
                        <span class="user-info-value"><?php echo htmlspecialchars($role); ?></span>
                    </div>
                    <div class="user-info-dropdown">
                        <span class="user-info-label">ID:</span>
                        <span class="user-info-value"><?php echo htmlspecialchars($username); ?></span>
                    </div>

                    <div class="dropdown-divider"></div>

                    <a href="#" id="theme-toggle-link" class="dropdown-item">
                        <span class="material-icons-round">light_mode</span>
                        Toggle Theme
                        <button id="theme-toggle-btn" class="icon-btn" aria-label="Toggle light/dark theme"></button>
                    </a>

                    <div class="dropdown-divider"></div>
                    
                    <a href="#" id="logout-link" class="dropdown-item logout-link">
                        <span class="material-icons-round">logout</span>
                        Log out
                    </a>
                </div>
            </div>

            <button class="mobile-nav-toggle icon-btn" aria-label="Toggle navigation">
                <span class="material-icons-round">menu</span>
            </button>
        </div>
    </header>

    <nav class="mobile-nav">
        </nav>


    <main class="main-content">
        <?php
            if ($role == 'Student') {
                include 'pages/student_dashboard.php';
                include 'pages/student_search.php';
                include 'pages/student_history.php';
                include 'pages/student_settings.php';
            } else if ($role == 'Librarian') {
                include 'pages/librarian_dashboard.php';
                include 'pages/librarian_circulation.php';
                include 'pages/librarian_catalog.php';
                include 'pages/librarian_users.php';
            }
        ?>
    </main>

    <?php
        if ($role == 'Student') {
            include 'includes/_modals_student.php';
        } else if ($role == 'Librarian') {
            include 'includes/_modals_librarian.php';
        }
    ?>
    
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
                <p>...</p>
                <h3>Data Usage</h3>
                <p>...</p>
                <ul>...</ul>
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
                <p>...</p>
                <ul>...</ul>
            </div>
        </div>
    </div>


    <script type="module" src="../js/shared/portal.js"></script> 
    <script type="module" src="../js/pages/visitor.js"></script> 
    
    <?php
        if ($role == 'Student') {
            echo '<script type="module" src="../js/pages/student.js"></script>';
        } else if ($role == 'Librarian') {
            echo '<script type="module" src="../js/pages/librarian.js"></script>';
        }
    ?>
</body>
</html>