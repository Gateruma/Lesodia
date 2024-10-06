<?php
include('config.php');


$subjectName = $_POST['subjectName'];
$startTime = $_POST['startTime'];
$endTime = $_POST['endTime'];
$duration = $_POST['duration'];

$subjectName = $conn->real_escape_string($subjectName);
$startTime = $conn->real_escape_string($startTime);
$endTime = $conn->real_escape_string($endTime);
$duration = $conn->real_escape_string($duration);

$sql = "INSERT INTO class (subject_name, start_time, end_time, duration) 
        VALUES ('$subjectName', '$startTime', '$endTime', '$duration')";

if ($conn->query($sql) === TRUE) {
    echo "Class data saved successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
