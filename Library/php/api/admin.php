<?php
/**
 * API Endpoint: admin.php
 *
 * Handles all authenticated Admin-level actions.
 */

session_start();
header('Content-Type: application/json'); // Default response type

// --- Security Check: Ensure user is an Admin ---
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Error: Not authorized.']);
    exit;
}

// --- Includes ---
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../services/AccountManagementService.php';
require_once __DIR__ . '/../models/SettingsDAO.php';
require_once __DIR__ . '/../models/LogDAO.php';
require_once __DIR__ . '/../models/AccountDAO.php';
require_once __DIR__ . '/../models/AnnouncementDAO.php';

// --- Service & DAO Initialization ---
$accountService = new AccountManagementService($conn);
$settingsDAO = new SettingsDAO($conn);
$logDAO = new LogDAO($conn);
$accountDAO = new AccountDAO($conn);
$announcementDAO = new AnnouncementDAO($conn);

$response = ['success' => false, 'message' => 'Invalid action.'];
$action = $_REQUEST['action'] ?? null;
$adminId = $_SESSION['account_id'];

try {
    switch ($action) {

        // --- Account Management ---
        case 'getAllAccounts':
            $response['success'] = true;
            $response['data'] = $accountDAO->getAllAccounts();
            break;

        case 'createLibrarian':
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $name = $_POST['name'] ?? '';
            $physical_id = $_POST['physical_id'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($email) || empty($name) || empty($password) || empty($physical_id)) {
                throw new Exception("All fields are required.");
            }
            
            $newAccountId = $accountService->createLibrarianAccount($username, $email, $name, $password, $physical_id);
            $response['success'] = true;
            $response['message'] = "Librarian account created (ID: $newAccountId).";
            break;

        case 'toggleAccountStatus':
            $accountId = $_POST['account_id'] ?? 0;
            $isActive = $_POST['is_active'] ?? 0; // 0 or 1
            
            if (empty($accountId)) throw new Exception("Account ID is required.");
            if ($accountId == $adminId) throw new Exception("Admin cannot change their own status.");

            $accountService->toggleAccountStatus($accountId, (bool)$isActive);
            $response['success'] = true;
            $response['message'] = "Account status updated.";
            break;

        // --- Settings Management ---
        case 'getSettings':
            $response['success'] = true;
            $response['data'] = $settingsDAO->getAllSettings();
            break;
        
        case 'updateSettings':
            $settingsToUpdate = $_POST;
            unset($settingsToUpdate['action']); // Remove action from list

            $this->conn->begin_transaction();
            foreach ($settingsToUpdate as $key => $value) {
                $settingsDAO->updateSetting($key, $value);
            }
            $this->conn->commit();

            $response['success'] = true;
            $response['message'] = 'Settings updated successfully.';
            break;

        // --- Log Console ---
        case 'getLogs':
            $logs = $logDAO->getLogs(100);
            $response['success'] = true;
            $response['data'] = $logs;
            break;
            
        // --- Announcement ---
        case 'createAnnouncement':
            $title = $_POST['title'] ?? '';
            $message = $_POST['message'] ?? '';
            $priority = $_POST['priority'] ?? 'Normal';
            
            if (empty($title) || empty($message)) {
                throw new Exception("Title and message are required.");
            }
            
            $announcementDAO->createAnnouncement($adminId, $title, $message, $priority);
            $response['success'] = true;
            $response['message'] = 'Announcement published!';
            break;
            
        case 'getAllAnnouncements':
            $response['success'] = true;
            $response['data'] = $announcementDAO->getAllAnnouncements();
            break;

        default:
            throw new Exception("Invalid admin action specified.");
    }
} catch (Exception $e) {
    // Catch any errors and send them back as JSON
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>