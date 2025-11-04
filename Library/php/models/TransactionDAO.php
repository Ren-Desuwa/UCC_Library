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
            throw new Exception("DAO Error: Failed to create transaction: "." - ".$stmt->error);
        }
        return $this->conn->insert_id;
    }

    public function updateTransactionStatus($transactionId, $status, $fine = 0.00) {
        $sql = "UPDATE transactions SET status = ?, date_returned = NOW(), fine = ?
                WHERE transaction_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sdi", $status, $fine, $transactionId);
        if (!$stmt->execute()) {
            throw new Exception("DAO Error: Failed to update transaction: "." - ".$stmt->error);
        }
        return $stmt->affected_rows > 0;
    }
    
    public function setFine($transactionId, $fineAmount) {
        $sql = "UPDATE transactions SET fine = ? WHERE transaction_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("di", $fineAmount, $transactionId);
         if (!$stmt->execute()) {
            throw new Exception("DAO Error: Failed to set fine: "." - ".$stmt->error);
        }
        return $stmt->affected_rows > 0;
    }
    
    // --- NEW: Function to add to an existing fine ---
    public function addFine($transactionId, $amountToAdd) {
        $sql = "UPDATE transactions SET fine = fine + ? WHERE transaction_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("di", $amountToAdd, $transactionId);
         if (!$stmt->execute()) {
            throw new Exception("DAO Error: Failed to add fine: "." - ".$stmt->error);
        }
        return $stmt->affected_rows > 0;
    }

    public function getActiveTransactionCountForUser($accountId) {
        $sql = "SELECT COUNT(*) as count FROM transactions 
                WHERE account_id = ? AND status IN ('Active', 'Overdue')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $accountId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['count'];
    }

    public function getTransactionById($transactionId) {
        $sql = "SELECT * FROM transactions WHERE transaction_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $transactionId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getActiveTransactionsForUser($accountId) {
        $sql = "SELECT t.*, b.title, b.cover_url FROM transactions t
                JOIN book_copies c ON t.copy_id = c.copy_id
                JOIN books b ON c.book_id = b.book_id
                WHERE t.account_id = ? AND t.status IN ('Active', 'Overdue')
                ORDER BY t.date_due ASC"; // <-- Changed to sort by due date
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $accountId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // --- NEW: Function to get completed transactions ---
    public function getCompletedTransactionsForUser($accountId) {
        $sql = "SELECT t.*, b.title, b.cover_url FROM transactions t
                JOIN book_copies c ON t.copy_id = c.copy_id
                JOIN books b ON c.book_id = b.book_id
                WHERE t.account_id = ? AND t.status = 'Completed'
                ORDER BY t.date_returned DESC
                LIMIT 50"; // Limit to last 50
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $accountId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getTransactionDetailsById($transactionId, $accountId) {
        $sql = "SELECT 
                    t.*, 
                    b.title, b.cover_url, b.description, b.publisher, b.isbn,
                    c.shelf_location,
                    GROUP_CONCAT(DISTINCT a.name SEPARATOR ', ') AS author_names
                FROM transactions t
                LEFT JOIN book_copies c ON t.copy_id = c.copy_id
                LEFT JOIN books b ON c.book_id = b.book_id
                LEFT JOIN book_authors ba ON b.book_id = ba.book_id
                LEFT JOIN authors a ON ba.author_id = a.author_id
                WHERE t.transaction_id = ? AND t.account_id = ?
                GROUP BY t.transaction_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $transactionId, $accountId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}