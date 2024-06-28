<?php
include '../connect.php';
include '../core/functions/filter_request.php';
include '../core/functions/utills_functions.php';

// Get user input from POST request
$userEmail = FilterRequest::postRequest('userEmail');
$newPassword = FilterRequest::postRequest('newPassword');
$confirmPassword = FilterRequest::postRequest('confirmPassword');

// Check if passwords are provided
if (empty($newPassword) || empty($confirmPassword)) {
    http_response_code(400); // Bad Request
    printState("fail", "Fill in required fields");
    exit();
}

// Check if passwords match
if ($newPassword !== $confirmPassword) {
    http_response_code(400); // Bad Request
    printState("fail", "Passwords do not match");
    exit();
}

// Hash the new password
$hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$updateStmt = $conn->prepare("UPDATE `user` SET `user_password` = ? 
WHERE `user_email` = ?");

try {
    $updateStmt->execute([$hashedNewPassword, $userEmail]);

    if ($updateStmt->rowCount() > 0) {
        http_response_code(200); // OK
        printState("success", "Password has been reset successfully");
    } else {
        http_response_code(500); // Internal Server Error
        printState("fail", "Failed to reset password");
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    printState("error", $e->getMessage());
}
