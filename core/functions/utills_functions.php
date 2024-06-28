<?php

// print either fail or success with message
function printState($status, $message)
{
    // Set header to indicate JSON response
    header('Content-Type: application/json');
    echo json_encode(array(
        "status" => $status,
        "response" => $message
    ));
}
