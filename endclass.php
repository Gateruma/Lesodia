<?php
include('config.php');


// Retrieve data from the POST request
$subjectName = $_POST['subjectName'];
$startTime = $_POST['startTime'];
$endTime = $_POST['endTime'];

// Escape variables for security (to prevent SQL injection)
$subjectName = $conn->real_escape_string($subjectName);
$startTime = $conn->real_escape_string($startTime);
$endTime = $conn->real_escape_string($endTime);

// SQL query to update the end time in the class table
$sql = "INSERT INTO class (subject_name, start_time, end_time) 
        VALUES ('$subjectName', '$startTime', '$endTime')";

if ($conn->query($sql) === TRUE) {
    echo "Class ended successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close the database connection
$conn->close();
?>
