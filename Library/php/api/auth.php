<?php
/**
 * API Endpoint: auth.php
 * * This file acts as the bridge between the frontend JavaScript (auth.js)
 * and the backend PHP logic (AuthService.php).
 * * It handles 'action' parameters from the URL to route requests.
 */

// Set the content type header to JSON so the JavaScript fetch() knows how to read it.
header('Content-Type: application/json');

// Include the necessary backend files
require_once __DIR__ . '/../db_connect.php'; // Database connection
require_once __DIR__ . '/../services/AuthService.php'; // The logic file

// Get the requested action from the URL (e.g., ?action=login)
$action = $_GET['action'] ?? null;

// Initialize the AuthService with the database connection
$authService = new AuthService($conn);

// Create an array to hold the JSON response
$response = [
    'success' => false,
    'message' => 'Invalid action.'
];

try {
    switch ($action) {
        /**
         * --- LOGIN ---
         * Handles the login request from auth.js
         * Expects: $_POST['username'], $_POST['password']
         */
        case 'login':
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                throw new Exception("Username and password are required.");
            }

            // The login method starts the session and sets cookies on success
            $account = $authService->login($username, $password);
            
            $response['success'] = true;
            $response['message'] = "Login successful. Redirecting...";
            $response['role'] = $account['role']; // Send role back to JS
            break;

        /**
         * --- REGISTER ---
         * Handles the registration request from auth.js
         * Expects: $_POST['username'], $_POST['email'], $_POST['firstName'], etc.
         */
        case 'register':
            // Get all required fields from the registration form
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmPassword'] ?? '';
            
            // Combine names. The 'name' field in the DB is just one field.
            $firstName = $_POST['firstName'] ?? '';
            $lastName = $_POST['lastName'] ?? '';
            $name = trim($firstName . ' ' . $lastName);

            // --- Server-Side Validation ---
            if (empty($username) || empty($email) || empty($name) || empty($password)) {
                throw new Exception("Please fill out all required fields.");
            }
            if ($password !== $confirmPassword) {
                throw new Exception("Passwords do not match.");
            }
            if (strlen($password) < 8) {
                throw new Exception("Password must be at least 8 characters long.");
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format.");
            }

            // Call the registerStudent method
            $accountId = $authService->registerStudent($username, $email, $name, $password);

            $response['success'] = true;
            $response['message'] = "Registration successful! You can now log in.";
            break;

        /**
         * --- LOGOUT ---
         * Handles a logout request.
         */
        case 'logout':
            // The token is stored in an HttpOnly cookie
            $token = $_COOKIE['auth_token'] ?? '';
            $authService->logout($token); // This clears cookies and session
            
            $response['success'] = true;
            $response['message'] = 'Logged out successfully.';
            break;
            
        default:
            // This will use the default $response message
            break;
    }
} catch (Exception $e) {
    // If anything in the 'try' block throws an Exception, catch it here.
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

// Finally, encode the $response array as JSON and send it back to auth.js
echo json_encode($response);