<?php
header('Content-Type: application/json');
session_start();

// --- Global Includes ---
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../services/StudentService.php';
// We also need DAOs that the service depends on
require_once __DIR__ . '/../models/BookDAO.php';
require_once __DIR__ . '/../models/BookCopyDAO.php';
require_once __DIR__ . '/../models/TransactionDAO.php';
require_once __DIR__ . '/../models/AccountDAO.php';

// --- Base Response ---
$response = ['success' => false, 'message' => 'Invalid action.'];

// Check if user is logged in
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'Student') {
    $response['message'] = 'Unauthorized';
    echo json_encode($response);
    exit;
}

// --- Instantiate Services ---
$studentService = new StudentService($conn);
$transactionDAO = new TransactionDAO($conn); // For history modal

$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        // === NEW: Place Hold Case ===
        case 'requestHold':
            $bookId = $_POST['book_id'] ?? 0;
            $accountId = $_SESSION['account_id'];
            
            if (empty($bookId)) {
                throw new Exception("Invalid Book ID.");
            }
            
            // This function will throw an error if it fails
            $holdData = $studentService->requestHold($accountId, $bookId);
            
            $response['success'] = true;
            $response['message'] = $holdData['message'];
            break;

        // --- Your existing History Modal Case ---
        case 'getHistoryDetails':
            $transactionId = $_GET['id'] ?? 0;
            $accountId = $_SESSION['account_id'];
            
            $details = $transactionDAO->getTransactionDetailsById($transactionId, $accountId);
            
            if (!$details) {
                throw new Exception("Transaction not found.");
            }
            
            // Build and echo the HTML for the history modal
            echo '
            <div class="history-details-grid">
                <div class="book-cover-area">
                    <img src="../assets/covers/' . htmlspecialchars($details['cover_url']) . '" alt="' . htmlspecialchars($details['title']) . '" class="book-detail-cover">
                </div>
                <div class="book-info-area">
                    <h2 class="book-detail-title">' . htmlspecialchars($details['title']) . '</h2>
                    <dl class="book-meta-list">
                        <dt>Author</dt><dd>' . htmlspecialchars($details['author_names'] ?? 'N/A') . '</dd>
                        <dt>Shelf Location</dt><dd>' . htmlspecialchars($details['shelf_location'] ?? 'N/A') . '</dd>
                        <dt>ISBN</dt><dd>' . htmlspecialchars($details['isbn'] ?? 'N/A') . '</Iadd>
                    </dl>
                </div>
                <div class="receipt-area">
                    <h3 class="receipt-title">Transaction Receipt</h3>
                    <dl class="receipt-list">
                        <dt>Borrowed Date:</dt><dd>' . htmlspecialchars($details['date_borrowed']) . '</dd>
                        <dt>Expected Due:</dt><dd>' . htmlspecialchars($details['date_due']) . '</dd>
                        <dt>Actual Return:</dt><dd>' . htmlspecialchars($details['date_returned'] ?? 'Not returned') . '</dd>
                        <dt>Status:</dt><dd class="receipt-status-text">' . htmlspecialchars($details['status']) . '</dd>
                    </dl>
                </div>
                <div class="notes-area">
                    <h3 class="notes-title">Fine</h3>
                    <div class="notes-box">
                        <p>Fine: $' . number_format($details['fine'], 2) . '</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer book-modal-footer history-modal-footer">
                <div class="book-actions">
                    <button class="modal-close-btn close-history-modal-btn">Close</button>
                </div>
            </div>
            ';
            // We exit here because we're sending HTML, not JSON
            exit;
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Echo the JSON response for 'requestHold'
echo json_encode($response);
?>