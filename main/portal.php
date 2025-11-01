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
            // Assuming you create this file from my previous instructions
            echo '<link rel="stylesheet" href="../css/pages/librarian.css">';
        } else if ($role == 'Admin') {
            echo '<link rel="stylesheet" href="../css/pages/admin.css">';
        }
    ?>
    
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
</head>
<body>
    
    <?php
        if ($role == 'Student') {
            include 'includes/_navbar_student.php';
        } else if ($role == 'Librarian') {
            include 'includes/_navbar_librarian.php';
        } else if ($role == 'Admin') {
            include 'includes/_navbar_admin.php';
        }
    ?>

    <main class="main-content">
        </main>

    <?php
        include 'includes/_modals_common.php';
        if ($role == 'Student') {
            include 'includes/_modals_student.php';
        } else if ($role == 'Librarian') {
            include 'includes/_modals_librarian.php';
        } else if ($role == 'Admin') {
            include 'includes/_modals_admin.php';
        }
    ?>

    <?php
        if ($role == 'Student') {
            echo '<script type="module" src="JS/student.js"></script>';
        } else if ($role == 'Librarian') {
            echo '<script type="module" src="JS/librarian.js"></script>';
        } else if ($role == 'Admin') {
            echo '<script type="module" src="JS/admin.js"></script>';
        }
    ?>
</body>
</html>