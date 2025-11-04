<?php
// Library/php/services/NotificationService.php

// 1. Include the Composer autoloader (Corrected Path)
require_once __DIR__ . '/../../../vendor/autoload.php';

// 2. Import ALL required classes
use AndroidSmsGateway\Client;
use AndroidSmsGateway\Domain\MessageBuilder;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class NotificationService {
    
    private $gatewayClient;
    private $mailer; // For PHPMailer

    public function __construct() {
        
        // --- Configure for SMS Gateway App ---
        $login = 'Library'; 
        $password = 'Qwerty123'; 
        $serverUrl = 'http://192.168.1.10:8080'; // <-- Change this

        try {
            $this->gatewayClient = new Client($login, $password, $serverUrl);
        } catch (Exception $e) {
            error_log("Failed to connect to SMS Gateway: " . $e->getMessage());
            $this->gatewayClient = null;
        }
        
        // --- Configure for PHPMailer (Email) ---
        try {
            $this->mailer = new PHPMailer(true);
            
            //Server settings
            // ...
            $this->mailer->isSMTP();
            $this->mailer->Host       = 'smtp.gmail.com';
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = getenv('SMTP_USER'); // <-- USE getenv()
            $this->mailer->Password   = getenv('SMTP_PASS'); // <-- USE getenv()
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $this->mailer->Port       = 465;

            //Set sender
            $this->mailer->setFrom('UCCLibraryManagement@gmail.com', 'UCC Library Admin');

        } catch (Exception $e) {
            error_log("PHPMailer could not be configured: " . $e->getMessage());
            $this->mailer = null;
        }
    }

    /**
     * Sends an SMS using the Android Gateway app.
     */
    public function sendSms($toPhoneNumber, $messageBody) {
        if (!$this->gatewayClient) {
            throw new Exception("SMS Gateway is not configured or failed to connect.");
        }
        // ... (SMS sending code from before) ...
        try {
            $message = (new MessageBuilder($messageBody, [$toPhoneNumber]));
            $this->gatewayClient->messages->sendMessage($message);
            return true;
        } catch (Exception $e) {
            error_log("SMS Gateway Error: " . $e->getMessage());
            throw new Exception("SMS Gateway Error: " . $e->getMessage());
        }
    }
    
    /**
     * Sends an Email using PHPMailer and Gmail.
     */
    public function sendEmail($toEmail, $subject, $body) {
        if (!$this->mailer) {
            throw new Exception("PHPMailer is not configured.");
        }

        try {
            //Recipients
            $this->mailer->addAddress($toEmail); 

            //Content
            $this->mailer->isHTML(false); // Set to true if you want to send HTML
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;

            $this->mailer->send();
            
            // Clear addresses for the next email in the loop
            $this->mailer->clearAddresses();
            return true;

        } catch (Exception $e) {
            error_log("Email could not be sent. Mailer Error: {$this->mailer->ErrorInfo}");
            throw new Exception("Email could not be sent. Mailer Error: {$this->mailer->ErrorInfo}");
        }
    }
}
?>