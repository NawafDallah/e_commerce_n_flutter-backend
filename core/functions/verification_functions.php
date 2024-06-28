<?php
// help us to send verification code by local server
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail($email, $code)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username = 'nawafdallah@gmail.com'; // SMTP username
        $mail->Password = 'sbsk qpas vrcz pdok'; // SMTP password
        $mail->SMTPSecure = 'tls'; // Enable TLS encryption
        $mail->Port = 587; // TCP port to connect to

        // Recipients
        $mail->setFrom('nawafdallah@gmail.com', 'E-commerce Nawaf');
        $mail->addAddress($email); // Add a recipient

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = 'Verification Code of E-commerce-nawaf';
        $mail->Body    = 'Your verification code is: ' . $code;

        $mail->send();
    } catch (Exception $e) {
        echo "\nMessage could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}


function generateVerificationCode($length = 5)
{   // Generates a random 5-digit number
    return rand(pow(10, $length - 1), pow(10, $length) - 1);
}
