<?php
class UserOtpDAO {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function createOtp($userId, $verificationTarget, $otpCode, $expiresAt) {
        $sql = "INSERT INTO user_otp (user_id, verification_target, otp_code, expires_at) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $userId, $verificationTarget, $otpCode, $expiresAt);
         if (!$stmt->execute()) {
            throw new Exception("DAO Error: Failed to create OTP: " . $stmt->error);
        }
        return $this->conn->insert_id;
    }
    
    public function getValidOtp($verificationTarget, $otpCode) {
        $sql = "SELECT * FROM user_otp 
                WHERE verification_target = ? AND otp_code = ? 
                AND expires_at > NOW() AND is_used = 0
                ORDER BY otp_id DESC LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $verificationTarget, $otpCode);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
        
    }
    
    public function markOtpAsUsed($otpId) {
        $sql = "UPDATE user_otp SET is_used = 1 WHERE otp_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $otpId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}