<?php
// USE STATEMENTS MUST BE AT THE VERY TOP
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Then load PHPMailer files
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

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
    
    // Option A: Plain Text Version (for isHTML(false))
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
    
    // Option B: HTML Version (for isHTML(true))
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
    
    // 4. Configure and send email
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'mail.raybale.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'no-reply@raybale.com';
        $mail->Password   = '9mm]Ny!tBjs4';
        $mail->SMTPSecure = false;
        $mail->SMTPAutoTLS = false;
        $mail->Port       = 587;
        
        // Alternative settings if above doesn't work:
        // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        // $mail->Port = 587;
        // OR
        // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        // $mail->Port = 465;
        
        // Sender/Recipient - CORRECTED
        $mail->setFrom('no-reply@raybale.com', 'Raybale Drayage Form');
        $mail->addAddress('dakenny21@gmail.com');
        // Add Reply-To so recipient can reply directly to the submitter
        $mail->addReplyTo($email, $name);
        
        // Optional: Add CC or BCC
        // $mail->addCC('someone@example.com');
        // $mail->addBCC('admin@example.com');
        
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlMessage;
        // Add plain text alternative for email clients that don't support HTML
        $mail->AltBody = $plainMessage;
        
        
        // For debugging - shows SMTP conversation
        // $mail->SMTPDebug = 2;
        
        if ($mail->send()) {
            echo "SUCCESS";
        } else {
            echo "FAILED to send email";
        }
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        // For more detailed error info during testing:
        // echo "Detailed error: " . $mail->ErrorInfo;
    }
} else {
    echo "Invalid request method.";
}
?>