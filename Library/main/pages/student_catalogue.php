<?php
    // We need the DB connection and the CatalogueService
    require_once __DIR__ . '/../../php/db_connect.php';
    require_once __DIR__ . '/../../php/services/CatalogueService.php';
    
    $catalogueService = new CatalogueService($conn);
    
    // UPDATED: Fetch 11 books (one more than the display limit)
    $shelfLimit = 10;
    $fetchLimit = $shelfLimit + 1;
    
    $recommendedBooks = $catalogueService->searchBooks("", "", "", "", "", "", $fetchLimit, 0); 
    $thrillerBooks = $catalogueService->searchBooks("", "", "Thriller", "", "", "", $fetchLimit, 0);
    $fantasyBooks = $catalogueService->searchBooks("", "", "Fantasy", "", "", "", $fetchLimit, 0);

    /**
     * Helper function to render a single "book shelf" carousel.
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

        echo '<section class="book-shelf-student">';
        echo '<div class="shelf-header-student">';
        echo "<h2>{$title}</h2>";
        
        // UPDATED: Only show the link if $hasMoreBooks is true
        if ($hasMoreBooks) {
             // ADDED: class="open-see-all-modal-btn" and data-genre attribute
            echo '<a href="#" class="see-all-link-student open-see-all-modal-btn" data-genre="' . htmlspecialchars($title) . '">See All <span class="material-icons-round">arrow_forward_ios</span></a>';
        }

        echo '</div>';
        echo '<div class="shelf-carousel-student">';
        
        foreach ($books as $book) {
            echo '<a href="#" class="book-card-student open-book-modal-btn" data-book-id="' . htmlspecialchars($book['book_id']) . '">';
            echo '<img src="../assets/covers/' . htmlspecialchars($book['cover_url']) . '" alt="' . htmlspecialchars($book['title']) . '">';
            echo '<h3>' . htmlspecialchars($book['title']) . '</h3>';
            echo '<p class="book-card-author-student">' . htmlspecialchars($book['author_names'] ?? 'N/A') . '</p>';
            echo '</a>';
        }
        
        echo '</div>'; 
        echo '</section>';
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
                <input type="text" id="student-search-input" class="search-input-flex" placeholder="Search... e.g., author:Rowling year:2012-2015 Harry Potter">
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