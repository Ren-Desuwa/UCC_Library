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
require_once __DIR__ . '/../models/BookDAO.php'; 

// --- Service & DAO Initialization ---
$catalogueService = new CatalogueService($conn);
$transactionService = new BookTransactionService($conn);
$accountService = new AccountManagementService($conn);
$accountDAO = new AccountDAO($conn);
$bookCopyDAO = new BookCopyDAO($conn);
$transactionDAO = new TransactionDAO($conn);
$bookDAO = new BookDAO($conn); 

$response = ['success' => false, 'message' => 'Invalid action.'];
$action = $_REQUEST['action'] ?? null;


// --- MODIFIED HELPER FUNCTION FOR FILE UPLOAD ---
// Now uses the original filename instead of sanitizing the title
function handleBookCoverUpload($fileInputName, $existingCoverUrl = 'CoverBookTemp.png') {
    $uploadDir = __DIR__ . '/../../assets/covers/';
    
    // Check if a file was uploaded
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$fileInputName];
        
        // --- KEY CHANGE: Use the original filename ---
        $newFileName = basename($file['name']);
        $targetPath = $uploadDir . $newFileName;
        $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));

        // Basic validation
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.");
        }
        if ($file['size'] > 5000000) { // 5MB limit
            throw new Exception("File is too large. Max 5MB.");
        }

        // Move the file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $newFileName; // Return the ORIGINAL filename
        } else {
            throw new Exception("Failed to move uploaded file. Check folder permissions.");
        }
    }
    
    // If no new file, return the existing one
    return $existingCoverUrl;
}
// --- END HELPER FUNCTION ---

try {
    switch ($action) {

        // ===========================================
        // CATALOG MANAGEMENT
        // ===========================================

        case 'getBooks':
            // (This case remains unchanged)
            header('Content-Type: text/html');
            $books = $catalogueService->searchBooks("", "", "", "", "", "", 100, 0); 
            $html = '';
            if (empty($books)) {
                $html = '<tr><td colspan="5" style="text-align: center;">No books found.</td></tr>';
            } else {
                foreach ($books as $book) {
                    $availability = $book['available_copies_count'] > 0 ? $book['available_copies_count'] . ' Available' : 'All Checked Out';
                    $copies = $bookCopyDAO->getCopiesForBook($book['book_id']);
                    $totalCopies = count($copies);
                    $html .= '
                        <tr>
                            <td class="cover-cell"><img src="../assets/covers/' . htmlspecialchars($book['cover_url']) . '" alt="' . htmlspecialchars($book['title']) . '"></td>
                            <td>
                                <strong>' . htmlspecialchars($book['title']) . '</strong><br>
                                <span style="font-size: 0.9rem; color: #666;"> Author(s): ' . htmlspecialchars($book['author_names'] ?? 'N/A') . '</span>
                            </td>
                            <td>' . $totalCopies . '</td>
                            <td>' . $availability . '</td>
                            <td>
                                <button class="action-btn edit-action-btn" data-book-id="' . $book['book_id'] . '">Edit</button>
                                <button class="action-btn archive-action-btn" data-book-id="' . $book['book_id'] . '">Archive</button>
                            </td>
                        </tr>
                    ';
                }
            }
            echo $html; 
            exit;

        case 'addBook':
            // (This case remains unchanged)
            $title = $_POST['title'] ?? '';
            $isbn = $_POST['isbn'] ?? '';
            $publisher = $_POST['publisher'] ?? '';
            $year = !empty($_POST['year']) ? (int)$_POST['year'] : null;
            $desc = $_POST['description'] ?? '';
            $coverUrl = handleBookCoverUpload('cover_image', 'CoverBookTemp.png');
            $authorNamesString = $_POST['authors'] ?? '';
            $genreNamesString = $_POST['genres'] ?? '';
            $bookId = $catalogueService->addBook(
                $title, $isbn, $publisher, $year, $desc, $coverUrl,
                $authorNamesString, $genreNamesString
            );
            $response['success'] = true;
            $response['message'] = "Book added successfully (ID: $bookId).";
            break;

        case 'getBookForEdit':
            // (This case remains unchanged)
            $bookId = $_GET['book_id'] ?? 0;
            if (empty($bookId)) throw new Exception("No book ID provided.");
            $bookData = $catalogueService->getBookForEdit($bookId); 
            if (!$bookData) throw new Exception("Book not found.");
            $response['success'] = true;
            $response['data'] = $bookData;
            break;

        case 'updateBook':
            // (This case remains unchanged)
            $bookId = $_POST['book_id'] ?? 0;
            if (empty($bookId)) throw new Exception("No book ID provided for update.");
            $title = $_POST['title'] ?? '';
            $isbn = $_POST['isbn'] ?? '';
            $publisher = $_POST['publisher'] ?? '';
            $year = !empty($_POST['year']) ? (int)$_POST['year'] : null;
            $desc = $_POST['description'] ?? '';
            $authorNamesString = $_POST['authors'] ?? '';
            $genreNamesString = $_POST['genres'] ?? '';
            $existingBook = $catalogueService->getBookForEdit($bookId);
            $existingCover = $existingBook['cover_url'] ?? 'CoverBookTemp.png';
            $coverUrl = handleBookCoverUpload('cover_image', $existingCover);
            $catalogueService->updateBook(
                $bookId, $title, $isbn, $publisher, $year, $desc, $coverUrl,
                $authorNamesString, $genreNamesString
            );
            $response['success'] = true;
            $response['message'] = "Book (ID: $bookId) updated successfully.";
            break;

        case 'archiveBook':
            // (This case remains unchanged)
            $bookId = $_POST['book_id'] ?? 0;
            if (empty($bookId)) throw new Exception("No book ID provided.");
            $catalogueService->archiveBook($bookId); 
            $response['success'] = true;
            $response['message'] = 'Book (ID: ' . $bookId . ') has been archived.';
            break;

        case 'getArchivedBooks':
            // (This case remains unchanged)
            header('Content-Type: text/html');
            $books = $bookDAO->getArchivedBooks(); 
            $html = '';
            if (empty($books)) {
                $html = '<tr><td colspan="4" style="text-align: center;">No archived books found.</td></tr>';
            } else {
                foreach ($books as $book) {
                    $html .= '
                        <tr>
                            <td class="cover-cell"><img src="../assets/covers/' . htmlspecialchars($book['cover_url']) . '" alt="' . htmlspecialchars($book['title']) . '"></td>
                            <td>
                                <strong>' . htmlspecialchars($book['title']) . '</strong><br>
                                <span style="font-size: 0.9rem; color: #666;">' . htmlspecialchars($book['author_names'] ?? 'N/A') . '</span>
                            </td>
                            <td>' . htmlspecialchars($book['isbn']) . '</td>
                            <td>
                                <button class="action-btn restore-action-btn" style="background-color: #10B981;" data-book-id="' . $book['book_id'] . '">Restore</button>
                            </td>
                        </tr>
                    ';
                }
            }
            echo $html;
            exit;
            
        case 'unarchiveBook':
            // (This case remains unchanged)
            $bookId = $_POST['book_id'] ?? 0;
            if (empty($bookId)) throw new Exception("No book ID provided.");
            $catalogueService->unarchiveBook($bookId); 
            $response['success'] = true;
            $response['message'] = 'Book (ID: ' . $bookId . ') has been restored.';
            break;

        // ===========================================
        // --- NEW BOOK COPY ACTIONS ---
        // ===========================================

        case 'getBookCopies':
            $bookId = $_GET['book_id'] ?? 0;
            if (empty($bookId)) throw new Exception("No book ID provided.");
            $copies = $bookCopyDAO->getCopiesForBook($bookId);
            $response['success'] = true;
            $response['data'] = $copies;
            break;

        case 'addBookCopy':
            $bookId = $_POST['book_id'] ?? 0;
            $condition = $_POST['condition'] ?? 'Good';
            $shelfLocation = $_POST['shelf_location'] ?? '';
            if (empty($bookId) || empty($shelfLocation)) {
                throw new Exception("Book ID and shelf location are required.");
            }
            $newCopyId = $bookCopyDAO->createCopy($bookId, $condition, $shelfLocation);
            $response['success'] = true;
            $response['message'] = "New copy (ID: $newCopyId) added successfully.";
            break;

        case 'updateBookCopy':
            $copyId = $_POST['copy_id'] ?? 0;
            $status = $_POST['status'] ?? 'Available';
            $condition = $_POST['condition'] ?? 'Good';
            $shelfLocation = $_POST['shelf_location'] ?? '';
            if (empty($copyId)) throw new Exception("Copy ID is required.");
            
            // Prevent changing status of a borrowed book
            $copy = $bookCopyDAO->getCopyById($copyId);
            if ($copy['status'] == 'Borrowed' || $copy['status'] == 'Overdue') {
                if ($status != $copy['status']) {
                    throw new Exception("Cannot change status of a borrowed or overdue book.");
                }
            }
            
            $bookCopyDAO->updateCopyDetails($copyId, $status, $condition, $shelfLocation);
            $response['success'] = true;
            $response['message'] = "Copy (ID: $copyId) updated successfully.";
            break;

        case 'deleteBookCopy':
            $copyId = $_POST['copy_id'] ?? 0;
            if (empty($copyId)) throw new Exception("Copy ID is required.");
            
            // Check if copy is currently borrowed
            $copy = $bookCopyDAO->getCopyById($copyId);
            if ($copy['status'] == 'Borrowed' || $copy['status'] == 'Overdue') {
                throw new Exception("Cannot delete a copy that is currently borrowed or overdue.");
            }

            // The DAO will throw an error if it's linked to history,
            // which will be caught by the main catch block.
            $bookCopyDAO->deleteCopy($copyId);
            $response['success'] = true;
            $response['message'] = "Copy (ID: $copyId) deleted successfully.";
            break;
            
        // ===========================================
        // CIRCULATION (BORROW & RETURN)
        // ===========================================

        case 'findUser':
            // (This case remains unchanged)
            $username = $_GET['query'] ?? '';
            $user = $accountDAO->getAccountByUsername($username);
            if (!$user || $user['role'] !== 'Student') throw new Exception("Student account not found.");
            $response['success'] = true;
            $response['account_id'] = $user['account_id'];
            $response['name'] = $user['name'];
            break;

        case 'findCopy':
            // (This case remains unchanged)
            $copyId = $_GET['copy_id'] ?? 0;
            $copy = $bookCopyDAO->getCopyById($copyId); 
            if ($copy['status'] !== 'Available') throw new Exception("Copy not available. Status: " . $copy['status']);
            $book = $catalogueService->getBookDetails($copy['book_id']);
            $response['success'] = true;
            $response['copy_id'] = $copy['copy_id'];
            $response['title'] = $book['title'];
            break;

        case 'borrowBook':
            // (This case remains unchanged)
            $accountId = $_POST['account_id'] ?? 0;
            $copyId = $_POST['copy_id'] ?? 0;
            $transactionId = $transactionService->borrowBook($accountId, $copyId);
            $response['success'] = true;
            $response['message'] = "Book checked out. Transaction ID: $transactionId";
            break;

        case 'findReturn':
            // (This case remains unchanged)
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
            if (!$transaction) throw new Exception("No active transaction found for this copy.");
            $response['success'] = true;
            $response['transaction'] = $transaction;
            break;

        case 'returnBook':
            // (This case remains unchanged)
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