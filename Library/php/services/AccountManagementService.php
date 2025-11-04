<?php
require_once __DIR__ . '/../models/AccountDAO.php';
require_once __DIR__ . '/../models/TransactionDAO.php';
require_once __DIR__ . '/../models/NotificationDAO.php';

class AccountManagementService {
    private $conn;
    private $accountDAO;
    private $transactionDAO;
    private $notificationDAO;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->accountDAO = new AccountDAO($conn);
        $this->transactionDAO = new TransactionDAO($conn);
        $this->notificationDAO = new NotificationDAO($conn);
    }

    /**
     * Creates a new Librarian account (implements 'librarian_account' request).
     */
    public function createLibrarianAccount($username, $email, $name, $password, $physical_id) {
        // This uses the same logic as AuthService::registerStudent
        // but sets the role to 'Librarian'.
        $this->conn->begin_transaction();
        try {
            if ($this->accountDAO->getAccountByUsername($username)) {
                throw new Exception("Username already taken.");
            }
            $passwordHash = hash('sha256', $password);
            $accountId = $this->accountDAO->createAccount($username, $passwordHash, 'Librarian', $name, $email, $physical_id);
            $this->conn->commit();
            return $accountId;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function toggleAccountStatus($accountId, $isActive) {
        // This will now work
        return $this->accountDAO->updateAccountStatus($accountId, $isActive);
    }

    public function sendManualNotification($accountId, $message) {
        $notificationType = 99; // 99 for 'Manual/Admin'
        return $this->notificationDAO->createNotification($accountId, $message, $notificationType);
    }

    public function waiveFine($transactionId) {
        // This will now work
        return $this->transactionDAO->setFine($transactionId, 0.00);
    }
    /**
     * Manually adds a fine to a user's account for a specific transaction.
     * (e.g., for book damage).
     */
    public function issueManualFine($transactionId, $fineAmount, $reason) {
        // 1. Use the DAO to add the fine
        // (You would add a 'addFine' method to TransactionDAO)
        $this->transactionDAO->addFine($transactionId, $fineAmount);
        
        // 2. Get transaction details to notify user
        $transaction = $this->transactionDAO->getTransactionById($transactionId);
        $accountId = $transaction['account_id'];
        
        // 3. Send a notification
        $message = "A fine of $$fineAmount has been added to your account for transaction $transactionId. Reason: $reason";
        $this->notificationDAO->createNotification($accountId, $message, 10, $transactionId); // 10 = ManualFine
        
        return true;
    }
}