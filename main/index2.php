<?php
    // We need the DB connection and the CatalogueService
    require_once __DIR__ . '/../php/db_connect.php';
    require_once __DIR__ . '/../php/services/CatalogueService.php';
    
    $catalogueService = new CatalogueService($conn);
    
    // Fetch books for different shelves
    $recommendedBooks = $catalogueService->searchBooks("", 10, 0); // All books
    $thrillerBooks = $catalogueService->searchBooks("Thriller", 10, 0); // Thriller/Mystery
    $fantasyBooks = $catalogueService->searchBooks("Fantasy", 10, 0); // Fantasy
    $classicBooks = $catalogueService->searchBooks("Classic", 10, 0); // Classics
    $romanceBooks = $catalogueService->searchBooks("Romance", 10, 0); // Romance

    /**
     * Helper function to render a single "book shelf" carousel.
     */
    function renderBookShelf($title, $books) {
        if (empty($books)) return; // Don't show empty shelves

        echo '<section class="book-shelf">';
        echo '<div class="shelf-header">';
        echo "<h2>{$title}</h2>";
        // This link is decorative for now
        echo '<a href="index.php" class="see-all-link">See All <span class="material-icons-round">arrow_forward_ios</span></a>';
        echo '</div>';
        echo '<div class="shelf-carousel">';
        
        foreach ($books as $book) {
            echo '<a href="#" class="book-card-netflix" data-book-id="' . htmlspecialchars($book['book_id']) . '">';
            echo '<img src="../assets/covers/' . htmlspecialchars($book['cover_url']) . '" alt="' . htmlspecialchars($book['title']) . '">';
            echo '<h3>' . htmlspecialchars($book['title']) . '</h3>';
            echo '<p class="book-card-author">' . htmlspecialchars($book['author_names'] ?? 'N/A') . '</p>';
            echo '</a>';
        }
        
        echo '</div>'; // end shelf-carousel
        echo '</section>'; // end book-shelf
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library MS - Catalogue</title>
    
    <link rel="stylesheet" href="../css/shared/base.css">
    <link rel="stylesheet" href="../css/pages/visitor2.css">
    
    <link rel="stylesheet" href="../css/shared/modals.css">
    <link rel="stylesheet" href="../css/pages/visitor.css"> <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
</head>
<body class="netflix-body">

    <aside class="icon-sidebar">
        <div class="sidebar-logo-netflix">
            <img src="../assets/icons/Logo.png" alt="Library Logo">
        </div>
        <nav class="sidebar-nav-netflix">
            <a href="index2.php" class="nav-icon-link active" title="Home">
                <span class="material-icons-round">home</span>
            </a>
            <a href="index.php" class="nav-icon-link" title="Full Catalogue">
                <span class="material-icons-round">menu_book</span>
            </a>
        </nav>
        <div class="sidebar-footer-netflix">
            <a href="login.php" class="nav-icon-link" title="Login">
                <span class="material-icons-round">person</span>
            </a>
            <a href="login.php" class="nav-icon-link" title="Sign In">
                <span class="material-icons-round">login</span>
            </a>
        </div>
    </aside>

    <main class="main-content-netflix">
        <header class="netflix-header">
            <div class="search-bar-netflix">
                <span class="material-icons-round">search</span>
                <input type="text" placeholder="Search by title, author, or genre...">
            </div>
        </header>

        <div class="content-scroll-area">
            <?php
                // Render the shelves
                renderBookShelf("Recommended", $recommendedBooks);
                renderBookShelf("Thrillers & Mystery", $thrillerBooks);
                renderBookShelf("Fantasy & Adventure", $fantasyBooks);
                renderBookShelf("Classics", $classicBooks);
                renderBookShelf("Romance", $romanceBooks);
            ?>
        </div>
    </main>

    <div id="book-modal" class="modal-overlay">
        <div class="modal-content book-modal-content">
            </div>
    </div>
    
    <script type="module" src="../js/pages/visitor2.js"></script>
</body>
</html>