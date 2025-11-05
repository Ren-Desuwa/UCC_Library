<?php
/**
 * API Endpoint: catalogue.php
 *
 * Handles public-facing catalogue actions, such as fetching
 * book details for the visitor modal and performing searches.
 */

session_start();
// We are sending back HTML, not JSON
header('Content-Type: text/html'); 

// Require the necessary database connection and service
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../services/CatalogueService.php';

// Use $_REQUEST to handle both GET (for book details) and POST (for search)
$action = $_REQUEST['action'] ?? null;
$responseHTML = '';

try {
    $catalogueService = new CatalogueService($conn);

    switch ($action) {
        /**
         * --- GET BOOK DETAILS ---
         * Expects: $_GET['id']
         */
        case 'getBookDetails':
            $bookId = $_GET['id'] ?? 0;
            if (empty($bookId)) {
                throw new Exception("No book ID provided.");
            }

            // Get all book data (title, authors, genres, copies)
            $book = $catalogueService->getBookDetails($bookId);
            
            // --- Helper data ---
            $authors = implode(", ", array_map(fn($a) => htmlspecialchars($a['name']), $book['authors']));
            $genres = implode(", ", array_map(fn($g) => htmlspecialchars($g['name']), $book['genres']));
            
            $availableCopies = 0;
            foreach ($book['copies'] as $copy) {
                if ($copy['status'] == 'Available') {
                    $availableCopies++;
                }
            }
            $statusText = $availableCopies > 0 ? "Available" : "Checked Out";
            $statusColor = $availableCopies > 0 ? "#059669" : "#DC2626"; // Green or Red

            // --- Generate HTML response based on visitor.css ---
            $responseHTML .= '
                <div class="book-details-grid">
                    <div class="book-cover-area">
                        <img src="../assets/covers/' . htmlspecialchars($book['cover_url']) . '" alt="' . htmlspecialchars($book['title']) . '" class="book-detail-cover">
                    </div>
                    <div class="book-info-area">
                        <h2 class="book-detail-title">' . htmlspecialchars($book['title']) . '</h2>
                        <dl class="book-meta-list">
                            <dt>Author:</dt>
                            <dd>' . ($authors ?: 'N/A') . '</dd>
                            
                            <dt>Genre(s):</dt>
                            <dd>' . ($genres ?: 'N/A') . '</dd>
                            
                            <dt>Publisher:</dt>
                            <dd>' . htmlspecialchars($book['publisher'] ?: 'N/A') . '</dd>
                            
                            <dt>Year:</dt>
                            <dd>' . htmlspecialchars($book['year_published'] ?: 'N/A') . '</dd>
                            
                            <dt>ISBN:</dt>
                            <dd>' . htmlspecialchars($book['isbn']) . '</dd>
                        </dl>
                    </div>
                    <div class="plot-summary-area">
                        <h3 class="plot-summary-title">Summary</h3>
                        <p class="plot-summary-text">' . nl2br(htmlspecialchars($book['description'] ?: 'No summary available.')) . '</p>
                    </div>
                </div>
            ';
            
            // --- Modal Footer (matches visitor.css) ---
            $responseHTML .= '
                <div class="modal-footer book-modal-footer">
                    <div class="book-status-info">
                        <span class="status-indicator" style="color: ' . $statusColor . ';">●</span>
                        Status: <strong>' . $statusText . '</strong>
                        <span class="copies-available">(' . $availableCopies . ' ' . ($availableCopies == 1 ? "copy" : "copies") . ' available)</span>
                    </div>
                    <div class="book-actions">
                        <button type="button" class="modal-close-btn">Close</button>
                        <a href="login.php" class="action-btn sign-in-btn">Sign In to Borrow</a>
                    </div>
                </div>
            ';
            break;

        /**
         * --- SEARCH BOOKS (FIXED) ---
         * This handles searches from visitor.js and student.js
         * Expects: $_POST['term'], $_POST['author'], $_POST['genre']
         */
        case 'searchBooks':
            // Read from POST
            $searchTerm = $_POST['term'] ?? ($_GET['term'] ?? ""); // Keep backward compatibility
            $author = $_POST['author'] ?? "";
            $genre = $_POST['genre'] ?? "";
            $year_from = $_POST['year_from'] ?? ""; 
            $year_to = $_POST['year_to'] ?? "";
            $status = $_POST['status'] ?? ""; // New field
            
            // Use the updated service method
            $books = $catalogueService->searchBooks($searchTerm, $author, $genre, $year_from, $year_to, $status, 20, 0); 
            
            if (empty($books)) {
                $responseHTML = '<tr><td colspan="6" style="text-align: center;">No books found matching your criteria.</td></tr>';
            } else {
                foreach ($books as $book) {
                    // We can now show the *actual* availability
                    $statusTag = '<span class="status-tag tag-checkedout">Unavailable</span>';
                    if (isset($book['available_copies_count']) && $book['available_copies_count'] > 0) {
                        $statusTag = '<span class="status-tag tag-available">Available</span>';
                    }

                    $responseHTML .= '
                        <tr>
                            <td class="cover-cell">
                                <img src="../assets/covers/' . htmlspecialchars($book['cover_url']) . '" alt="' . htmlspecialchars($book['title']) . '" class="book-cover">
                            </td>
                            <td>' . htmlspecialchars($book['title']) . '</td>
                            <td>' . htmlspecialchars($book['author_names'] ?? 'N/A') . '</td>
                            <td>' . htmlspecialchars($book['genre_names'] ?? 'N/A') . '</td>
                            <td>' . $statusTag . '</td>
                            <td><button class="action-btn open-book-modal-btn" data-book-id="' . $book['book_id'] . '">View</button></td>
                        </tr>
                    ';
                }
            }
            break;
        case 'getGenreShelf':
            $genreTitle = $_GET['genre'] ?? 'Recommended';
            $searchGenre = '';

            // Map the display title to the search term
            if ($genreTitle == 'Thrillers & Mystery') $searchGenre = 'Thriller';
            if ($genreTitle == 'Fantasy & Adventure') $searchGenre = 'Fantasy';
            if ($genreTitle == 'Classics') $searchGenre = 'Classic';
            if ($genreTitle == 'Romance') $searchGenre = 'Romance';
            // "Recommended" stays as ''

            // Fetch up to 50 books for this genre
            $books = $catalogueService->searchBooks("", "", $searchGenre, "", "", "", 50, 0); 
            
            if (empty($books)) {
                $responseHTML = '<p style="text-align: center; padding: 20px;">No books found for this category.</p>';
            } else {
                // Use the student card style by default, visitor style is compatible
                $cardClass = 'book-card-student'; 
                $authorClass = 'book-card-author-student';

                // Check if this is a visitor or student
                if (!isset($_SESSION['role'])) { // Visitor
                    $cardClass = 'book-card-visitor';
                    $authorClass = 'book-card-author-visitor';
                }

                $responseHTML = '<div class="see-all-grid">';
                foreach ($books as $book) {
                    $responseHTML .= '
                        <a href="#" class="' . $cardClass . ' open-book-modal-btn" data-book-id="' . htmlspecialchars($book['book_id']) . '">
                            <img src="../assets/covers/' . htmlspecialchars($book['cover_url']) . '" alt="' . htmlspecialchars($book['title']) . '">
                            <h3>' . htmlspecialchars($book['title']) . '</h3>
                            <p class="' . $authorClass . '">' . htmlspecialchars($book['author_names'] ?? 'N/A') . '</p>
                        </a>
                    ';
                }
                $responseHTML .= '</div>';
            }
            break;
            
        default:
            throw new Exception("Invalid catalogue action.");
    }
} catch (Exception $e) {
    // Send an error message HTML
    $responseHTML = '
        <tr><td colspan="6" style="text-align: center; color: red;">
            An error occurred: ' . htmlspecialchars($e->getMessage()) . '
        </td></tr>
    ';
    
    if ($action == 'getBookDetails') {
        $responseHTML = '
            <div class="modal-header"><h2>Error</h2></div>
            <div class="modal-body">
                <p>Could not load book details: ' . htmlspecialchars($e->getMessage()) . '</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-close-btn">Close</button>
            </div>
        ';
    }
}

// Send the final HTML back to visitor.js
echo $responseHTML;

?>