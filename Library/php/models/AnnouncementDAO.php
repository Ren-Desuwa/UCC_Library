<?php
class AnnouncementDAO {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Creates a new announcement.
     * @param int $adminId The account_id of the admin creating it.
     * @param string $title
     * @param string $message
     * @param string $priority (e.g., 'High', 'Normal', 'Low')
     * @return int The ID of the new announcement.
     */
    public function createAnnouncement($adminId, $title, $message, $priority) {
        $sql = "INSERT INTO announcements (admin_id, title, message, priority, is_active) 
                VALUES (?, ?, ?, ?, 1)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $adminId, $title, $message, $priority);
        if (!$stmt->execute()) {
            throw new Exception("DAO Error: Failed to create announcement: " . $stmt->error);
        }
        return $this->conn->insert_id;
    }

    /**
     * Fetches all announcements.
     * @return array
     */
    public function getAllAnnouncements() {
        $sql = "SELECT a.*, ac.username as admin_username
                FROM announcements a
                JOIN accounts ac ON a.admin_id = ac.account_id
                ORDER BY a.date_posted DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>