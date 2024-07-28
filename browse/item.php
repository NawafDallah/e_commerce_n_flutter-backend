<?php
include '../connect.php';
include '../core/functions/filter_request.php';
include '../core/functions/utills_functions.php';

// Get parameters from request
$categoryId = FilterRequest::getRequest('categoryId');
$currentPage = FilterRequest::getRequest('page');
$itemsPerPage = FilterRequest::getRequest('itemsPerPage');


$offset = ($currentPage - 1) * $itemsPerPage;

$itemsStmt = $conn->prepare("SELECT * FROM `item` WHERE `catogary_id` = :categoryId LIMIT :limit OFFSET :offset");

try {

    $itemsStmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
    $itemsStmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
    $itemsStmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    
    $itemsStmt->execute();
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    if ($itemsStmt->rowCount() > 0) {
        header("Content-Security-Policy: default-src 'self'");
        http_response_code(200); // OK
        printState("success", $items);
    } else {
        http_response_code(404); // Not Found
        printState("fail", "No items found");
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    printState("error", $e->getMessage());
}
