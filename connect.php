<?php

include "../core/functions/check_authinticate.php";

$servername = "localhost";
$dbName = "mysql:host=$servername;dbname=e_commerce_n_flutter";
$username = "root";
$password = "";
$option = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8");

try {
    $conn = new PDO($dbName, $username, $password, $option);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, Access-Control-Allow-Origin");
    header("Access-Control-Allow-Methods: POST, OPTIONS , GET");
    checkAuthenticate();
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
