<?php

include '../connect.php';
include '../core/functions/filter_request.php';
include '../core/functions/file_request.php';


$notes_title = FilterRequest::postRequest('notes_title');
$notes_content   = FilterRequest::postRequest('notes_content');
$notes_id = FilterRequest::getRequest('notes_id');
$notes_image = FilterRequest::getRequest('notes_image');


if (isset($_FILES['file'])) {
    FileRequest::deleteFile("../uploads", $notes_image);
    $notes_image = FileRequest::uploadeFile('file');
}


// Validate if title and content are not empty
if (empty($notes_title) || empty($notes_content)) {
    echo json_encode(array(
        "status" => "fail",
        "message" => "Title and content are required"
    ));
    exit();
}

$stmt = $conn->prepare("UPDATE `notes` SET `notes_title`= ?,`notes_content`= ?,
`notes_image`= ? WHERE notes_id = ?");

try {
    $stmt->execute([$notes_title, $notes_content, $notes_image, $notes_id]);
    header("Content-Security-Policy: default-src 'self'");
    if ($stmt->rowCount() > 0) {
        echo json_encode(array(
            "status" => "success",
            "message" => "Note updated successfully"
        ));
    } else {
        echo json_encode(array(
            "status" => "fail",
            "message" => "Failed to update note"
        ));
    }
} catch (PDOException $e) {
    echo json_encode(array("status" => "error", "message" => $e->getMessage()));
}
