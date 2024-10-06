<?php

// Connect to the database
$conn = new mysqli("localhost", "root", "", "klasemo");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch subjects from the Subject table
$sql = "SELECT subjectID, subject_name FROM Subject";
$result = $conn->query($sql);

$subjects = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}

// Close database connection
$conn->close();

// Send JSON response
header('Content-Type: application/json');
echo json_encode($subjects);