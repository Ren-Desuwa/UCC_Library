<?php
class LogDAO {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Fetches the most recent logs, joining with account username.
     * @param int $limit Number of logs to fetch.
     * @return array An array of log entries.
     */
    public function getLogs($limit = 100) {
        $sql = "SELECT l.*, a.username 
                FROM logs l
                LEFT JOIN accounts a ON l.account_id = a.account_id
                ORDER BY l.timestamp DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>