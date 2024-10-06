<?php
session_start();
include('config.php');

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentID = $_POST['studentid'];
    $blockID = $_POST['blockid'];
    $image = $_POST['image'];

    // Get the current date
    $date = date("Y-m-d H:i:s");

    // Convert the base64 image to binary data
    $imageData = explode(',', $image);
    $imageData = base64_decode($imageData[1]);

    // Insert into response table
    $sql = "INSERT INTO response (studentID, blockid, date, file) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $studentID, $blockID, $date, $imageData);
    $stmt->execute();
    $stmt->close();

    http_response_code(200);
} else {
    http_response_code(405);
}
?>
