<?php
include '../connect.php';
include '../core/functions/filter_request.php';
include '../core/functions/utills_functions.php';

$bannerStmt = $conn->prepare("SELECT * FROM `banner`");
$catigoriesStmt = $conn->prepare("SELECT * FROM `catigory`");
$featuredProduct = $conn->prepare("SELECT * FROM `item` WHERE `item_type` = \"featured\" ORDER BY item_descount DESC");
$popularProduct = $conn->prepare("SELECT * FROM `item` WHERE `item_type` = \"popular\"");

$allData = array();


try {
    $bannerStmt->execute();
    $banners = $bannerStmt->fetchAll(PDO::FETCH_ASSOC);
    $catigoriesStmt->execute();
    $catigory = $catigoriesStmt->fetchAll(PDO::FETCH_ASSOC);
    $featuredProduct->execute();
    $featured = $featuredProduct->fetchAll(PDO::FETCH_ASSOC);
    $popularProduct->execute();
    $popular = $popularProduct->fetchAll(PDO::FETCH_ASSOC);

    if ($bannerStmt->rowCount() > 0 && $catigoriesStmt->rowCount() > 0) {
        header("Content-Security-Policy: default-src 'self'");
        http_response_code(200); // OK
        $allData["catigories"] = $catigory;
        $allData["banners"] = $banners;
        $allData["featured"] = $featured;
        $allData["popular"] = $popular;
        printState("success", $allData);
    } else {
        http_response_code(404); // Internal Server Error
        printState("fail", "Somthing went wrong");
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    printState("error", $e->getMessage());
}
