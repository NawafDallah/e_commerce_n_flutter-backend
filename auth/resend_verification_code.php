<?php
// help us to send verification code by local server
require '../vendor/autoload.php';

include '../connect.php';
include '../core/functions/filter_request.php';
include '../core/functions/verification_functions.php';
include '../core/functions/utills_functions.php';

$userEmail = FilterRequest::postRequest('userEmail');
$userVerifyCode = generateVerificationCode(5);
$currentTime = date("Y-m-d H:i:s");

$stmt = $conn->prepare("SELECT * FROM `user` WHERE `user_email` = ?");

try {
    $stmt->execute([$userEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $deleteStmt = $conn->prepare(
        "DELETE FROM `verification_codes` WHERE `user_id` = ?"
    );
    if ($deleteStmt->execute([$user['user_id']])) {
        $insertStmt = $conn->prepare(
            "INSERT INTO `verification_codes`
            (`user_id`, `verifycode`, `verifycode_generated_at`) 
              VALUES (?, ?, ?)"
        );
        $insertStmt->execute([$user['user_id'], $userVerifyCode, $currentTime]);
        if ($insertStmt->rowCount() > 0) {
            sendVerificationEmail($userEmail, $userVerifyCode);
            http_response_code(200); // OK
            printState("success", "Verification code has been sent to your email");
        } else {
            http_response_code(500); // Internal Server Error
            printState("fail", "Failed to send verification code");
        }
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    printState("error", $e->getMessage());
}
