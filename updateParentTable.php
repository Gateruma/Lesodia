<?php
include('config.php');


// Get the values from the POST request
$requestId = $_POST['requestId'];
$parentId = $_POST['parentId'];
$studentId = $_POST['studentId'];

// Update the parent table with the student ID
$sqlUpdateParent = "UPDATE parent SET studentid = '$studentId' WHERE parentid = '$parentId'";

if ($conn->query($sqlUpdateParent) === TRUE) {
    // Update the request status to "approved"
    $sqlUpdateRequest = "UPDATE request SET status = 'approved' WHERE requestid = '$requestId'";
    if ($conn->query($sqlUpdateRequest) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating request status: " . $conn->error;
    }
} else {
    echo "Error updating parent record: " . $conn->error;
}

// Close the connection
$conn->close();
?>
