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
// (This function is unchanged from your file)
function handleBookCoverUpload($fileInputName, $existingCoverUrl = 'CoverBookTemp.png') {
    $uploadDir = __DIR__ . '/../../assets/covers/';
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$fileInputName];
        $newFileName = basename($file['name']);
        $targetPath = $uploadDir . $newFileName;
        $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.");
        }
        if ($file['size'] > 5000000) { // 5MB limit
            throw new Exception("File is too large. Max 5MB.");
        }
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $newFileName;
        } else {
            throw new Exception("Failed to move uploaded file. Check folder permissions.");
        }
    }
    return $existingCoverUrl;
}
// --- END HELPER FUNCTION ---

try {
    switch ($action) {

        // ===========================================
        // CATALOG MANAGEMENT
        // ===========================================
        // (All cases from 'getBooks' to 'deleteBookCopy' remain unchanged)
        // ... (getBooks) ...
        case 'getBooks':
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

        // ... (addBook) ...
        case 'addBook':
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

        // ... (getBookForEdit) ...
        case 'getBookForEdit':
            $bookId = $_GET['book_id'] ?? 0;
            if (empty($bookId)) throw new Exception("No book ID provided.");
            $bookData = $catalogueService->getBookForEdit($bookId); 
            if (!$bookData) throw new Exception("Book not found.");
            $response['success'] = true;
            $response['data'] = $bookData;
            break;

        // ... (updateBook) ...
        case 'updateBook':
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

        // ... (archiveBook) ...
        case 'archiveBook':
            $bookId = $_POST['book_id'] ?? 0;
            if (empty($bookId)) throw new Exception("No book ID provided.");
            $catalogueService->archiveBook($bookId); 
            $response['success'] = true;
            $response['message'] = 'Book (ID: ' . $bookId . ') has been archived.';
            break;

        // ... (getArchivedBooks) ...
        case 'getArchivedBooks':
            header('Content-Type: text/html');
            $searchTerm = $_GET['query'] ?? "";
            $books = $bookDAO->getArchivedBooks($searchTerm); 
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
            
        // ... (unarchiveBook) ...
        case 'unarchiveBook':
            $bookId = $_POST['book_id'] ?? 0;
            if (empty($bookId)) throw new Exception("No book ID provided.");
            $catalogueService->unarchiveBook($bookId); 
            $response['success'] = true;
            $response['message'] = 'Book (ID: ' . $bookId . ') has been restored.';
            break;

        // ... (getBookCopies) ...
        case 'getBookCopies':
            $bookId = $_GET['book_id'] ?? 0;
            if (empty($bookId)) throw new Exception("No book ID provided.");
            $copies = $bookCopyDAO->getCopiesForBook($bookId);
            $response['success'] = true;
            $response['data'] = $copies;
            break;

        // ... (addBookCopy) ...
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

        // ... (updateBookCopy) ...
        case 'updateBookCopy':
            $copyId = $_POST['copy_id'] ?? 0;
            $status = $_POST['status'] ?? 'Available';
            $condition = $_POST['condition'] ?? 'Good';
            $shelfLocation = $_POST['shelf_location'] ?? '';
            if (empty($copyId)) throw new Exception("Copy ID is required.");
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

        // ... (deleteBookCopy) ...
        case 'deleteBookCopy':
            $copyId = $_POST['copy_id'] ?? 0;
            if (empty($copyId)) throw new Exception("Copy ID is required.");
            $copy = $bookCopyDAO->getCopyById($copyId);
            if ($copy['status'] == 'Borrowed' || $copy['status'] == 'Overdue') {
                throw new Exception("Cannot delete a copy that is currently borrowed or overdue.");
            }
            $bookCopyDAO->deleteCopy($copyId);
            $response['success'] = true;
            $response['message'] = "Copy (ID: $copyId) deleted successfully.";
            break;

        // ===========================================
        // --- USER MANAGEMENT ACTIONS (NEW) ---
        // ===========================================
        
        case 'searchUsers':
            // (This case remains unchanged from your file)
            header('Content-Type: text/html');
            $searchTerm = $_GET['query'] ?? "";
            $accounts = $accountDAO->searchAccounts($searchTerm, 'Student');
            $html = '';
            if (empty($accounts)) {
                $html = '<tr><td colspan="5" style="text-align: center;">No students found.</td></tr>';
            } else {
                foreach ($accounts as $acc) {
                    $statusTag = $acc['is_active'] 
                        ? '<span class="status-tag tag-available">Active</span>'
                        : '<span class="status-tag tag-checkedout">Inactive</span>';
                    $html .= '
                        <tr>
                            <td>' . htmlspecialchars($acc['username']) . '</td>
                            <td>' . htmlspecialchars($acc['name']) . '</td>
                            <td>' . htmlspecialchars($acc['email']) . '</td>
                            <td>' . $statusTag . '</td>
                            <td>
                                <button class="action-btn view-details-btn" data-account-id="' . $acc['account_id'] . '">View Details</button>
                            </td>
                        </tr>
                    ';
                }
            }
            echo $html;
            exit;

        // --- NEW: Get all details for the student modal ---
        case 'getStudentDetails':
            $accountId = $_GET['account_id'] ?? 0;
            if (empty($accountId)) throw new Exception("No account ID provided.");

            $data = [
                'profile' => $accountDAO->getAccountById($accountId),
                'currentBorrows' => $transactionDAO->getActiveTransactionsForUser($accountId),
                'history' => $transactionDAO->getCompletedTransactionsForUser($accountId)
            ];
            
            $response['success'] = true;
            $response['data'] = $data;
            break;

        // --- NEW: Toggle a student's active status ---
        case 'toggleStudentStatus':
            $accountId = $_POST['account_id'] ?? 0;
            $isActive = $_POST['is_active'] ?? 0;
            
            if (empty($accountId)) throw new Exception("Account ID is required.");
            
            $accountService->toggleAccountStatus($accountId, (bool)$isActive);
            $response['success'] = true;
            $response['message'] = "Account status updated.";
            break;

        // --- NEW: Manually return a book ---
        case 'manuallyReturnBook':
            $transactionId = $_POST['transaction_id'] ?? 0;
            if (empty($transactionId)) throw new Exception("Transaction ID is required.");
            
            $result = $transactionService->returnBook($transactionId); 
            
            $response['success'] = true;
            $response['message'] = "Book returned. Fine: $" . number_format($result['fine_paid'], 2);
            break;

        // --- NEW: Waive a fine ---
        case 'waiveFine':
            $transactionId = $_POST['transaction_id'] ?? 0;
            if (empty($transactionId)) throw new Exception("Transaction ID is required.");
            
            $transactionDAO->setFine($transactionId, 0.00);
            
            $response['success'] = true;
            $response['message'] = "Fine waived for Transaction #$transactionId.";
            break;

        // --- NEW: Issue a manual fine ---
        case 'issueFine':
            $transactionId = $_POST['transaction_id'] ?? 0;
            $amount = $_POST['amount'] ?? 0;
            
            if (empty($transactionId) || empty($amount) || !is_numeric($amount)) {
                throw new Exception("Valid Transaction ID and amount are required.");
            }
            
            $transactionDAO->addFine($transactionId, (float)$amount);
            
            $response['success'] = true;
            $response['message'] = "Fine of $" . number_format($amount, 2) . " added to Transaction #$transactionId.";
            break;
            
        // ===========================================
        // CIRCULATION (BORROW & RETURN)
        // ===========================================
        // (All cases from 'findUser' to 'returnBook' remain unchanged)
        // ... (findUser) ...
        case 'findUser':
            $username = $_GET['query'] ?? '';
            $user = $accountDAO->getAccountByUsername($username);
            if (!$user || $user['role'] !== 'Student') throw new Exception("Student account not found.");
            $response['success'] = true;
            $response['account_id'] = $user['account_id'];
            $response['name'] = $user['name'];
            break;

        // ... (findCopy) ...
        case 'findCopy':
            $copyId = $_GET['copy_id'] ?? 0;
            $copy = $bookCopyDAO->getCopyById($copyId); 
            if ($copy['status'] !== 'Available') throw new Exception("Copy not available. Status: " . $copy['status']);
            $book = $catalogueService->getBookDetails($copy['book_id']);
            $response['success'] = true;
            $response['copy_id'] = $copy['copy_id'];
            $response['title'] = $book['title'];
            break;

        // ... (borrowBook) ...
        case 'borrowBook':
            $accountId = $_POST['account_id'] ?? 0;
            $copyId = $_POST['copy_id'] ?? 0;
            $transactionId = $transactionService->borrowBook($accountId, $copyId);
            $response['success'] = true;
            $response['message'] = "Book checked out. Transaction ID: $transactionId";
            break;

        // ... (findReturn) ...
        case 'findReturn':
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

        // ... (returnBook) ...
        case 'returnBook':
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