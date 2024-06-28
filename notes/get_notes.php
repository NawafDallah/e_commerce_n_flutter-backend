<?php

include '../connect.php';
include '../core/functions/filter_request.php';


$userId = FilterRequest::getRequest('user_id');

$stmt = $conn->prepare("SELECT `notes_title`, `notes_content`, `notes_image` 
FROM notes WHERE `notes_user` = ?");

try {
    $stmt->execute([$userId]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($notes) {
        // appropriate security headers
        header("Content-Security-Policy: default-src 'self'");
        echo json_encode([
            "status" => "success",
            "notes" => $notes
        ]);
    } else {
        echo json_encode([
            "status" => "fail",
            "message" => "No notes found for the user"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(array("status" => "error", "message" => $e->getMessage()));
}
