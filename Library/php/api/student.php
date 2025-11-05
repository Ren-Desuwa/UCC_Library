<?php
/**
 * API Endpoint: student.php
 *
 * Handles student-specific data requests, like history and settings.
 */

session_start();
header('Content-Type: text/html'); // We will return HTML fragments

require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../models/TransactionDAO.php';

// Ensure user is logged in
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'Student') {
    http_response_code(403); // Forbidden
    echo "<p>Error: You are not authorized to perform this action.</p>";
    exit;
}

$action = $_GET['action'] ?? null;
$accountId = $_SESSION['account_id'];
$responseHTML = '';

try {
    $transactionDAO = new TransactionDAO($conn);

    switch ($action) {
        /**
         * --- GET HISTORY DETAILS ---
         * Fetches all details for a single transaction and formats it
         * as HTML for the history modal.
         * Expects: $_GET['id'] (transaction_id)
         */
        case 'getHistoryDetails':
            $transactionId = $_GET['id'] ?? 0;
            if (empty($transactionId)) {
                throw new Exception("No transaction ID provided.");
            }

            $t = $transactionDAO->getTransactionDetailsById($transactionId, $accountId);
            if (!$t) {
                throw new Exception("Transaction not found or you do not have permission to view it.");
            }
            
            // Helper function to format dates
            $formatDate = fn($date) => $date ? date("M j, Y", strtotime($date)) : 'N/A';

            // Build the HTML response based on _modals_student.php
            $responseHTML .= '
                <div class="history-details-grid">
                    <div class="book-cover-area">
                        <img src="../assets/covers/' . htmlspecialchars($t['cover_url']) . '" alt="' . htmlspecialchars($t['title']) . '" class="book-detail-cover">
                    </div>
                    
                    <div class="book-info-area">
                        <h2 class="book-detail-title">' . htmlspecialchars($t['title']) . '</h2>
                        <dl class="book-meta-list">
                            <dt>Author</dt><dd>' . htmlspecialchars($t['author_names'] ?? 'N/A') . '</dd>
                            <dt>Shelf Location</dt><dd>' . htmlspecialchars($t['shelf_location'] ?? 'N/A') . '</dd>
                            <dt>ISBN</dt><dd>' . htmlspecialchars($t['isbn'] ?? 'N/A') . '</dd>
                            <dt>Publisher</dt><dd>' . htmlspecialchars($t['publisher'] ?? 'N/A') . '</dd>
                            <dt>Description</dt><dd>' . htmlspecialchars($t['description'] ?? 'N/A') . '</dd>
                        </dl>
                    </div>

                    <div class="receipt-area">
                        <h3 class="receipt-title">Transaction Receipt</h3>
                        <dl class="receipt-list">
                            <dt>Borrowed Date:</dt><dd>' . $formatDate($t['date_borrowed']) . '</dd>
                            <dt>Expected Due:</dt><dd>' . $formatDate($t['date_due']) . '</dd>
                            <dt>Transaction ID:</dt><dd>' . htmlspecialchars($t['transaction_id']) . '</dd>
                            <dt>Actual Return:</dt><dd>' . $formatDate($t['date_returned']) . '</dd>
                            <dt>Status:</dt><dd class="receipt-status-text">' . htmlspecialchars($t['status']) . '</dd>
                            <dt>Fine:</dt><dd class="receipt-status-text">$' . number_format($t['fine'], 2) . '</dd>
                        </dl>
                    </div>
                    
                    <div class="notes-area">
                        <h3 class="notes-title">Librarian Notes</h3>
                        <div class="notes-box">
                            <p>Condition on return: ...</p>
                            <p>...</p>
                        </div>
                    </div>
                </div>

                <div class="modal-footer book-modal-footer history-modal-footer">
                    <div class="book-actions">
                        <button id="close-history-modal-btn" class="modal-close-btn close-history-modal-btn">Close</button>
                    </div>
                </div>
            ';
            break;

        default:
            throw new Exception("Invalid student action.");
    }
    
    echo $responseHTML;

} catch (Exception $e) {
    http_response_code(500);
    // Send an error message HTML
    echo '
        <div class="modal-header"><h2>Error</h2></div>
        <div class="modal-body">
            <p>Could not load details: ' . htmlspecialchars($e->getMessage()) . '</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="modal-close-btn close-history-modal-btn">Close</button>
        </div>
    ';
}
?>