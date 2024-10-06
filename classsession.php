<?php
// Start the session
session_start();

// Connect to the database
$conn = new mysqli("localhost", "root", "", "klasemo");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Replace with your actual way of storing user ID
$userID = $_SESSION['userID'];

// Fetch data from the subjects table based on user ID
$sql = "SELECT * FROM subjects WHERE userID = $userID";
$result = $conn->query($sql);

// Store the fetched data in the session
$_SESSION['subjects'] = $result->fetch_all(MYSQLI_ASSOC);

// Close the database connection
$conn->close();
?>
