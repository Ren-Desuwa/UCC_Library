<?php
class SessionDAO {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function createSession($accountId, $tokenHash, $expiresAt) {
        $sql = "INSERT INTO sessions (account_id, token_hash, expires_at) 
                VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $accountId, $tokenHash, $expiresAt);
        
        if (!$stmt->execute()) {
            throw new Exception("DAO Error: Could not create session: " . $stmt->error);
        }
        return $this->conn->insert_id;
    }

    public function deleteSessionByTokenHash($tokenHash) {
        $sql = "DELETE FROM sessions WHERE token_hash = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $tokenHash);
        
        if (!$stmt->execute()) {
            throw new Exception("DAO Error: Could not delete session: " . $stmt->error);
        }
        return $stmt->affected_rows > 0;
    }
}
?>