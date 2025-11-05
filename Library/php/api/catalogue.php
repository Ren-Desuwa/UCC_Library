<?php
// === These lines are required ===
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../services/CatalogueService.php';

// This line creates the service object
$catalogueService = new CatalogueService($conn);
// === End of required code ===


// This is your existing code, which is correct
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'searchBooks':
            $term = $_GET['term'] ?? '';
            $view = $_GET['view'] ?? 'list';
            $sort = $_GET['sort'] ?? 'title_asc';

            // This now returns 'available_copies'
            $books = $catalogueService->searchBooks($term, "", "", 50, 0);

            // Sorting block (unchanged)
            if (!empty($books)) {
                if ($sort === 'author_asc') {
                    usort($books, function($a, $b) {
                        return strcasecmp($a['author_names'] ?? 'zzzz', $b['author_names'] ?? 'zzzz');
                    });
                } else {
                    usort($books, function($a, $b) {
                        return strcasecmp($a['title'], $b['title']);
                    });
                }
            }
            
            // --- THIS WHOLE SECTION IS UPDATED TO FIX THE BUG ---
            if ($view === 'grid') {
                if (empty($books) && $term !== '') {
                } else {
                    foreach ($books as $book) {
                        // === NEW STATUS LOGIC ===
                        $isAvailable = $book['available_copies'] > 0;
                        $statusTag = $isAvailable ? 'tag-available' : 'tag-checkedout';
                        $statusText = $isAvailable ? 'Available' : 'Checked Out';
                        
                        echo '<div class="book-card open-book-modal-btn" data-book-id="' . $book['book_id'] . '">';
                        echo '  <div class="card-cover-wrapper">';
                        echo '    <img src="../assets/covers/' . htmlspecialchars($book['cover_url']) . '" alt="' . htmlspecialchars($book['title']) . '" class="book-cover-card">';
                        echo '  </div>';
                        echo '  <div class="card-details">';
                        echo '    <h3 class="card-title">' . htmlspecialchars($book['title']) . '</h3>';
                        echo '    <p class="card-author">' . htmlspecialchars($book['author_names'] ?? 'N/A') . '</p>';
                        // === This is now dynamic ===
                        echo '    <span class="status-tag ' . $statusTag . '">' . $statusText . '</span>';
                        echo '  </div>';
                        echo '</div>';
                    }
                }
            } else {
                if (empty($books) && $term !== '') {
                } else {
                    foreach ($books as $book) {
                        // === NEW STATUS LOGIC ===
                        $isAvailable = $book['available_copies'] > 0;
                        $statusTag = $isAvailable ? 'tag-available' : 'tag-checkedout';
                        $statusText = $isAvailable ? 'Available' : 'Checked Out';
                        
                        echo '<tr>';
                        echo '  <td class="cover-cell"><img src="../assets/covers/' . htmlspecialchars($book['cover_url']) . '" alt="' . htmlspecialchars($book['title']) . '" class="book-cover"></td>';
                        echo '  <td>' . htmlspecialchars($book['title']) . '</td>';
                        echo '  <td>' . htmlspecialchars($book['author_names'] ?? 'N/A') . '</td>';
                        echo '  <td>' . htmlspecialchars($book['genre_names'] ?? 'N/A') . '</td>';
                        // === This is now dynamic ===
                        echo '  <td><span class="status-tag ' . $statusTag . '">' . $statusText . '</span></td>';
                        echo '  <td><button class="action-btn open-book-modal-btn" data-book-id="' . $book['book_id'] . '">View</button></td>';
                        echo '</tr>';
                    }
                }
            }
            break;

        // ===============================================
        // === THIS SECTION IS UNCHANGED AND CORRECT ===
        // ===============================================
        case 'getBookDetails':
            if (!isset($catalogueService)) {
                $catalogueService = new CatalogueService($conn);
            }
            
            $bookId = $_GET['id'] ?? 0;
            if (empty($bookId)) {
                echo '<p>Invalid book ID.</p>';
                break;
            }

            try {
                $bookDetails = $catalogueService->getBookDetails($bookId);

                // Process Authors
                $authorNames = 'N/A';
                if (!empty($bookDetails['authors'])) {
                    $authorNames = implode(', ', array_map(function($author) {
                        return $author['name'];
                    }, $bookDetails['authors']));
                }

                // Process Genres
                $genreNames = 'N/A';
                if (!empty($bookDetails['genres'])) {
                    $genreNames = implode(', ', array_map(function($genre) {
                        return $genre['name'];
                    }, $bookDetails['genres']));
                }

                // Process Copies
                $availableCopies = 0;
                $totalCopies = 0;
                if (!empty($bookDetails['copies'])) {
                    $totalCopies = count($bookDetails['copies']);
                    foreach ($bookDetails['copies'] as $copy) {
                        if ($copy['status'] === 'Available') {
                            $availableCopies++;
                        }
                    }
                }
                
                $statusTag = $availableCopies > 0 ? 'tag-available' : 'tag-checkedout';
                $statusText = $availableCopies > 0 ? 'Available' : 'Checked Out';
                $copyText = "($availableCopies of $totalCopies copies available)";

                // --- Start of Modal HTML ---
                echo '
                <div class="modal-header">
                    <h2>' . htmlspecialchars($bookDetails['title']) . '</h2>
                </div>
                <div class="modal-body book-detail-modal">
                    <img src="../assets/covers/' . htmlspecialchars($bookDetails['cover_url']) . '" alt="' . htmlspecialchars($bookDetails['title']) . '" class="book-detail-cover">
                    <div class="book-detail-info">
                        <p><strong>Author:</strong> ' . htmlspecialchars($authorNames) . '</p>
                        <p><strong>Genre:</strong> ' . htmlspecialchars($genreNames) . '</p>
                        <p><strong>ISBN:</strong> ' . htmlspecialchars($bookDetails['isbn'] ?? 'N/A') . '</p>
                        <p><strong>Year:</strong> ' . htmlspecialchars($bookDetails['year_published'] ?? 'N/A') . '</p>
                        
                        <h3>Summary</h3>
                        <p>' . nl2br(htmlspecialchars($bookDetails['description'] ?? 'No summary available.')) . '</p>
                        
                        <div class="book-detail-status">
                            <strong>Status:</strong> 
                            <span class="status-tag ' . $statusTag . '">' . $statusText . '</span> 
                            <span>' . $copyText . '</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-close-btn">Close</button>
                    <a href="login.php" class="action-btn primary-btn">Sign in to Borrow</a>
                </div>
                ';
                // --- End of Modal HTML ---

            } catch (Exception $e) {
                // This catches the "Book not found" exception from your service
                echo '<div class="modal-header"><h2>Error</h2></div>
                      <div class="modal-body"><p>Book details not found.</p></div>
                      <div class="modal-footer"><button type="button" class="modal-close-btn">Close</button></div>';
            }
            break;
    }
}
?>