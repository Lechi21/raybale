<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Verify reCAPTCHA
    $recaptcha_secret = "6Lft7CUsAAAAAP616hwC6MkJvCrmAs-QICK5hIo3";
    $recaptcha_response = $_POST['g-recaptcha-response'];

    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}");
    $captcha_success = json_decode($verify);

    if (!$captcha_success->success) {
        echo "Captcha verification failed. Please try again.";
        exit;
    }

    // 2. Get form data
    $name = $_POST['YourName'];
    $email = $_POST['YourEmail'];
    $phone = $_POST['PhoneNumber'];
    $containerSize = $_POST['ContainerSize'];
    $commodityType = $_POST['CommodityType'];
    $transportType = $_POST['TransportType'];
    $railRamp = $_POST['RailRamp'];
    $zip = $_POST['DestinationZIP'];
    $containerNum = $_POST['ContainerNumber'];

    // 3. Build email
    $to = "dakenny21@gmail.com";
    $subject = "New Drayage Quote Request";
    $message = "
        Name: $name
        Email: $email
        Phone: $phone

        Container Size: $containerSize
        Commodity Type: $commodityType
        Transport Type: $transportType
        Rail Ramp: $railRamp
        Destination ZIP: $zip
        Container #: $containerNum
    ";

    $headers = "From: no-reply@raybale.com\r\n";

    // 4. Send email
    if (mail($to, $subject, $message, $headers)) {
        echo "SUCCESS";
    } else {
        echo "FAILED";
    }
}
?>
