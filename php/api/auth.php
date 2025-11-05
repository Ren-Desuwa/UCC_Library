<?php
header('Content-Type: application/json');
session_start(); 

// --- Global Includes ---
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../models/AccountDAO.php'; // Needed by AuthService

// --- Instantiate Services ---
$authService = new AuthService($conn);
$accountDAO = new AccountDAO($conn); // For username lookup

// --- Base Response ---
$response = ['success' => false, 'message' => 'Invalid action.'];

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        
        // --- LOGIN CASE ---
        case 'login':
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $authService->login($username, $password);
            $response['success'] = true;
            $response['message'] = 'Login successful.';
            break;

        // --- REGISTER CASE ---
        case 'register':
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $firstName = $_POST['firstName'] ?? '';
            $middleName = $_POST['middleName'] ?? '';
            $lastName = $_POST['lastName'] ?? '';
            $name = trim($firstName . ' ' . $middleName . ' ' . $lastName);
            
            $authService->registerStudent($username, $email, $name, $password);
            
            $response['success'] = true;
            $response['message'] = 'Registration successful.';
            break;

        // --- FORGOT PASSWORD CASE (UPDATED) ---
        case 'forgotPassword':
            $identifier = $_POST['recovery_identifier'] ?? '';
            $email = '';
            
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $email = $identifier;
            } else {
                $account = $accountDAO->getAccountByUsername($identifier);
                if ($account) {
                    $email = $account['email'];
                }
            }
            
            if (!empty($email)) {
                // This now sends a real email
                $authService->requestPasswordReset($email);
            }
            
            // SECURITY: Always send a generic success message
            // This prevents attackers from guessing which emails are registered.
            $response['success'] = true;
            $response['message'] = 'If an account with that email or username exists, a password reset code has been sent.';
            break;

        // --- RESET PASSWORD CASE ---
        case 'resetPassword':
            $email = $_POST['email'] ?? '';
            $otp = $_POST['otp'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            $authService->resetPassword($email, $otp, $newPassword, $confirmPassword);
            
            $response['success'] = true;
            $response['message'] = 'Password has been reset successfully! You can now log in.';
            break;

        // --- LOGOUT CASE ---
        case 'logout':
            $token = $_COOKIE['auth_token'] ?? '';
            $authService->logout($token);
            $response['success'] = true;
            $response['message'] = 'Logout successful.';
            break;
    }

} catch (Exception $e) {
    // For security, only show detailed errors for non-production
    // In a real production environment, you would log $e->getMessage()
    // and just show a generic error to the user.
    
    // For our testing, we will show the real error.
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>