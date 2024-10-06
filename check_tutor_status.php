<?php
include('config.php');
session_start();

$username = $_SESSION['username'];

// Get user ID
$sql_user = "SELECT user_ID FROM user WHERE username = '$username'";
$result_user = $conn->query($sql_user);

if ($result_user->num_rows > 0) {
    $row_user = $result_user->fetch_assoc();
    $userID = $row_user['user_ID'];

    // Check if the user is already a tutor
    $sql_check_tutor = "SELECT * FROM tutor WHERE userid = '$userID'";
    $result_check_tutor = $conn->query($sql_check_tutor);

    if ($result_check_tutor->num_rows > 0) {
        echo 'is_tutor';
    } else {
        echo 'not_tutor';
    }
}
?>
