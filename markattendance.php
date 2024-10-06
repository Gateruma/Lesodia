<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the configuration file
require_once('config.php');

// Connect to the database
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to mark attendance and submit data to the database
function markAttendance($conn, $studentId, $status, $classID) {
    // Get subjectID from the query parameter
    $subjectId = isset($_GET['subjectID']) ? $_GET['subjectID'] : 1; // Default to 1 if not provided
    $date = date("Y-m-d"); // Current date

    // Check if attendance already exists
    $checkStmt = $conn->prepare("SELECT * FROM attendance WHERE studentID = ? AND subjectid = ? AND classID = ?");
    $checkStmt->bind_param("iii", $studentId, $subjectId, $classID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // If attendance exists, delete the record (undo)
        $deleteStmt = $conn->prepare("DELETE FROM attendance WHERE studentID = ? AND subjectid = ? AND classID = ?");
        $deleteStmt->bind_param("iii", $studentId, $subjectId, $classID);
        $deleteStmt->execute();

        if ($deleteStmt->affected_rows > 0) {
            echo "Attendance undone successfully for userID: " . $studentId;
        } else {
            echo "Error undoing attendance for userID: " . $studentId;
        }

        $deleteStmt->close();
    } else {
        // If attendance doesn't exist, insert the new record
        $insertSql = "INSERT INTO attendance (studentID, subjectid, date, status, classID) VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);

        // Use bind_param to bind variables and prevent SQL injection
        $insertStmt->bind_param("iissi", $studentId, $subjectId, $date, $status, $classID);
        $insertStmt->execute();

        if ($insertStmt->affected_rows > 0) {
            echo "Attendance marked successfully for userID: " . $studentId;
        } else {
            echo "Error marking attendance for userID: " . $studentId;
        }

        $insertStmt->close();
    }

    // Close the prepared statement
    $checkStmt->close();
}

// Check if the studentId, status, and classID are set in the POST request
if (isset($_POST['studentId'], $_POST['status'], $_POST['classID'])) {
    // Use intval to ensure $studentId is an integer and prevent SQL injection
    $studentId = intval($_POST['studentId']);
    $status = $_POST['status'];
    $classID = intval($_POST['classID']);

    // Call the markAttendance function
    markAttendance($conn, $studentId, $status, $classID);
} else {
    echo "Invalid request. Please provide studentId, status, and classID.";
}

// Close the database connection
$conn->close();
?>
