<?php
require_once __DIR__ . '/../models/AccountDAO.php';
require_once __DIR__ . '/../models/TransactionDAO.php';
require_once __DIR__ . '/../models/NotificationDAO.php';
require_once __DIR__ . '/../models/FavoriteDAO.php';

class StudentProfileService {
    private $conn;
    private $accountDAO;
    private $transactionDAO;
    private $notificationDAO;
    private $favoriteDAO;

    public function __construct($conn) {
        $this->conn = $conn;
        // FIXED: Changed all assignments from . to ->
        $this->accountDAO = new AccountDAO($conn);
        $this->transactionDAO = new TransactionDAO($conn);
        $this->notificationDAO = new NotificationDAO($conn);
        $this->favoriteDAO = new FavoriteDAO($conn);
    }

    /**
     * Gets all data for a student's dashboard (history, favorites, etc.).
     */
    public function getDashboardData($accountId) {
        $data = [];
        // This now calls the new method in TransactionDAO
        $data['active_loans'] = $this->transactionDAO->getActiveTransactionsForUser($accountId);
        // Get favorites (This uses your FavoriteDAO)
        $data['favorites'] = $this->favoriteDAO->getFavoritesForUser($accountId);
        // Get notifications
        $data['notifications'] = $this->notificationDAO->getUnreadNotificationsForUser($accountId);
        
        return $data;
    }
    
    /**
     * Lets a student update their (non-critical) profile info.
     */
    public function updateProfile($accountId, $name, $contactNumber, $birthday) {
        // This now calls the new method in AccountDAO
        return $this->accountDAO->updateProfileDetails($accountId, $name, $contactNumber, $birthday);
    }

    /**
     * Manages a student's favorites (using your FavoriteDAO).
     */
    public function addFavorite($accountId, $bookId) {
        return $this->favoriteDAO->addFavorite($accountId, $bookId);
    }
    
    public function removeFavorite($accountId, $bookId) {
        return $this->favoriteDAO->removeFavorite($accountId, $bookId);
    }
}