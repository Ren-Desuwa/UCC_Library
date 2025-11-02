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
        if ($role == 'Student') {
            echo '<link rel="stylesheet" href="../css/pages/student.css">';
        } else if ($role == 'Librarian') {
            echo '<link rel="stylesheet" href="../css/pages/librarian.css">';
        } else if ($role == 'Admin') {
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
            // include 'includes/_sidebar_admin.php';
        }
    ?>

    <main class="main-content">
        <?php
            // === CONTENT PAGES ===
            if ($role == 'Student') {
                include 'pages/student_dashboard.php';
                // FIXED: Changed 'student_catalogue.php' to 'student_search.php'
                include 'pages/student_catalogue.php'; 
                include 'pages/student_history.php';
                include 'pages/student_settings.php'; 
            } else if ($role == 'Librarian') {
                include 'pages/librarian_dashboard.php';
                include 'pages/librarian_circulation.php';
                include 'pages/librarian_catalogue.php';
                include 'pages/librarian_archive.php';
                include 'pages/librarian_users.php';
            }
        ?>
    </main>

    <?php
        // include 'includes/_modals_common.php'; 
        if ($role == 'Student') {
            include 'includes/_modals_student.php'; 
        } else if ($role == 'Librarian') {
            include 'includes/_modals_librarian.php';
        } else if ($role == 'Admin') {
            // include 'includes/_modals_admin.php';
        }
    ?>

    <?php
        if ($role == 'Student') {
            echo '<script type="module" src="../js/pages/student.js"></script>';
        } else if ($role == 'Librarian') {
            echo '<script type="module" src="../js/pages/librarian.js"></script>';
        } else if ($role == 'Admin') {
            // echo '<script type="module" src="../js/pages/admin.js"></script>';
        }
    ?>
</body>
</html>