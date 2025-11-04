<?php
require_once __DIR__ . '/../models/AccountDAO.php';
require_once __DIR__ . '/../models/SessionDAO.php';
require_once __DIR__ . '/../models/UserOtpDAO.php';

class AuthService {
    private $conn;
    private $accountDAO;
    private $sessionDAO;
    private $userOtpDAO; 

    public function __construct($conn) {
        $this->conn = $conn;
        $this->accountDAO = new AccountDAO($conn);
        $this->sessionDAO = new SessionDAO($conn);
        $this->userOtpDAO = new UserOtpDAO($conn);
    }

    /**
     * Registers a new student account.
     * This is a transactional operation.
     */
    public function registerStudent($username, $email, $contactNumber, $name, $password) {
        // Start the transaction
        $this->conn->begin_transaction();

        try {
            // 1. Business Logic: Check for existing user
            if ($this->accountDAO->getAccountByUsername($username)) {
                throw new Exception("Username already taken.");
            }
            if ($email && $this->accountDAO->getAccountByEmail($email)) {
                throw new Exception("Email already in use.");
            }
            if ($contactNumber && $this->accountDAO->getAccountByContactNumber($contactNumber)) {
                throw new Exception("Contact number already in use.");
            }

            // 2. Business Logic: Hash password (Your schema uses SHA-256)
            $passwordHash = hash('sha256', $password);
            // In a real app: $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // 3. Data Operation: Use the DAO to create the account
            $role = 'Student';
            $accountId = $this->accountDAO->createAccount($username, $passwordHash, $role, $name, $email, $contactNumber); 

            // If everything is successful, commit the transaction
            $this->conn->commit();
            return $accountId;

        } catch (Exception $e) {
            // An error occurred, roll back all changes
            $this->conn->rollback();
            // Re-throw the exception to let the caller (the page) know what went wrong
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
        // 1. Create secure token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token); // Store the hash, not the token
        $expiresAt = (new DateTime())->add(new DateInterval("PT30M"))->format('Y-m-d H:i:s'); // 30mins session

        // 2. Use the DAO to save the session
        $this->sessionDAO->createSession($account['account_id'], $tokenHash, $expiresAt);

        // 3. Set the REAL token in a cookie (not the hash)
        setcookie("auth_token", $token, [
            'expires' => time() + (86400 * 7), // 7 days
            'path' => '/',
            'httponly' => true,
            'secure' => true, // Set to true on a real server
            'samesite' => 'Lax'
        ]);
        
        // 4. Set session data for immediate use
        session_start();
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
        
        // 1. Delete from database
        $this->sessionDAO->deleteSessionByTokenHash($tokenHash);
        
        // 2. Unset the cookie
        setcookie("auth_token", "", time() - 3600, "/");
        
        // 3. Destroy the PHP session
        session_start();
        session_unset();
        session_destroy();
    }

    /**
     * Starts the password reset process.
     */
    public function requestPasswordReset($email) {
        $account = $this->accountDAO->getAccountByEmail($email);
        if (!$account) {
            // Don't reveal if email exists, just return success
            return true;
        }

        try {
        $otpCode = rand(100000, 999999);
        date_default_timezone_set('Asia/Manila');
        $expiresAt = (new DateTime())->add(new DateInterval("PT15M"))->format('Y-m-d H:i:s'); // 15 min expiry
        
        // This will now work
        $this->userOtpDAO->createOtp($account['account_id'], $email, $otpCode, $expiresAt);
        
        // Here you would email the $otpCode to the user
        //mail($email, "Your Password Reset Code", "Your code is: $otpCode");
        
        return true;
        }
        catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function verifyOtp($email, $otp) {
        $otpRecord = $this->userOtpDAO->getValidOtp($email, $otp);

        if (!$otpRecord) {
            throw new Exception('invalid or Expired Code');
        }

        $this->userOtpDAO->markOtpAsUsed($otpRecord['otp_id']);

        return true;
    }

   
}