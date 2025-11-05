<?php
require_once __DIR__ . '/../models/AccountDAO.php';
require_once __DIR__ . '/../models/SessionDAO.php';
require_once __DIR__ . '/../models/UserOtpDAO.php';
require_once __DIR__ . '/MailService.php'; 

class AuthService {
    private $conn;
    private $accountDAO;
    private $sessionDAO;
    private $userOtpDAO; 
    private $mailService; 

    public function __construct($conn) {
        $this->conn = $conn;
        $this->accountDAO = new AccountDAO($conn);
        $this->sessionDAO = new SessionDAO($conn);
        $this->userOtpDAO = new UserOtpDAO($conn); 
        $this->mailService = new MailService(); 
    }

    /**
     * Registers a new student account.
     */
    public function registerStudent($username, $email, $name, $password) {
        $this->conn->begin_transaction();
        try {
            if ($this->accountDAO->getAccountByUsername($username)) {
                throw new Exception("Username already taken.");
            }
            if ($this->accountDAO->getAccountByEmail($email)) {
                throw new Exception("Email already in use.");
            }
            $passwordHash = hash('sha256', $password);
            $role = 'Student';
            $accountId = $this->accountDAO->createAccount($username, $passwordHash, $role, $name, $email);
            $this->conn->commit();
            return $accountId;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * Logs in a user.
     */
    public function login($username, $password) {
        $account = $this->accountDAO->getAccountByUsername($username);
        if (!$account || !$this->accountDAO->verifyPassword($password, $account['password_hash'])) {
            throw new Exception("Invalid username or password.");
        }
        if (!$account['is_active']) {
            throw new Exception("Your account is deactivated.");
        }

        // --- SESSION LOGIC ---
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token); 
        
        // === TIMEZONE FIX ===
        // Force PHP to use UTC timezone for dates
        $utc = new DateTimeZone('UTC');
        $expiresAt = (new DateTime("now", $utc))->add(new DateInterval("PT30M"))->format('Y-m-d H:i:s');
        
        $this->sessionDAO->createSession($account['account_id'], $tokenHash, $expiresAt);
        
        setcookie("auth_token", $token, [
            'expires' => time() + (86400 * 7), 
            'path' => '/',
            'httponly' => true,
            'secure' => true, 
            'samesite' => 'Lax'
        ]);
        
        $_SESSION['account_id'] = $account['account_id'];
        $_SESSION['role'] = $account['role'];
        $_SESSION['name'] = $account['name'];
        $_SESSION['username'] = $account['username']; 

        return $account;
    }
    
    /**
     * Logs a user out by deleting their session.
     */
    public function logout($token) {
        $tokenHash = hash('sha256', $token);
        
        $this->sessionDAO->deleteSessionByTokenHash($tokenHash);
        
        setcookie("auth_token", "", time() - 3600, "/");
        
        session_unset();
        session_destroy();
    }

    /**
     * Starts the password reset process
     */
    public function requestPasswordReset($email) {
        $account = $this->accountDAO->getAccountByEmail($email);
        if (!$account) {
            error_log("Password reset request for non-existent email: $email");
            return true;
        }
        
        $otpCode = (string)rand(100000, 999999);
        
        // === TIMEZONE FIX ===
        // Force PHP to use UTC timezone for dates
        $utc = new DateTimeZone('UTC');
        $expiresAt = (new DateTime("now", $utc))->add(new DateInterval("PT15M"))->format('Y-m-d H:i:s');
        
        // Save the OTP to the database
        $this->userOtpDAO->createOtp($account['account_id'], $email, $otpCode, $expiresAt);
        
        // Send the email
        $this->mailService->sendPasswordResetOtp($account['email'], $otpCode, $account['name']);
        
        return true; // Always return true
    }

    /**
     * Completes the password reset process.
     */
    public function resetPassword($email, $otpCode, $newPassword, $confirmPassword) {
        // 1. Validation
        if ($newPassword !== $confirmPassword) {
            throw new Exception("New passwords do not match.");
        }
        if (strlen($newPassword) < 8) {
            throw new Exception("Password must be at least 8 characters long.");
        }

        // 2. Find user by email
        $account = $this->accountDAO->getAccountByEmail($email);
        if (!$account) {
            throw new Exception("Invalid email or OTP.");
        }

        // 3. Validate the OTP for that user
        $otpData = $this->userOtpDAO->validateOtp($account['account_id'], $otpCode);
        if (!$otpData) {
            throw new Exception("Invalid or expired OTP.");
        }

        // 4. Start transaction
        $this->conn->begin_transaction();
        try {
            // 5. Update password
            $newPasswordHash = hash('sha256', $newPassword);
            $this->accountDAO->updatePassword($account['account_id'], $newPasswordHash);

            // 6. Mark OTP as used
            $this->userOtpDAO->markOtpAsUsed($otpData['otp_id']);

            // 7. Commit
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Could not reset password: " . $e->getMessage());
        }
    }
}
?>