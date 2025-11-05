<?php
class AccountDAO {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAccountByUsername($username) {
        $sql = "SELECT * FROM accounts WHERE username = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getAccountByEmail($email) {
        $sql = "SELECT * FROM accounts WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function verifyPassword($password, $passwordHash) {
        // Your database stores a raw SHA-256 hash
        return hash('sha256', $password) === $passwordHash;
    }

    public function createAccount($username, $passwordHash, $role, $name, $email) {
        $sql = "INSERT INTO accounts (username, password_hash, role, name, email) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $passwordHash, $role, $name, $email);
        if (!$stmt->execute()) {
            throw new Exception("DAO Error: Failed to create account: " . $stmt->error);
        }
        return $this->conn->insert_id;
    }

    /**
     * NEW: This function is required for the reset password feature
     */
    public function updatePassword($accountId, $newPasswordHash) {
        $sql = "UPDATE accounts SET password_hash = ? WHERE account_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $newPasswordHash, $accountId);
        if (!$stmt->execute()) {
            throw new Exception("DAO Error: Failed to update password: " . $stmt->error);
        }
        return $stmt->affected_rows > 0;
    }
}
?>