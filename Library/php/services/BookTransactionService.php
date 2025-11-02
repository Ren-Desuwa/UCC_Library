<?php
require_once __DIR__ . '/../models/TransactionDAO.php';
require_once __DIR__ . '/../models/BookCopyDAO.php';
require_once __DIR__ . '/../models/SettingsDAO.php';

class BookTransactionService {
    private $conn;
    private $transactionDAO;
    private $bookCopyDAO;
    private $settingsDAO;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->transactionDAO = new TransactionDAO($conn);
        $this->bookCopyDAO = new BookCopyDAO($conn);
        $this->settingsDAO = new SettingsDAO($conn); // This will now work
    }

    public function borrowBook($accountId, $copyId) {
        $this->conn->begin_transaction();
        try {
            // Get all settings at once
            $settings = $this->settingsDAO->getAllSettings();
            $maxBooks = (int) $settings['max_books_per_user'];
            $duration = (int) $settings['borrow_duration_days'];

            $copy = $this->bookCopyDAO->getCopyById($copyId);
            if ($copy['status'] !== 'Available') {
                throw new Exception("This book copy is not available.");
            }

            $activeLoanCount = $this->transactionDAO->getActiveTransactionCountForUser($accountId);
            if ($activeLoanCount >= $maxBooks) {
                throw new Exception("You have reached your maximum borrow limit of $maxBooks books.");
            }

            $dateDue = (new DateTime())->add(new DateInterval("P{$duration}D"))->format('Y-m-d H:i:s');
            $this->bookCopyDAO->updateCopyStatus($copyId, 'Borrowed');
            $transactionId = $this->transactionDAO->createTransaction($accountId, $copyId, 'Borrow', $dateDue, 'Active');

            $this->conn->commit();
            return $transactionId;

        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
     public function returnBook($transactionId) {
        $this->conn->begin_transaction();
        try {
            $transaction = $this->transactionDAO->getTransactionById($transactionId);
            if (!$transaction || !in_array($transaction['status'], ['Active', 'Overdue'])) {
                throw new Exception("Transaction not found or already completed.");
            }
            
            $fine = 0.00;
            if ($transaction['status'] === 'Overdue') {
                $settings = $this->settingsDAO->getAllSettings();
                $finePerDay = (float) $settings['overdue_fine_per_day'];
                
                $dateDue = new DateTime($transaction['date_due']);
                $dateReturned = new DateTime();
                if($dateReturned > $dateDue) {
                    $daysOverdue = $dateReturned->diff($dateDue)->days;
                    $fine = $daysOverdue * $finePerDay;
                }
            }
            
            $this->transactionDAO->updateTransactionStatus($transactionId, 'Completed', $fine);
            $this->bookCopyDAO->updateCopyStatus($transaction['copy_id'], 'Available');
            
            $this->conn->commit();
            return ['status' => 'Success', 'fine_paid' => $fine];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
     }

    /**
     * Creates a reservation for a book that is currently borrowed.
     */
    /**
     * Creates a reservation for a book that is currently borrowed.
     */
     // START: REMOVED BROKEN FUNCTION
     /*
    public function reserveBook($accountId, $bookId) {
        // 1. Check if any copy of this book is available
        $copies = $this->bookCopyDAO->getCopiesForBook($bookId);
        foreach ($copies as $copy) {
            if ($copy['status'] == 'Available') {
                throw new Exception("This book is available. You can borrow it directly instead of reserving.");
            }
        }
        
        // 2. Check if user already has a reservation for this book
        // (You would add a TransactionDAO method 'findReservationByUserAndBook')
        
        // 3. Create a 'Reservation' transaction
        $this->conn->begin_transaction();
        try {
            // Note: copy_id is NULL because no specific copy is assigned yet.
            $transactionId = $this->transactionDAO->createTransaction($accountId, null, 'Reservation', null, 'Pending');
            $this.conn->commit(); // FIXED: Changed $this. to $this->
            return $transactionId;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    */
    // END: REMOVED BROKEN FUNCTION
}