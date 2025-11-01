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

$action = $_GET['action'] ?? null;
$responseHTML = '';

try {
    $catalogueService = new CatalogueService($conn);

    switch ($action) {
        /**
         * --- GET BOOK DETAILS ---
         * Fetches all details for a book and formats it as HTML
         * for the visitor modal, matching the styles in visitor.css.
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
            // Combine author names into a string
            $authors = implode(", ", array_map(fn($a) => htmlspecialchars($a['name']), $book['authors']));
            // Combine genre names into a string
            $genres = implode(", ", array_map(fn($g) => htmlspecialchars($g['name']), $book['genres']));
            
            // Calculate available copies
            $availableCopies = 0;
            foreach ($book['copies'] as $copy) {
                if ($copy['status'] == 'Available') {
                    $availableCopies++;
                }
            }
            $statusText = $availableCopies > 0 ? "Available" : "Checked Out";
            $statusColor = $availableCopies > 0 ? "#059669" : "#DC2626"; // Green or Red

            // --- Generate HTML response based on visitor.css ---
            // This structure matches the .book-details-grid styles
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
         * --- SEARCH BOOKS (NEW) ---
         * Performs a search and returns only the HTML <tr> rows
         * for the catalogue table body.
         * Expects: $_GET['term']
         */
        case 'searchBooks':
            $searchTerm = $_GET['term'] ?? "";
            
            // Use the existing service method
            $books = $catalogueService->searchBooks($searchTerm, 20, 0); 
            
            if (empty($books)) {
                // UPDATED: Colspan is now 6
                $responseHTML = '<tr><td colspan="6" style="text-align: center;">No books found in the catalogue.</td></tr>';
            } else {
                foreach ($books as $book) {
                    $responseHTML .= '
                        <tr>
                            <td class="cover-cell">
                                <img src="../assets/covers/' . htmlspecialchars($book['cover_url']) . '" alt="' . htmlspecialchars($book['title']) . '" class="book-cover">
                            </td>
                            <td>' . htmlspecialchars($book['title']) . '</td>
                            
                            <td>' . htmlspecialchars($book['author_names'] ?? 'N/A') . '</td>
                            <td>' . htmlspecialchars($book['genre_names'] ?? 'N/A') . '</td>
                            
                            <td><span class="status-tag tag-available">Available</span></td>
                            
                            <td><button class="action-btn open-book-modal-btn" data-book-id="' . $book['book_id'] . '">View</button></td>
                        </tr>
                    ';
                }
            }
            break;
            
        default:
            throw new Exception("Invalid catalogue action.");
    }
} catch (Exception $e) {
    // Send an error message HTML if something goes wrong
    // UPDATED: Colspan is now 6
    $responseHTML = '
        <tr><td colspan="6" style="text-align: center; color: red;">
            An error occurred: ' . htmlspecialchars($e->getMessage()) . '
        </td></tr>
    ';
    
    // For modal action, provide the full modal error
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