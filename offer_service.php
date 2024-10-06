<?php
include('config.php');
session_start();

$username = $_SESSION['username'];

// Get user ID and teacher ID
$sql_user = "SELECT user_ID FROM user WHERE username = '$username'";
$result_user = $conn->query($sql_user);

if ($result_user->num_rows > 0) {
    $row_user = $result_user->fetch_assoc();
    $userID = $row_user['user_ID'];

    $sql_teacher = "SELECT teacher_id FROM teacher WHERE user_ID = '$userID'";
    $result_teacher = $conn->query($sql_teacher);

    if ($result_teacher->num_rows > 0) {
        $row_teacher = $result_teacher->fetch_assoc();
        $teacherID = $row_teacher['teacher_id'];

        // Insert into tutor table
        $sql_insert_tutor = "INSERT INTO tutor (teacherid, userid) VALUES ('$teacherID', '$userID')";
        if ($conn->query($sql_insert_tutor) === TRUE) {
            echo 'You are now offering your services as a tutor.';
        } else {
            echo 'Error: ' . $conn->error;
        }
    }
}
?>
