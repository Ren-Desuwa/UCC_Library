<?php
class SessionDAO {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Creates a new session for a user.
     * @param int $accountId The user's account ID.
     * @param string $tokenHash The cryptographically secure, hashed token.
     * @param string $expiresAt The SQL-formatted expiry timestamp.
     */
    public function createSession($accountId, $tokenHash, $expiresAt) {
        $sql = "INSERT INTO sessions (account_id, token_hash, expires_at) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $accountId, $tokenHash, $expiresAt);
        if (!$stmt->execute()) {
            throw new Exception("DAO Error: Failed to create session: " . $stmt->error);
        }
        return $this->conn->insert_id;
    }

    /**
     * Finds a session by its token hash.
     */
    public function getSessionByTokenHash($tokenHash) {
        $sql = "SELECT * FROM sessions WHERE token_hash = ? AND expires_at > NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $tokenHash);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Deletes a session by its token hash (used for logout).
     */
    public function deleteSessionByTokenHash($tokenHash) {
        $sql = "DELETE FROM sessions WHERE token_hash = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $tokenHash);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}