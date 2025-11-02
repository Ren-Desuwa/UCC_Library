<?php
/**
 * API Endpoint: librarian.php
 *
 * Handles all authenticated librarian-level actions, including
 * catalog management, user lookups, and circulation.
 */

session_start();
header('Content-Type: application/json'); // Default response type

// --- Security Check: Ensure user is a Librarian ---
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'Librarian') {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Error: Not authorized.']);
    exit;
}

// --- Includes ---
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../services/CatalogueService.php';
require_once __DIR__ . '/../services/BookTransactionService.php';
require_once __DIR__ . '/../services/AccountManagementService.php';
require_once __DIR__ . '/../models/AccountDAO.php';
require_once __DIR__ . '/../models/BookCopyDAO.php';
require_once __DIR__ . '/../models/TransactionDAO.php';

// --- Service & DAO Initialization ---
$catalogueService = new CatalogueService($conn);
$transactionService = new BookTransactionService($conn);
$accountService = new AccountManagementService($conn);
$accountDAO = new AccountDAO($conn);
$bookCopyDAO = new BookCopyDAO($conn);
$transactionDAO = new TransactionDAO($conn);

$response = ['success' => false, 'message' => 'Invalid action.'];
$action = $_REQUEST['action'] ?? null;

try {
    switch ($action) {

        // ===========================================
        // CATALOG MANAGEMENT
        // ===========================================

        case 'getBooks':
            // Fetches all books for the main catalog table
            // This action returns HTML, not JSON
            header('Content-Type: text/html');
            $books = $catalogueService->searchBooks("", "", "", "", "", "", 100, 0); // Get up to 100 books
            
            $html = '';
            if (empty($books)) {
                $html = '<tr><td colspan="5" style="text-align: center;">No books found.</td></tr>';
            } else {
                foreach ($books as $book) {
                    $availability = $book['available_copies_count'] > 0 ? $book['available_copies_count'] . ' Available' : 'All Checked Out';
                    // We need a proper count of total copies, not just available
                    $copies = $bookCopyDAO->getCopiesForBook($book['book_id']);
                    $totalCopies = count($copies);
                    
                    $html .= '
                        <tr>
                            <td class="cover-cell"><img src="../assets/covers/' . htmlspecialchars($book['cover_url']) . '" alt="' . htmlspecialchars($book['title']) . '"></td>
                            <td>
                                Tite: <strong>' . htmlspecialchars($book['title']) . '</strong><br>
                                <span style="font-size: 0.9rem; color: #666;"> Author(s): ' . htmlspecialchars($book['author_names'] ?? 'N/A') . '</span>
                            </td>
                            <td>' . $totalCopies . '</td>
                            <td>' . $availability . '</td>
                            <td>
                                <button class="action-btn edit-action-btn" data-book-id="' . $book['book_id'] . '">Edit</button>
                                <button class="action-btn delete-action-btn" data-book-id="' . $book['book_id'] . '">Delete</button>
                            </td>
                        </tr>
                    ';
                }
            }
            echo $html; 
            exit; // Exit before the JSON encode

        case 'addBook':
            // UPDATED: This now handles author/genre strings
            $title = $_POST['title'] ?? '';
            $isbn = $_POST['isbn'] ?? '';
            $publisher = $_POST['publisher'] ?? '';
            $year = !empty($_POST['year']) ? (int)$_POST['year'] : null;
            $desc = $_POST['description'] ?? '';
            $coverUrl = 'CoverBookTemp.png'; // Default
            
            // Get the raw comma-separated strings
            $authorNamesString = $_POST['authors'] ?? '';
            $genreNamesString = $_POST['genres'] ?? '';
            
            // The service will handle the logic of parsing, finding, or creating
            $bookId = $catalogueService->addBook(
                $title, $isbn, $publisher, $year, $desc, $coverUrl,
                $authorNamesString, $genreNamesString
            );

            $response['success'] = true;
            $response['message'] = "Book added successfully (ID: $bookId).";
            break;

        // ===========================================
        // CIRCULATION (BORROW)
        // ===========================================

        case 'findUser':
            // Finds a student by username for the borrow form
            $username = $_GET['query'] ?? '';
            $user = $accountDAO->getAccountByUsername($username);
            if (!$user || $user['role'] !== 'Student') {
                throw new Exception("Student account not found or is not a student.");
            }
            $response['success'] = true;
            $response['account_id'] = $user['account_id'];
            $response['name'] = $user['name'];
            break;

        case 'findCopy':
            // Finds an available book copy for the borrow form
            $copyId = $_GET['copy_id'] ?? 0;
            $copy = $bookCopyDAO->getCopyById($copyId); 
            if ($copy['status'] !== 'Available') {
                throw new Exception("Copy is not available. Status: " . $copy['status']);
            }
            
            $book = $catalogueService->getBookDetails($copy['book_id']);
            $response['success'] = true;
            $response['copy_id'] = $copy['copy_id'];
            $response['title'] = $book['title'];
            break;

        case 'borrowBook':
            // Processes the checkout
            $accountId = $_POST['account_id'] ?? 0;
            $copyId = $_POST['copy_id'] ?? 0;
            
            $transactionId = $transactionService->borrowBook($accountId, $copyId);
            
            $response['success'] = true;
            $response['message'] = "Book checked out successfully. Transaction ID: $transactionId";
            break;

        // ===========================================
        // CIRCULATION (RETURN)
        // ===========================================

        case 'findReturn':
            // Finds an active transaction by copy ID for the return form
            $copyId = $_GET['copy_id'] ?? 0;
            $sql = "SELECT t.*, a.name as user_name, b.title, b.cover_url 
                    FROM transactions t
                    JOIN accounts a ON t.account_id = a.account_id
                    JOIN book_copies c ON t.copy_id = c.copy_id
                    JOIN books b ON c.book_id = b.book_id
                    WHERE t.copy_id = ? AND t.status IN ('Active', 'Overdue')
                    ORDER BY t.date_borrowed DESC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $copyId);
            $stmt->execute();
            $transaction = $stmt->get_result()->fetch_assoc();
            
            if (!$transaction) {
                throw new Exception("No active transaction found for this book copy.");
            }
            
            $response['success'] = true;
            $response['transaction'] = $transaction;
            break;

        case 'returnBook':
            // Processes the return
            $transactionId = $_POST['transaction_id'] ?? 0;
            
            $result = $transactionService->returnBook($transactionId); 
            
            $response['success'] = true;
            $response['message'] = "Book returned. Fine: $" . number_format($result['fine_paid'], 2);
            break;

        default:
            throw new Exception("Invalid librarian action specified.");
    }
} catch (Exception $e) {
    // Catch any errors and send them back as JSON
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>