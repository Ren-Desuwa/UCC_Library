<?php
// Library/php/cron/send_reminders.php

// This script is meant to be run by a server, not a user.
// It connects directly to the database and services.

// --- CORRECTED PATHS ---
require_once __DIR__ . '/../db_connect.php'; // This path is correct
require_once __DIR__ . '/../services/NotificationService.php'; // This path is correct
require_once __DIR__ . '/../models/NotificationDAO.php'; // This path is correct
// --- END CORRECTIONS ---


echo "--- Starting Overdue Reminder Cron Job --- \n";

// Initialize services
$notificationService = new NotificationService();
$notificationDAO = new NotificationDAO($conn); //

// 1. Find all overdue transactions that haven't been notified today
$sql = "SELECT t.transaction_id, t.account_id, a.name, a.email, a.contact_number, b.title
        FROM transactions t
        JOIN accounts a ON t.account_id = a.account_id
        JOIN book_copies c ON t.copy_id = c.copy_id
        JOIN books b ON c.book_id = b.book_id
        WHERE t.status = 'Overdue'
        AND NOT EXISTS (
            -- Don't send if a reminder was already sent in the last 24 hours
            SELECT 1 FROM notifications n
            WHERE n.transaction_id = t.transaction_id
              AND n.notification_type = 1 -- (1 = OverdueReminder)
              AND n.date_sent > NOW() - INTERVAL 24 HOUR
        )";

$result = $conn->query($sql);
if (!$result) {
    echo "DB Error: " . $conn->error . "\n";
    exit;
}

$overdueTransactions = $result->fetch_all(MYSQLI_ASSOC);
$sentCount = 0;

if (empty($overdueTransactions)) {
    echo "No overdue transactions found that need reminding. \n";
}

// 2. Loop through them and send notifications
foreach ($overdueTransactions as $t) {
    $message = "imLibrary Reminder: Hi " . $t['name'] . ", your book '" . $t['title'] . "' is overdue. Please return it as soon as possible to avoid further fines.";

    try {
        // YOUR LOGIC: If it's overdue AND they have a phone number, send SMS
        if (!empty($t['contact_number'])) {
            
            echo "Sending SMS to " . $t['name'] . " (" . $t['contact_number'] . ")... \n";
            $notificationService->sendSms($t['contact_number'], $message);
        
        } 
        // Otherwise, send an email (if you set it up)
        else if (!empty($t['email'])) {
            
            echo "Sending Email to " . $t['name'] . " (" . $t['email'] . ")... \n";
            $notificationService->sendEmail($t['email'], "Your Book is Overdue", $message);
        }

        // 3. Log that we sent the notification so we don't spam them
        $notificationDAO->createNotification($t['account_id'], $message, 1, $t['transaction_id']); // 1 = OverdueReminder
        $sentCount++;

    } catch (Exception $e) {
        echo "!! FAILED to send notification for transaction #" . $t['transaction_id'] . ": " . $e->getMessage() . "\n";
    }
}

echo "--- Cron Job Finished. Sent $sentCount reminders. --- \n";
?>