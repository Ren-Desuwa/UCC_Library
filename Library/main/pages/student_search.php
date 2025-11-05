<?php
    // We need the DB connection and the CatalogueService
    require_once __DIR__ . '/../../php/db_connect.php';
    require_once __DIR__ . '/../../php/services/CatalogueService.php';
    
    $catalogueService = new CatalogueService($conn);
    
    // FIXED: Updated all calls to match the new function signature
    $recommendedBooks = $catalogueService->searchBooks("", "", "", 10, 0); 
    $thrillerBooks = $catalogueService->searchBooks("", "", "Thriller", 10, 0);
    $fantasyBooks = $catalogueService->searchBooks("", "", "Fantasy", 10, 0);

    /**
     * Helper function to render a single "book shelf" carousel.
     */
    function renderBookShelf($title, $books) {
        if (empty($books)) return; // Don't show empty shelves

        echo '<section class="book-shelf-student">';
        echo '<div class="shelf-header-student">';
        echo "<h2>{$title}</h2>";
        echo '<a href="#" class="see-all-link-student">See All <span class="material-icons-round">arrow_forward_ios</span></a>';
        echo '</div>';
        echo '<div class="shelf-carousel-student">';
        
        foreach ($books as $book) {
            // Use 'open-book-modal-btn' class for JS hook
            echo '<a href="#" class="book-card-student open-book-modal-btn" data-book-id="' . htmlspecialchars($book['book_id']) . '">';
            echo '<img src="../assets/covers/' . htmlspecialchars($book['cover_url']) . '" alt="' . htmlspecialchars($book['title']) . '">';
            echo '<h3>' . htmlspecialchars($book['title']) . '</h3>';
            echo '<p class="book-card-author-student">' . htmlspecialchars($book['author_names'] ?? 'N/A') . '</p>';
            echo '</a>';
        }
        
        echo '</div>'; // end shelf-carousel
        echo '</section>'; // end book-shelf
    }
?>

<div id="search-books-content" class="content-panel">
    <header class="main-header">
        <h1>Library Catalogue</h1>
        <p>Browse our collection or search for something specific.</p>
    </header>

    <section class="search-panel">
        
        <div class="search-controls-advanced">
            <div class="search-bar-advanced" id="search-bar-advanced">
                <span class="material-icons-round search-icon">search</span>
                <input type="text" id="student-search-input" placeholder="Search by Title, Author, or Genre...">
            </div>
            <div class="filter-container">
                <button class="filter-btn" id="filter-btn">
                    <span class="material-icons-round">filter_list</span>
                    Filter
                </button>
                <div class="filter-dropdown" id="filter-dropdown">
                    <a href="#" data-filter-type="author">Author</a>
                    <a href="#" data-filter-type="genre">Genre</a>
                </div>
            </div>
        </div>

        <div id="catalogue-grid-view">
            <?php
                renderBookShelf("Recommended", $recommendedBooks);
                renderBookShelf("Thrillers & Mystery", $thrillerBooks);
                renderBookShelf("Fantasy & Adventure", $fantasyBooks);
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
                    <tbody id="student-search-table-body">
                        </tbody>
                </table>
            </div>
            <div class="pagination">
                <p>Page 1 of 10</p>
            </div>
        </div>
        
    </section>
</div>