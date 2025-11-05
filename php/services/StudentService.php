<?php
// We need all these DAOs to make a transaction
require_once __DIR__ . '/../models/BookDAO.php';
require_once __DIR__ . '/../models/BookCopyDAO.php';
require_once __DIR__ . '/../models/TransactionDAO.php';
require_once __DIR__ . '/../models/AccountDAO.php';

class StudentService {
    private $conn;
    private $bookDAO;
    private $bookCopyDAO;
    private $transactionDAO;
    private $accountDAO;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->bookDAO = new BookDAO($conn);
        $this->bookCopyDAO = new BookCopyDAO($conn);
        $this->transactionDAO = new TransactionDAO($conn);
        $this->accountDAO = new AccountDAO($conn);
    }

    /**
     * === NEW: Handles the logic for a student placing a hold ===
     */
    public function requestHold($accountId, $bookId) {
        // --- 1. Check Business Rules ---
        
        // Rule: Does the user already have this book borrowed or on hold?
        if ($this->transactionDAO->hasActiveHoldOrLoanForBook($accountId, $bookId)) {
            throw new Exception("You already have this book borrowed or on hold.");
        }

        // Rule: Are any copies *in general* available? (Not in 'Maintenance')
        $copies = $this->bookCopyDAO->getCopiesForBook($bookId);
        $isHoldable = false;
        foreach ($copies as $copy) {
            if ($copy['status'] !== 'Maintenance' && $copy['status'] !== 'Lost') {
                $isHoldable = true;
                break;
            }
        }
        if (!$isHoldable) {
            throw new Exception("Sorry, this book is not available for holds (e.g., all copies are in maintenance).");
        }
        
        // --- 2. Process the Transaction ---
        $this.conn->begin_transaction();
        try {
            
            // a. Create the new 'Hold' transaction record
            $transactionId = $this->transactionDAO->createHoldRequest(
                $accountId, 
                $bookId, 
                'Hold',      // transaction_type
                'Pending'    // status
            );

            // b. Commit the changes
            $this.conn->commit();
            
            // --- 3. Return success message ---
            return [
                'transaction_id' => $transactionId,
                'message' => 'Hold placed successfully! A librarian will process your request.'
            ];

        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Transaction Failed: " . $e->getMessage());
        }
    }
}
?>