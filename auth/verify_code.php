<?php
include '../connect.php';
include '../core/functions/filter_request.php';
include '../core/functions/utills_functions.php';

$userEmail = FilterRequest::postRequest('userEmail');
$userVerifyCode = FilterRequest::postRequest('userVerifyCode');

// Check if the verification code is empty
if (empty($userVerifyCode)) {
    http_response_code(200); // Bad Request
    printState("fail", "Verification code is required");
    exit();
}

try {
    // Prepare and execute the query to fetch the verification code details
    $stmt = $conn->prepare(
        "SELECT u.user_id, u.user_approved, vc.verifycode, 
        vc.verifycode_generated_at
        FROM verification_codes vc
        INNER JOIN user u ON u.user_id = vc.user_id
        WHERE u.user_email = ? AND vc.verifycode = ?"
    );
    $stmt->execute([$userEmail, $userVerifyCode]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Check if the verification code is still valid (within 2 minutes)
        $codeGeneratedAt = new DateTime($user['verifycode_generated_at']);
        $currentTime = new DateTime();
        $interval = $currentTime->diff($codeGeneratedAt);

        if ($interval->i < 1 && $interval->h == 0) {
            // Approve user if not already approved
            if ($user['user_approved'] == 0) {
                $updateStmt = $conn->prepare("UPDATE user SET user_approved = 1 
                WHERE user_id = ?");
                $updateStmt->execute([$user['user_id']]);
            }

            // Remove the verification code entry
            $deleteStmt = $conn->prepare("DELETE FROM verification_codes 
            WHERE user_id = ?");
            $deleteStmt->execute([$user['user_id']]);

            http_response_code(200); // OK
            printState("success", "Your account has been verified");
        } else {
            // Verification code has expired, remove the entry
            $deleteStmt = $conn->prepare("DELETE FROM verification_codes 
            WHERE user_id = ?");
            $deleteStmt->execute([$user['user_id']]);

            http_response_code(410); // Gone
            printState("fail", "Verification code has expired");
        }
    } else {
        http_response_code(400); // Bad Request
        printState("fail", "Invalid verification code");
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    printState("error", $e->getMessage());
}
