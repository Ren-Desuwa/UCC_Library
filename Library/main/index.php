<?php
    // We need the DB connection and the CatalogueService
    require_once __DIR__ . '/../php/db_connect.php';
    require_once __DIR__ . '/../php/services/CatalogueService.php';
    
    $catalogueService = new CatalogueService($conn);
    
    // UPDATED: Fetch 11 books (one more than the display limit)
    $shelfLimit = 10;
    $fetchLimit = $shelfLimit + 1;
    
    // UPDATED: Added "Classics" and "Romance"
    $recommendedBooks = $catalogueService->searchBooks("", "", "", "", "", "", $fetchLimit, 0); 
    $thrillerBooks = $catalogueService->searchBooks("", "", "Thriller", "", "", "", $fetchLimit, 0);
    $fantasyBooks = $catalogueService->searchBooks("", "", "Fantasy", "", "", "", $fetchLimit, 0);
    $classicBooks = $catalogueService->searchBooks("", "", "Classic", "", "", "", $fetchLimit, 0);
    $romanceBooks = $catalogueService->searchBooks("", "", "Romance", "", "", "", $fetchLimit, 0);

    /**
     * ADDED: Helper function to render a single "book shelf" carousel.
     */
    function renderBookShelf($title, $books, $displayLimit = 10) {
        if (empty($books)) return; 

        // --- NEW LOGIC START ---
        // Check if the number of books exceeds the display limit
        $bookCount = count($books);
        $hasMoreBooks = $bookCount > $displayLimit;

        if ($hasMoreBooks) {
            // Remove the extra book so we only display the limit
            array_pop($books);
        }
        // --- NEW LOGIC END ---

        // UPDATED: Renamed classes to -visitor
        echo '<section class="book-shelf-visitor">';
        echo '<div class="shelf-header-visitor">';
        echo "<h2>{$title}</h2>";
        
        // UPDATED: Only show the link if $hasMoreBooks is true
        if ($hasMoreBooks) {
            // ADDED: class="open-see-all-modal-btn" and data-genre attribute
            echo '<a href="#" class="see-all-link-visitor open-see-all-modal-btn" data-genre="' . htmlspecialchars($title) . '">See All <span class="material-icons-round">arrow_forward_ios</span></a>';
        }

        echo '</div>';
        echo '<div class="shelf-carousel-visitor">';
        
        foreach ($books as $book) {
            echo '<a href="#" class="book-card-visitor open-book-modal-btn" data-book-id="' . htmlspecialchars($book['book_id']) . '">';
            echo '<img src="../assets/covers/' . htmlspecialchars($book['cover_url']) . '" alt="' . htmlspecialchars($book['title']) . '">';
            echo '<h3>' . htmlspecialchars($book['title']) . '</h3>';
            echo '<p class="book-card-author-visitor">' . htmlspecialchars($book['author_names'] ?? 'N/A') . '</p>';
            echo '</a>';
        }
        
        echo '</div>'; 
        echo '</section>';
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library MS - Visitor Portal</title>
    
    <link rel="stylesheet" href="../css/shared/base.css">
    <link rel="stylesheet" href="../css/shared/sidebar.css">
    <link rel="stylesheet" href="../css/shared/main.css">
    <link rel="stylesheet" href="../css/shared/tables.css">
    <link rel="stylesheet" href="../css/shared/modals.css">
    <link rel="stylesheet" href="../css/shared/forms.css">
    <link rel="stylesheet" href="../css/shared/responsive.css">

    <link rel="stylesheet" href="../css/pages/visitor.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">

        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/icons/Logo.png" alt="Library MS Logo" class="library-logo"> 
                <div class="logo-text">
                    <span class="app-name">Library MS</span>
                    <span class="portal-name">Visitor Portal</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="#search-books" class="nav-item active" data-target="search-books-content">
                    <span class="material-icons-round">search</span>
                    Search Books
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    <span class="material-icons-round user-icon">account_circle</span>
                    <div class="user-details">
                        <p class="user-role">Guest</p>
                        <p class="user-id">Not Signed In</p>
                    </div>
                </div>
                <a href="login.php" class="login-link">
                    <span class="material-icons-round">login</span>
                    Sign In / Log In
                </a>
            </div>
        </aside>

        <main class="main-content">
            
            <div id="search-books-content" class="content-panel active">
                <header class="main-header">
                    <h1>Library Catalogue</h1>
                    <p>Browse our collection or search for something specific.</p>
                </header>

                <section class="search-panel">
        
                    <div class="search-controls-advanced">
                        <div class="search-bar-advanced" id="search-bar-advanced">
                            <span class="material-icons-round search-icon">search</span>
                            <input type="text" id="catalogue-search-input" class="search-input-flex" placeholder="Search... e.g., author:Rowling year:2012-2015 Harry Potter">
                        </div>
                        <div class="filter-container">
                            <button class="filter-btn" id="filter-btn">
                                <span class="material-icons-round">filter_list</span>
                                Filter
                            </button>
                            <div class="filter-dropdown" id="filter-dropdown">
                                <a href="#" data-filter-type="author">Author</a>
                                <a href="#" data-filter-type="genre">Genre</a>
                                <a href="#" data-filter-type="year">Year</a>
                                <a href="#" data-filter-type="status">Status</a>
                            </div>
                        </div>
                    </div>

                    <div id="catalogue-grid-view">
                        <?php
                            // UPDATED: Pass the $shelfLimit to the function
                            renderBookShelf("Recommended", $recommendedBooks, $shelfLimit);
                            renderBookShelf("Thrillers & Mystery", $thrillerBooks, $shelfLimit);
                            renderBookShelf("Fantasy & Adventure", $fantasyBooks, $shelfLimit);
                            renderBookShelf("Classics", $classicBooks, $shelfLimit);
                            renderBookShelf("Romance", $romanceBooks, $shelfLimit);
                        ?>
                    </div>

                    <div id="catalogue-table-view" style="display: none;">
                        <div class="results-table-container">
                            <table class="results-table">
                                <thead>
                                    <tr>
                                        <th>Cover</th>
                                        <th>Title</th>
                                        <th>Author(s)</th>
                                        <th>Genre(s)</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="catalogue-table-body">
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination">
                            <p>Page 1 of 10</p>
                        </div>
                    </div>
                    
                </section>
                </div>
        </main>
    </div>

    <div id="book-modal" class="modal-overlay">
        <div class="modal-content book-modal-content">
            </div>
    </div>
    <div id="see-all-modal" class="modal-overlay">
        <div class="modal-content see-all-modal-content">
            <div class="modal-header">
                <h2 id="see-all-modal-title">All Books</h2>
            </div>
            
            <div class="modal-body" id="see-all-modal-body">
                <p style="padding: 30px; text-align: center;">Loading...</p>
            </div>

            <div class="modal-footer">
                <button class="modal-close-btn" data-target="#see-all-modal">Close</button>
            </div>
        </div>
    </div>
    <script type="module" src="../js/pages/visitor.js"></script>
</body>
</html>