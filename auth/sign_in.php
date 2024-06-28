<?php
require '../vendor/autoload.php';
include '../connect.php';
include '../core/functions/filter_request.php';
include '../core/functions/utills_functions.php';
include '../core/functions/verification_functions.php';

$userEmail    = FilterRequest::postRequest('userEmail');
$userPassword = FilterRequest::postRequest('userPassword');
$userVerifyCode = generateVerificationCode(5);
$currentTime = date("Y-m-d H:i:s");

// Validate if email and password are not empty
if (empty($userEmail) || empty($userPassword)) {
    http_response_code(400); // Bad Request
    printState("fail", "Fill in required fields");
    exit();
}

// Validate email format
if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); // Bad Request
    printState("fail", "Invalid email format");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM user WHERE `user_email` = ?");

try {
    $stmt->execute([$userEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($userPassword, $user['user_password'])) {
        http_response_code(401); // Unauthorized
        printState("fail", "Incorrect email or password");
        exit();
    }

    if ($user['user_approved'] == 0) {
        // Insert verification code into the verification_codes table
        $verifyStmt = $conn->prepare("INSERT INTO `verification_codes` 
            (`user_id`, `verifycode`, `verifycode_generated_at`) 
              VALUES (?, ?, ?)");
        if (!$verifyStmt->execute([
            $user['user_id'],
            $userVerifyCode, $currentTime
        ])) {
            printState("error", "Failed to save verification code.");
            exit();
        }
        // Send the verification code to the user's email
        sendVerificationEmail($userEmail, $userVerifyCode);
    }

    // appropriate security headers
    header("Content-Security-Policy: default-src 'self'");
    http_response_code(200); // OK
    printState("success", $user);
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    printState("error", $e->getMessage());
}
