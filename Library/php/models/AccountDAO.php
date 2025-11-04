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
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAccountById($accountId) {
        $sql = "SELECT * FROM accounts WHERE account_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $accountId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAccountByUsernameOrEmail($identifier) {
        $sql = "SELECT * FROM accounts WHERE username = ? OR email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAccountByStudentId($studentId) {
        $sql = "SELECT a.* FROM accounts a 
                JOIN students s ON a.account_id = s.account_id 
                WHERE s.student_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $studentId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAccountByEmail($email) {
        $sql = "SELECT * FROM accounts WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function verifyPassword($plainTextPassword, $passwordHash) {
        $hashed_password_to_check = hash('sha256', $plainTextPassword);
        return hash_equals($passwordHash, $hashed_password_to_check);
    }

    public function createAccount($username, $passwordHash, $role, $name, $email, $physical_id) {
        $sql = "INSERT INTO accounts (username, password_hash, role, name, email, physical_id, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, 1)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssss", $username, $passwordHash, $role, $name, $email, $physical_id);
        if (!$stmt->execute()) {
             throw new Exception("DAO Error: Failed to create account: " . $stmt->error);
        }
        return $this->conn->insert_id;
    }
    
    /**
     * NEW: This method was missing for the AccountManagementService.
     * Toggles an account's active status.
     */
    public function updateAccountStatus($accountId, $isActive) {
        $sql = "UPDATE accounts SET is_active = ? WHERE account_id = ?";
        $stmt = $this->conn->prepare($sql);
        $isActiveInt = $isActive ? 1 : 0;
        $stmt->bind_param("ii", $isActiveInt, $accountId);
        if (!$stmt->execute()) {
             throw new Exception("DAO Error: Failed to update account status: " . $stmt->error);
        }
        return $stmt->affected_rows > 0;
    }

    /**
     * NEW: This method was missing for StudentProfileService.
     * Lets a user update their own non-critical details.
     */
    public function updateProfileDetails($accountId, $name, $contactNumber, $birthday) {
        // Assumes birthday is a string like 'YYYY-MM-DD' or null
        $sql = "UPDATE accounts SET name = ?, contact_number = ?, birthday = ? WHERE account_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $contactNumber, $birthday, $accountId);
        if (!$stmt->execute()) {
             throw new Exception("DAO Error: Failed to update profile details: " . $stmt->error);
        }
        return $stmt->affected_rows > 0;
    }

    /**
     * NEW: Fetches all accounts for the Admin panel.
     */
    public function getAllAccounts() {
        $sql = "SELECT account_id, username, role, name, email, is_active 
                FROM accounts 
                ORDER BY role, username";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * --- NEW: Searches accounts, optionally filtered by role ---
     */
    public function searchAccounts($searchTerm, $roleFilter = "") {
        $sql = "SELECT account_id, username, role, name, email, is_active 
                FROM accounts
                WHERE (username LIKE ? OR name LIKE ? OR email LIKE ?)";
        
        $likeTerm = "%" . $searchTerm . "%";
        $params = [$likeTerm, $likeTerm, $likeTerm];
        $types = "sss";

        if (!empty($roleFilter)) {
            $sql .= " AND role = ?";
            $params[] = $roleFilter;
            $types .= "s";
        }
        
        $sql .= " ORDER BY name";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}