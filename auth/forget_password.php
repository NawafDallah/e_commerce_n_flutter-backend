<?php
require '../vendor/autoload.php';
include '../connect.php';
include '../core/functions/filter_request.php';
include '../core/functions/verification_functions.php';
include '../core/functions/utills_functions.php';

// Get user email from POST request
$userEmail = FilterRequest::postRequest('userEmail');
$userVerifyCode = generateVerificationCode(5);
$currentTime = date("Y-m-d H:i:s");

// Check if the email is empty
if (empty($userEmail)) {
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

try {
    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT * FROM `user` WHERE `user_email` = ?");
    $stmt->execute([$userEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404); // Not Found
        printState("fail", "Email does not exist");
        exit();
    }

    // Remove any existing verification codes for this user
    $deleteStmt = $conn->prepare("DELETE FROM `verification_codes` 
    WHERE `user_id` = ?");
    if ($deleteStmt->execute([$user['user_id']])) {
        // Insert a new verification code
        $insertStmt = $conn->prepare(
            "INSERT INTO `verification_codes` 
            (`user_id`, `verifycode`, `verifycode_generated_at`) 
             VALUES (?, ?, ?)"
        );
        $insertStmt->execute([$user['user_id'], $userVerifyCode, $currentTime]);

        if ($insertStmt->rowCount() > 0) {
            // Send the verification code to the user's email
            sendVerificationEmail($userEmail, $userVerifyCode);
            http_response_code(200); // OK
            printState("success", "Verification code has been sent to your email");
        } else {
            http_response_code(500); // Internal Server Error
            printState("fail", "Failed to send verification code");
        }
    } else {
        http_response_code(500); // Internal Server Error
        printState("fail", "Failed to remove existing verification codes");
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    printState("error", $e->getMessage());
}
