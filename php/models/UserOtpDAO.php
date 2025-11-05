<?php
class UserOtpDAO {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Creates a new OTP for a user, invalidating any old ones.
     */
    public function createOtp($accountId, $email, $otpCode, $expiresAt) {
        // Invalidate old OTPs for this user
        $sqlDelete = "DELETE FROM user_otp WHERE user_id = ?";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->bind_param("i", $accountId);
        $stmtDelete->execute();

        // Insert the new OTP
        $sql = "INSERT INTO user_otp (user_id, verification_target, otp_code, expires_at) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $accountId, $email, $otpCode, $expiresAt);
        
        if (!$stmt->execute()) {
            throw new Exception("DAO Error: Could not create OTP: " . $stmt->error);
        }
        return $this->conn->insert_id;
    }

    /**
     * Validates an OTP for a user.
     */
    public function validateOtp($accountId, $otpCode) {
        
        // === THIS IS THE FIX ===
        // Changed NOW() (local time) to UTC_TIMESTAMP() (universal time)
        // This will correctly match the UTC time saved by AuthService.php
        $sql = "SELECT * FROM user_otp 
                WHERE user_id = ? AND otp_code = ? AND expires_at > UTC_TIMESTAMP() AND is_used = 0";
        // === END OF FIX ===
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $accountId, $otpCode);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Marks an OTP as used.
     */
    public function markOtpAsUsed($otpId) {
        $sql = "UPDATE user_otp SET is_used = 1 WHERE otp_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $otpId);
        if (!$stmt->execute()) {
            throw new Exception("DAO Error: Could not mark OTP as used: " . $stmt->error);
        }
        return $stmt->affected_rows > 0;
    }
}
?>