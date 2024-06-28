<?php
require '../vendor/autoload.php';
include '../connect.php';
include '../core/functions/filter_request.php';
include '../core/functions/utills_functions.php';
include '../core/functions/verification_functions.php';

// get user date from POST request 
$userName = FilterRequest::postRequest('userName');
$userEmail = FilterRequest::postRequest('userEmail');
$userPassword = FilterRequest::postRequest('userPassword');
$hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);
$userPhone = FilterRequest::postRequest('userPhone');
$userVerifyCode = generateVerificationCode(5);
$currentTime = date("Y-m-d H:i:s");

// Check if fields are empty
if (
    empty($userName) || empty($userEmail) ||
    empty($userPassword) || empty($userPhone)
) {
    http_response_code(400); // Bad Request
    printState("error", "Fill in required fields");
    exit();
}

// Validate email format
if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    printState("error", "Invalid email format");
    exit();
}

try {
    // Check if the email is already exists
    $emailCheckStmt = $conn->prepare("SELECT `user_id` FROM `user` WHERE `user_email` = ?");
    $emailCheckStmt->execute([$userEmail]);
    if ($emailCheckStmt->fetch()) {
        http_response_code(409); // Conflict
        printState("error", "email already exists");
        exit();
    }

    // Check if the phone is already exists
    $phoneCheckStmt = $conn->prepare("SELECT `user_id` FROM `user` WHERE `user_phone` = ?");
    $phoneCheckStmt->execute([$userPhone]);
    if ($phoneCheckStmt->fetch()) {
        http_response_code(409);
        printState("error", "phone already exists");
        exit();
    }

    // Insert user data into the database
    $conn->beginTransaction();

    $stmt = $conn->prepare("INSERT INTO `user` (`user_name`, `user_email`, 
    `user_password`, `user_phone`) VALUES (?, ?, ?, ?)");
    if (!$stmt->execute([$userName, $userEmail, $hashedPassword, $userPhone])) {
        printState("error", "Failed to create account.");
        exit();
    }

    // Retrieve the user_id of the newly created user
    $userStmt = $conn->prepare("SELECT `user_id` FROM `user` 
    WHERE `user_email` = ?");
    $userStmt->execute([$userEmail]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    // Insert verification code into the verification_codes table
    $verifyStmt = $conn->prepare("INSERT INTO `verification_codes` 
    (`user_id`, `verifycode`, `verifycode_generated_at`) VALUES (?, ?, ?)");
    if (!$verifyStmt->execute([$user['user_id'], $userVerifyCode, $currentTime])) {
        printState("error", "Failed to save verification code.");
        exit();
    }

    // Send verification email
    sendVerificationEmail($userEmail, $userVerifyCode);

    $conn->commit();

    // Set appropriate security headers
    header("Content-Security-Policy: default-src 'self'");
    http_response_code(201); // Created
    printState("success", "Account created successfully.");
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500); // Internal Server Error
    throw new Exception($e->getMessage());
}
