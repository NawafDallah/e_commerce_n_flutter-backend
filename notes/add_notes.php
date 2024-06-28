<?php

include '../connect.php';
include '../core/functions/filter_request.php';
include '../core/functions/file_request.php';





$notes_title = FilterRequest::postRequest('notes_title');
$notes_content   = FilterRequest::postRequest('notes_content');
$notes_user = FilterRequest::getRequest('user_id');
$notes_image = FileRequest::uploadeFile('file');

if ($notes_image != 'fail' && !empty($notes_title) && !empty($notes_content)) {
    $stmt = $conn->prepare("INSERT INTO `notes`(
        `notes_title`, 
        `notes_content`, 
        `notes_user`,
        `notes_image`) 
VALUES (?, ?, ?, ?)");

    try {
        $stmt->execute([$notes_title, $notes_content, $notes_user, $notes_image]);
        header("Content-Security-Policy: default-src 'self'");
        if ($stmt->rowCount() > 0) {
            echo json_encode(array(
                "status" => "success",
                "message" => "Note added successfully"
            ));
        } else {
            echo json_encode(array(
                "status" => "fail",
                "message" => "Failed to add note"
            ));
        }
    } catch (PDOException $e) {
        echo json_encode(array("status" => "error", "message" => $e->getMessage()));
    }
} else {
    echo json_encode(array(
        "status" => "fail",
        "message" => "all fields are required"
    ));
}
