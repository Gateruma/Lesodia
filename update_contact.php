<?php
include('config.php');
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$tutorID = $_POST['tutorid'];
$newContactNumber = $_POST['contact_number'];

// Update the contact number in the tutor table
$sql_update_contact = "UPDATE tutor SET billinfo = ? WHERE tutorid = ?";
$stmt_update_contact = $conn->prepare($sql_update_contact);
$stmt_update_contact->bind_param("si", $newContactNumber, $tutorID);

if ($stmt_update_contact->execute()) {
    // Redirect back to the tutor dashboard or display success message
    header("Location: tutordashboard.php?contact_updated=true");
} else {
    // Handle error
    echo "Error updating contact number: " . $conn->error;
}

$stmt_update_contact->close();
$conn->close();
?>
