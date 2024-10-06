<?php
include('config.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = $_POST['userID'];
    $subjectID = $_POST['subjectID'];
    $date = $_POST['date'];
    $status = $_POST['status'];

    $sql = "INSERT INTO attendance (userID, subjectid, date, status) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die('Error in preparing the SQL statement: ' . $conn->error);
    }

    $stmt->bind_param("iiss", $userID, $subjectID, $date, $status);
    $stmt->execute();

    if ($stmt->error) {
        die('Error in executing the SQL statement: ' . $stmt->error);
    }

    $stmt->close();
    
    echo 'Attendance inserted successfully.';
} else {
    echo 'Invalid request method.';
}

$conn->close();
?>
