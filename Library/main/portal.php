<?php
    session_start();
    $role = $_SESSION['role'] ?? 'Visitor';
    
    if ($role == 'Visitor') {
        header('Location: login.php'); // Send visitors to login
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Portal</title>

    <link rel="stylesheet" href="../css/shared/base.css">
    <link rel="stylesheet" href="../css/shared/sidebar.css">
    <link rel="stylesheet" href="../css/shared/main.css">
    <link rel="stylesheet" href="../css/shared/tables.css">
    <link rel="stylesheet" href="../css/shared/modals.css">
    <link rel="stylesheet" href="../css/shared/forms.css">
    <link rel="stylesheet" href="../css/shared/responsive.css">

    <?php
        // Load CSS based on role
        if ($role == 'Student') {
            echo '<link rel="stylesheet" href="../css/pages/student.css">';
        } else if ($role == 'Librarian') {
            echo '<link rel="stylesheet" href="../css/pages/librarian.css">';
        } else if ($role == 'Admin') {
            // Admin uses both librarian and admin styles
            echo '<link rel="stylesheet" href="../css/pages/librarian.css">';
            echo '<link rel="stylesheet" href="../css/pages/admin.css">';
        }
    ?>
    
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
</head>
<body>
    
    <?php
        // === SIDEBAR NAVIGATION ===
        if ($role == 'Student') {
            include 'includes/_sidebar_student.php';
        } else if ($role == 'Librarian') {
            include 'includes/_sidebar_librarian.php';
        } else if ($role == 'Admin') {
            // Admin gets their own sidebar
            include 'includes/_sidebar_admin.php';
        }
    ?>

    <main class="main-content">
        <?php
            // === CONTENT PAGES ===
            if ($role == 'Student') {
                include 'pages/student_dashboard.php';
                include 'pages/student_catalogue.php'; 
                include 'pages/student_history.php';
                include 'pages/student_settings.php'; 
                
            } else if ($role == 'Librarian') {
                include 'pages/librarian_dashboard.php';
                include 'pages/librarian_circulation.php';
                include 'pages/librarian_catalogue.php';
                include 'pages/librarian_archive.php';
                include 'pages/librarian_users.php';
                
            } else if ($role == 'Admin') {
                // --- Admin-Specific Pages ---
                include 'pages/admin_dashboard.php';
                include 'pages/admin_accounts.php';
                include 'pages/admin_announcements.php';
                include 'pages/admin_settings.php';
                include 'pages/admin_logs.php';
                
                // --- Inherited Librarian Pages ---
                include 'pages/librarian_dashboard.php';
                include 'pages/librarian_circulation.php';
                include 'pages/librarian_catalogue.php';
                include 'pages/librarian_archive.php';
                include 'pages/librarian_users.php';
            }
        ?>
    </main>

    <?php
        // === MODALS ===
        if ($role == 'Student') {
            include 'includes/_modals_student.php'; 
        } else if ($role == 'Librarian') {
            include 'includes/_modals_librarian.php';
        } else if ($role == 'Admin') {
            // Admin gets their own modals *and* librarian modals
            include 'includes/_modals_librarian.php';
            include 'includes/_modals_admin.php';
        }
    ?>

    <?php
        // === JAVASCRIPT ===
        if ($role == 'Student') {
            echo '<script type="module" src="../js/pages/student.js"></script>';
        } else if ($role == 'Librarian') {
            echo '<script type="module" src="../js/pages/librarian.js"></script>';
        } else if ($role == 'Admin') {
            // --- THIS IS THE FIX ---
            // We NO LONGER load librarian.js
            // echo '<script type="module" src="../js/pages/librarian.js"></script>';
            // We ONLY load admin.js, which now contains all merged logic
            echo '<script type="module" src="../js/pages/admin.js"></script>';
            // --- END FIX ---
        }
    ?>
</body>
</html>