<?php
class TransactionDAO {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function createTransaction($accountId, $copyId, $type, $dateDue, $status) {
        $sql = "INSERT INTO transactions (account_id, copy_id, transaction_type, date_borrowed, date_due, status)
                VALUES (?, ?, ?, NOW(), ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iissi", $accountId, $copyId, $type, $dateDue, $status);
        if (!$stmt->execute()) {
            throw new Exception("DAO Error: Failed to create transaction: " . $stmt->error);
        }
        return $this->conn->insert_id;
    }

    public function updateTransactionStatus($transactionId, $status, $fine = 0.00) {
        $sql = "UPDATE transactions SET status = ?, date_returned = NOW(), fine = ?
                WHERE transaction_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sdi", $status, $fine, $transactionId);
        if (!$stmt->execute()) {
            throw new Exception("DAO Error: Failed to update transaction: " . $stmt->error);
        }
        return $stmt->affected_rows > 0;
    }
    
    /**
     * NEW: This method was missing for the AccountManagementService.
     * Manually sets or waives a fine for a transaction.
     */
    public function setFine($transactionId, $fineAmount) {
        $sql = "UPDATE transactions SET fine = ? WHERE transaction_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("di", $fineAmount, $transactionId);
         if (!$stmt->execute()) {
            throw new Exception("DAO Error: Failed to set fine: " . $stmt->error);
        }
        return $stmt->affected_rows > 0;
    }

    /**
     * Gets a count of active loans for a user.
     */
    public function getActiveTransactionCountForUser($accountId) {
        $sql = "SELECT COUNT(*) as count FROM transactions 
                WHERE account_id = ? AND status IN ('Active', 'Overdue')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $accountId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['count'];
    }

    /**
     * Fetches a transaction by its ID.
     */
    public function getTransactionById($transactionId) {
        $sql = "SELECT * FROM transactions WHERE transaction_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $transactionId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * NEW: This method was missing for AccountManagementService.
     * Adds an amount to an existing fine.
     */
    public function addFine($transactionId, $amountToAdd) {
        $sql = "UPDATE transactions SET fine = fine + ? WHERE transaction_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("di", $amountToAdd, $transactionId);
         if (!$stmt->execute()) {
            throw new Exception("DAO Error: Failed to add to fine: " . $stmt->error);
        }
        return $stmt->affected_rows > 0;
    }

    

    /**
     * NEW: This method was missing for StudentProfileService.
     * Fetches all active borrowed books for a specific user.
     */
    public function getActiveTransactionsForUser($accountId) {
        $sql = "SELECT t.*, b.title, b.cover_url FROM transactions t
                JOIN book_copies c ON t.copy_id = c.copy_id
                JOIN books b ON c.book_id = b.book_id
                WHERE t.account_id = ? AND t.status IN ('Active', 'Overdue')";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $accountId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    
}