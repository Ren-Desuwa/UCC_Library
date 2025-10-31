<?php
    // 1. Start the session to find out who is logged in
    session_start();
    
    // 2. Determine the user's role. Default to 'Visitor'
    $role = $_SESSION['role'] ?? 'Visitor';
    
    // 3. If a visitor (not logged in) tries to access, send them to the index.
    if ($role == 'Visitor') {
        header('Location: index.php');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    
    <?php
        if ($role == 'Student') {
            echo '<link rel="stylesheet" href="CSS/StudentPortal.css">';
        } else if ($role == 'Librarian') {
            echo '<link rel="stylesheet" href="CSS/LibrarianPortal.css">';
        } else if ($role == 'Admin') {
            echo '<link rel="stylesheet" href="CSS/AdminPortal.css">';
        }
    ?>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">

        <?php
            // 5. Use PHP 'include' to insert the correct sidebar
            // We will create these "partial" files in Step 2.
            if ($role == 'Student') {
                include 'includes/_sidebar_student.php';
            } else if ($role == 'Librarian') {
                include 'includes/_sidebar_librarian.php';
            } else if ($role == 'Admin') {
                include 'includes/_sidebar_admin.php';
            }
        ?>

        <main class="main-content">
            </main>

    </div> <?php
        // 7. Include ALL modal skeletons your app might need.
        // They are all hidden by default. No more copy-pasting!
        include 'includes/_modals_common.php'; // (e.g., book details modal)
        
        if ($role == 'Student') {
            include 'includes/_modals_student.php'; // (e.g., history receipt modal)
        } else if ($role == 'Librarian') {
            include 'includes/_modals_librarian.php'; // (e.g., add book, select copy)
        } else if ($role == 'Admin') {
            include 'includes/_modals_admin.php'; // (e.g., create account, resolve password)
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