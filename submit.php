<?php
// USE STATEMENTS MUST BE AT THE VERY TOP
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Then load PHPMailer files
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// ===== NAME CHEAP PRIVATE EMAIL CONFIGURATION =====
define('PRIVATE_EMAIL_HOST', 'mail.privateemail.com');
define('PRIVATE_EMAIL_PORT', 587);
define('PRIVATE_EMAIL_SECURE', PHPMailer::ENCRYPTION_STARTTLS);

// Email addresses - UPDATED FOR PRIVATE EMAIL
define('ADMIN_EMAIL', 'trucking@raybale.com');      // Where quote requests go
define('SITE_NAME', 'Raybale.com');
define('SENDER_EMAIL', 'no-reply@raybale.com');         // Alias - shown as "From"
define('SENDER_NAME', 'Raybale Drayage Quote Form');

// IMPORTANT: Use main mailbox credentials for SMTP
define('SMTP_USERNAME', 'trucking@raybale.com');    // Your MAIN mailbox
define('SMTP_PASSWORD', 'Kingsley$27');             // Password for trucking@raybale.com

// Now continue with your code
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Verify reCAPTCHA
    $recaptcha_secret = "6Lft7CUsAAAAAP616hwC6MkJvCrmAs-QICK5hIo3";
    $recaptcha_response = $_POST['recaptcha_token'];
    
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}");
    $captcha_success = json_decode($verify);
    
    if (!$captcha_success->success) {
        echo "Captcha verification failed. Please try again.";
        exit;
    }
    
    // 2. Get form data
    $name = htmlspecialchars($_POST['YourName']);
    $email = filter_var($_POST['YourEmail'], FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars($_POST['PhoneNumber']);
    $containerSize = htmlspecialchars($_POST['ContainerSize']);
    $commodityType = htmlspecialchars($_POST['CommodityType']);
    $transportType = htmlspecialchars($_POST['TransportType']);
    $railRamp = htmlspecialchars($_POST['RailRamp']);
    $zip = htmlspecialchars($_POST['DestinationZIP']);
    $containerNum = htmlspecialchars($_POST['ContainerNumber']);
    
    // 3. Create email content - IMPROVED FORMAT
    $subject = "New Drayage Quote Request - " . date('m/d/Y');
    
    // Plain Text Version
    $plainMessage = "NEW DRAYAGE QUOTE REQUEST
=============================
Date: " . date('F j, Y') . "
    
CONTACT INFORMATION
---------------------------------------
Name: $name
Email: $email
Phone: $phone
    
SHIPMENT DETAILS
----------------------------
Container Size: $containerSize
Commodity Type: $commodityType
Transport Type: $transportType
Rail Ramp: $railRamp
Destination ZIP: $zip
Container #: " . ($containerNum ?: 'Not provided') . "
    

This quote request was submitted via Raybale.com Drayage Form
";
    
    // HTML Version
    $htmlMessage = '
    <!DOCTYPE html>
    <html>
    <body style="margin:0; padding:0; background:#f2f2f2; font-family:Arial, sans-serif; color:#333;">
    
    <div style="max-width:600px; margin:20px auto; background:#ffffff; 
    border:1px solid #ddd; border-radius:8px; padding:20px;">
    
        <!-- HEADER -->
        <div style="background:#f8f9fa; padding:15px; border-radius:6px; margin-bottom:20px;">
            <h2 style="margin:0; font-size:20px; color:#2c3e50;">📋 New Drayage Quote Request</h2>
            <p style="margin:5px 0 0 0; font-size:14px; color:#555;">
                Date: ' . date('F j, Y') . '
            </p>
        </div>
    
        <!-- CONTACT INFO -->
        <div style="padding:15px; background:#f8f9fa; border-radius:6px; margin-bottom:20px;">
            <h3 style="margin:0 0 10px 0; font-size:18px; color:#2c3e50;">👤 Contact Information</h3>
    
            <p style="margin:6px 0;"><strong>Name:</strong> ' . $name . '</p>
            <p style="margin:6px 0;"><strong>Email:</strong> 
                <a href="mailto:' . $email . '" style="color:#0066cc; text-decoration:none;">' . $email . '</a>
            </p>
            <p style="margin:6px 0;"><strong>Phone:</strong> 
                <a href="tel:' . $phone . '" style="color:#0066cc; text-decoration:none;">' . $phone . '</a>
            </p>
        </div>
    
        <!-- SHIPMENT DETAILS -->
        <div style="padding:15px; background:#f8f9fa; border-radius:6px;">
            <h3 style="margin:0 0 10px 0; font-size:18px; color:#2c3e50;">🚚 Shipment Details</h3>
    
            <p style="margin:6px 0;"><strong>Container Size:</strong> ' . $containerSize . '</p>
            <p style="margin:6px 0;"><strong>Commodity Type:</strong> ' . $commodityType . '</p>
            <p style="margin:6px 0;"><strong>Transport Type:</strong> ' . $transportType . '</p>
            <p style="margin:6px 0;"><strong>Rail Ramp:</strong> ' . $railRamp . '</p>
            <p style="margin:6px 0;"><strong>Destination ZIP:</strong> ' . $zip . '</p>
            <p style="margin:6px 0;"><strong>Container #:</strong> ' . ($containerNum ?: 'Not provided') . '</p>
        </div>
    
        <!-- FOOTER -->
        <div style="margin-top:25px; padding-top:15px; border-top:1px solid #ddd; font-size:12px; color:#777;">
            <p style="margin:0;">This quote request was submitted via Raybale.com Drayage Form</p>
            <p style="margin:5px 0 0 0;">You can reply directly to ' . $name . ' at ' . $email . '</p>
        </div>
    
    </div>
    
    </body>
    </html>
    ';
    
    // 4. Configure and send email with PRIVATE EMAIL
    $mail = new PHPMailer(true);
    
    try {
        // === PRIVATE EMAIL SERVER SETTINGS ===
        $mail->isSMTP();
        $mail->Host       = PRIVATE_EMAIL_HOST;        // mail.privateemail.com
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;             // trucking@raybale.com
        $mail->Password   = SMTP_PASSWORD;             // Your real password
        $mail->SMTPSecure = PRIVATE_EMAIL_SECURE;      // ENCRYPTION_STARTTLS
        $mail->Port       = PRIVATE_EMAIL_PORT;        // 587
        
        // Sender/Recipient - USING ALIAS WITH MAIN MAILBOX AUTH
        $mail->setFrom(SENDER_EMAIL, SENDER_NAME);      // Shows as from no-reply@raybale.com
        $mail->addAddress(ADMIN_EMAIL);                 // Goes to trucking@raybale.com
        $mail->addReplyTo($email, $name);               // So you can reply to submitter
        
        // BCC recipients
        $mail->addBCC('Thankgodoboh@gmail.com');
        $mail->addBCC('contact.insanjo@gmail.com');
        
        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlMessage;
        $mail->AltBody = $plainMessage;
        $mail->CharSet = 'UTF-8';
        
        // Optional debug - uncomment if emails aren't sending
        // $mail->SMTPDebug = 2;
        
        if ($mail->send()) {
            // Optional: Send confirmation to the submitter
            sendQuoteConfirmation($email, $name);
            echo "SUCCESS";
        } else {
            echo "Failed to send quote request. Please try again.";
        }
        
    } catch (Exception $e) {
        error_log("[" . date('Y-m-d H:i:s') . "] Quote email error: " . $e->getMessage());
        echo "An error occurred. Please call us directly for your quote.";
    }
} else {
    echo "Invalid request method.";
}

// === CONFIRMATION EMAIL FUNCTION FOR QUOTE REQUESTS ===
function sendQuoteConfirmation($toEmail, $toName) {
    $confirmation = new PHPMailer(true);
    
    try {
        // USE SAME PRIVATE EMAIL SETTINGS
        $confirmation->isSMTP();
        $confirmation->Host       = PRIVATE_EMAIL_HOST;
        $confirmation->SMTPAuth   = true;
        $confirmation->Username   = SMTP_USERNAME;
        $confirmation->Password   = SMTP_PASSWORD;
        $confirmation->SMTPSecure = PRIVATE_EMAIL_SECURE;
        $confirmation->Port       = PRIVATE_EMAIL_PORT;
        
        $confirmation->setFrom(SENDER_EMAIL, SITE_NAME . ' Drayage');
        $confirmation->addAddress($toEmail, $toName);
        $confirmation->addReplyTo(ADMIN_EMAIL, SITE_NAME . ' Drayage Team');
        
        $confirmation->isHTML(true);
        $confirmation->Subject = 'Your Drayage Quote Request - ' . SITE_NAME;
        
        $confirmationBody = '
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="margin:0; padding:0; font-family:Arial,sans-serif;">
        <div style="max-width:600px; margin:auto; background:#fff; border:1px solid #ddd; border-radius:8px; padding:25px;">
            <div style="background:#f0f7ff; padding:20px; border-radius:6px; text-align:center; margin-bottom:25px;">
                <h2 style="margin:0; color:#2c3e50;">✅ Quote Request Received!</h2>
            </div>
            
            <p>Dear ' . $toName . ',</p>
            <p>Thank you for requesting a drayage quote from <strong>' . SITE_NAME . '</strong>. Our logistics team has received your shipment details and will provide you with a competitive quote within 1 business day.</p>
            
            <div style="background:#f8f9fa; padding:15px; border-radius:6px; margin:20px 0;">
                <p style="margin:0; color:#555;"><strong>Next Steps:</strong></p>
                <ul style="margin:10px 0 0 20px; color:#555;">
                    <li>Our team will review your shipment requirements</li>
                    <li>We\'ll check availability and provide pricing</li>
                    <li>You\'ll receive our quote via email</li>
                    <li>For urgent requests, please call us directly</li>
                </ul>
            </div>
            
            <div style="margin-top:30px; padding-top:20px; border-top:1px solid #eee; text-align:center; color:#666;">
                <p style="margin:0;"><strong>' . SITE_NAME . ' Drayage Team</strong></p>
                <p style="margin:5px 0 0 0; font-size:12px;">Professional Midwest Drayage Solutions</p>
            </div>
        </div>
        </body>
        </html>';
        
        $confirmation->Body = $confirmationBody;
        $confirmation->AltBody = "Thank you for your drayage quote request. " . SITE_NAME . " will provide you with a competitive quote within 1 business day.";
        $confirmation->CharSet = 'UTF-8';
        
        $confirmation->send();
        
    } catch (Exception $e) {
        error_log("Quote confirmation failed to $toEmail: " . $e->getMessage());
    }
}
?>