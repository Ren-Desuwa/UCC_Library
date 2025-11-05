<?php
// Use the PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// --- Load the PHPMailer files ---
require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';

class MailService {

    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true); // Enable exceptions
        $this->configure();
    }

    /**
     * Configures the PHPMailer instance to use Gmail SMTP
     */
    private function configure() {
        try {
            // --- Server Settings ---
            $this->mailer->isSMTP();                                  // Set mailer to use SMTP
            $this->mailer->Host       = 'smtp.gmail.com';             // Specify main SMTP server
            $this->mailer->SMTPAuth   = true;                         // Enable SMTP authentication
            
            // === VITAL STEP ===
            // 1. Put your full Gmail address here
            $this->mailer->Username   = 'camachokyle13@gmail.com';     // <<< YOUR_EMAIL_HERE
            
            // 2. Put your 16-character App Password here
            $this->mailer->Password   = 'oahu wxvf dbnb ywxv';         // <<< YOUR_APP_PASSWORD_HERE 
            // === END VITAL STEP ===

            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;    // Enable implicit TLS encryption
            $this->mailer->Port       = 465;                          // TCP port to connect to (465 for SSL, 587 for TLS)

            // --- Sender Info ---
            // This is the "From" address (must be the same as your Username)
            $this->mailer->setFrom('your.email@gmail.com', 'Library MS Admin'); // <<< YOUR_EMAIL_HERE 
            
        } catch (Exception $e) {
            // Handle configuration errors
            throw new Exception("MailService configuration failed: " . $this->mailer->ErrorInfo);
        }
    }

    /**
     * Sends the password reset OTP email.
     * @param string $toEmail The user's email address.
     * @param string $otp The 6-digit code.
     * @param string $name The user's name (optional).
     */
    public function sendPasswordResetOtp($toEmail, $otp, $name = 'Student') {
        try {
            // --- Recipients ---
            $this->mailer->addAddress($toEmail, $name);     // Add a recipient

            // --- Content ---
            $this->mailer->isHTML(true);                                  // Set email format to HTML
            $this->mailer->Subject = 'Your Password Reset Code for Library MS';
            
            // This is the HTML body of the email
            $this->mailer->Body    = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                    <h2>Password Reset Request</h2>
                    <p>Hello $name,</p>
                    <p>We received a request to reset your password for the Library Management System.</p>
                    <p>Your One-Time Password (OTP) is:</p>
                    <h1 style='font-size: 42px; letter-spacing: 5px; margin: 20px 0; color: #A03A3A;'>
                        $otp
                    </h1>
                    <p>This code will expire in 15 minutes.</p>
                    <p>If you did not request this, please ignore this email.</p>
                    <br>
                    <p>Thank you,</p>
                    <p>The Library MS Team</p>
                </div>";

            // This is the plain-text version for non-HTML email clients
            $this->mailer->AltBody = "Hello $name,\n\nYour password reset code is: $otp\nThis code will expire in 15 minutes.\nIf you did not request this, please ignore this email.";

            $this->mailer->send();
            return true; // Email sent successfully
            
        } catch (Exception $e) {
            // Handle sending errors
            throw new Exception("Message could not be sent. Mailer Error: " . $this->mailer->ErrorInfo);
        }
    }
}
?>