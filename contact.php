<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Use PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// ===== NAME CHEAP PRIVATE EMAIL CONFIGURATION =====
// Using Private Email servers for ALL emails
define('PRIVATE_EMAIL_HOST', 'mail.privateemail.com');
define('PRIVATE_EMAIL_PORT', 587);
define('PRIVATE_EMAIL_SECURE', PHPMailer::ENCRYPTION_STARTTLS);

// Email addresses
define('ADMIN_EMAIL', 'trucking@raybale.com');    // Where form submissions go
define('SITE_NAME', 'Raybale.com');
define('SENDER_EMAIL', 'form@raybale.com');       // Alias - shown as "From" address
define('SENDER_NAME', 'Raybale Contact Form');

// === IMPORTANT FIX: USE MAIN MAILBOX CREDENTIALS ===
// You MUST use trucking@raybale.com credentials for SMTP
// Namecheap said aliases cannot authenticate with SMTP
define('SMTP_USERNAME', 'trucking@raybale.com');  // Your MAIN mailbox
define('SMTP_PASSWORD', 'Kingsley$27');      // Password for trucking@raybale.com

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // === 1. VALIDATION ===
    $errors = [];
    $required = ['YourName', 'YourEmail', 'PhoneNumber', 'Message'];
    
    foreach ($required as $field) {
        if (empty(trim($_POST[$field] ?? ''))) {
            $fieldName = str_replace(['Your', 'Number'], '', $field);
            $errors[] = ucfirst($fieldName) . " is required.";
        }
    }
    
    if (!empty($errors)) {
        echo implode("<br>", $errors);
        exit;
    }
    
    // Sanitize
    $name = trim(htmlspecialchars($_POST['YourName']));
    $email = filter_var($_POST['YourEmail'], FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars($_POST['PhoneNumber']);
    $message = trim(htmlspecialchars($_POST['Message']));
    
    // Validate
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email address.";
        exit;
    }
    
    // === 2. CREATE EMAIL CONTENT ===
    $subject = "Contact from " . substr($name, 0, 20) . " - " . SITE_NAME;
    
    $plainMessage = "NEW CONTACT FORM SUBMISSION
=================================
Date: " . date('F j, Y') . "

Name: $name
Email: $email
Phone: $phone

Message:
$message

---
Submitted via " . SITE_NAME . " contact form";

    $htmlMessage = '
    <!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="margin:0; padding:0; font-family:Arial,sans-serif;">
    <div style="max-width:600px; margin:auto; background:#fff; border:1px solid #ddd; border-radius:8px; padding:20px;">
        <div style="background:#f8f9fa; padding:15px; border-radius:6px; margin-bottom:20px;">
            <h2 style="margin:0; color:#2c3e50;">📧 New Contact Message</h2>
            <p style="margin:5px 0 0 0; color:#555;">' . date('F j, Y') . '</p>
        </div>
        
        <div style="padding:15px; background:#f8f9fa; border-radius:6px; margin-bottom:20px;">
            <h3 style="margin:0 0 10px 0; color:#2c3e50;">👤 Contact Details</h3>
            <p><strong>Name:</strong> ' . $name . '</p>
            <p><strong>Email:</strong> <a href="mailto:' . $email . '">' . $email . '</a></p>
            <p><strong>Phone:</strong> <a href="tel:' . $phone . '">' . $phone . '</a></p>
        </div>
        
        <div style="padding:15px; background:#f8f9fa; border-radius:6px;">
            <h3 style="margin:0 0 10px 0; color:#2c3e50;">💬 Message</h3>
            <div style="background:#fff; padding:15px; border-radius:4px; border:1px solid #eee;">
                ' . nl2br($message) . '
            </div>
        </div>
        
        <div style="margin-top:25px; padding-top:15px; border-top:1px solid #ddd; font-size:12px; color:#777;">
            <p>Submitted via ' . SITE_NAME . ' contact form</p>
            <p>Reply to: ' . $name . ' &lt;' . $email . '&gt;</p>
        </div>
    </div>
    </body>
    </html>';
    
    // === 3. SEND MAIN NOTIFICATION EMAIL ===
    $mail = new PHPMailer(true);
    
    try {
        // Use PRIVATE EMAIL settings
        $mail->isSMTP();
        $mail->Host       = PRIVATE_EMAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;      // FIXED: Use main mailbox for SMTP login
        $mail->Password   = SMTP_PASSWORD;      // Password for trucking@raybale.com
        $mail->SMTPSecure = PRIVATE_EMAIL_SECURE;
        $mail->Port       = PRIVATE_EMAIL_PORT;
        
        // Sender/Recipient
        $mail->setFrom(SENDER_EMAIL, SENDER_NAME); // Still shows as from form@raybale.com
        $mail->addAddress(ADMIN_EMAIL);           // Goes to trucking@raybale.com
        $mail->addReplyTo($email, $name);         // So admin can reply to submitter
        
        // Optional BCC
        $mail->addBCC('Thankgodoboh@gmail.com');
        $mail->addBCC('dakenny21@gmail.com');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlMessage;
        $mail->AltBody = $plainMessage;
        $mail->CharSet = 'UTF-8';
        
        // Optional debug - uncomment if emails aren't sending
        // $mail->SMTPDebug = 2;
        
        if ($mail->send()) {
            // Send confirmation to submitter
            sendConfirmationEmail($email, $name);
            echo "SUCCESS";
        } else {
            echo "Failed to send notification. Please try again.";
        }
        
    } catch (Exception $e) {
        error_log("[" . date('Y-m-d H:i:s') . "] Main email error: " . $e->getMessage());
        echo "An error occurred. Please email us directly at " . ADMIN_EMAIL;
    }
    
} else {
    echo "Invalid request method.";
    exit;
}

// === CONFIRMATION EMAIL FUNCTION ===
function sendConfirmationEmail($toEmail, $toName) {
    $confirmation = new PHPMailer(true);
    
    try {
        // USE SAME PRIVATE EMAIL SETTINGS
        $confirmation->isSMTP();
        $confirmation->Host       = PRIVATE_EMAIL_HOST;
        $confirmation->SMTPAuth   = true;
        $confirmation->Username   = SMTP_USERNAME;      // FIXED: Use main mailbox
        $confirmation->Password   = SMTP_PASSWORD;      // FIXED: Use main mailbox password
        $confirmation->SMTPSecure = PRIVATE_EMAIL_SECURE;
        $confirmation->Port       = PRIVATE_EMAIL_PORT;
        
        $confirmation->setFrom(SENDER_EMAIL, SITE_NAME);
        $confirmation->addAddress($toEmail, $toName);
        $confirmation->addReplyTo(ADMIN_EMAIL, SITE_NAME . ' Team');
        
        $confirmation->isHTML(true);
        $confirmation->Subject = 'Thank you for contacting ' . SITE_NAME;
        
        $confirmationBody = '
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="margin:0; padding:0; font-family:Arial,sans-serif;">
        <div style="max-width:600px; margin:auto; background:#fff; border:1px solid #ddd; border-radius:8px; padding:25px;">
            <div style="background:#f0f7ff; padding:20px; border-radius:6px; text-align:center; margin-bottom:25px;">
                <h2 style="margin:0; color:#2c3e50;">✅ Message Received!</h2>
            </div>
            
            <p>Dear ' . $toName . ',</p>
            <p>Thank you for contacting <strong>' . SITE_NAME . '</strong>. We have received your message and will respond within 24-48 hours.</p>
            
            <div style="background:#f8f9fa; padding:15px; border-radius:6px; margin:20px 0;">
                <p style="margin:0; color:#555;"><strong>What to expect:</strong></p>
                <ul style="margin:10px 0 0 20px; color:#555;">
                    <li>Our team reviews messages daily</li>
                    <li>Response time: 24-48 hours</li>
                    <li>For urgent matters, please call us</li>
                </ul>
            </div>
            
            <div style="margin-top:30px; padding-top:20px; border-top:1px solid #eee; text-align:center; color:#666;">
                <p style="margin:0;"><strong>' . SITE_NAME . ' Team</strong></p>
                <p style="margin:5px 0 0 0; font-size:12px;">This is an automated confirmation.</p>
            </div>
        </div>
        </body>
        </html>';
        
        $confirmation->Body = $confirmationBody;
        $confirmation->AltBody = "Thank you for contacting " . SITE_NAME . ". We have received your message and will respond within 24-48 hours.";
        $confirmation->CharSet = 'UTF-8';
        
        $confirmation->send();
        
    } catch (Exception $e) {
        error_log("Confirmation email failed to $toEmail: " . $e->getMessage());
    }
}
?>