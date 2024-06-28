<?php

include '../connect.php';
include '../core/functions/filter_request.php';
include '../core/functions/file_request.php';


$notes_id = FilterRequest::getRequest('notes_id');
$notes_image = FilterRequest::getRequest('notes_image');

$stmt = $conn->prepare("DELETE FROM `notes` WHERE notes_id = ?");

try {
    $stmt->execute([$notes_id]);
    header("Content-Security-Policy: default-src 'self'");
    if ($stmt->rowCount() > 0) {
        FileRequest::deleteFile("../uploads", $notes_image);
        echo json_encode(array(
            "status" => "success",
            "message" => "Note deleted successfully"
        ));
    } else {
        echo json_encode(array(
            "status" => "fail",
            "message" => "Failed to delete note"
        ));
    }
} catch (PDOException $e) {
    echo json_encode(array("status" => "error", "message" => $e->getMessage()));
}
