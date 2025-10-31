<?php
class NotificationDAO {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Creates a new notification for a user.
     */
    public function createNotification($accountId, $message, $notificationType, $transactionId = null) {
        $sql = "INSERT INTO notifications (account_id, transaction_id, message, notification_type) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisi", $accountId, $transactionId, $message, $notificationType);
        if (!$stmt->execute()) {
            throw new Exception("DAO Error: Failed to create notification: " . $stmt->error);
        }
        return $this->conn->insert_id;
    }

    /**
     * Fetches all unread notifications for a user.
     */
    public function getUnreadNotificationsForUser($accountId) {
        $sql = "SELECT * FROM notifications 
                WHERE account_id = ? AND is_read = 0 
                ORDER BY date_sent DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $accountId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Marks a specific notification as read.
     */
    public function markAsRead($notificationId, $accountId) {
        $sql = "UPDATE notifications SET is_read = 1 
                WHERE notification_id = ? AND account_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $notificationId, $accountId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}