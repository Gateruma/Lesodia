<?php
include('config.php');

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Start the session
    session_start();

    // Check if the user is logged in
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }

    // Get the username from the session
    $username = $_SESSION['username'];
    

    // Get the userID based on the logged-in username
    $sql_user = "SELECT user_ID FROM user WHERE username = '$username'";
    $result_user = $conn->query($sql_user);

    if ($result_user->num_rows > 0) {
        $row_user = $result_user->fetch_assoc();
        $userID = $row_user['user_ID'];

        // Get the teacherID based on the userID
        $sql_teacher = "SELECT teacher_id FROM teacher WHERE user_ID = '$userID'";
        $result_teacher = $conn->query($sql_teacher);

        if ($result_teacher->num_rows > 0) {
            $row_teacher = $result_teacher->fetch_assoc();
            $teacherID = $row_teacher['teacher_id'];

            // Get data from the POST request
            $subjectName = $_POST['subjectName'];
            $classDay = implode(',', $_POST['classDay']); // Convert array to comma-separated string
            $classLink = $_POST['classLink'];
            $startTime = $_POST['startTime'];
            $endTime = $_POST['endTime'];

            // Insert data into the 'subject' table with the correct teacherID
            $sql_insert = "INSERT INTO subject (userID, subjectName, classDay, classLink, startTime, endTime) VALUES ('$teacherID', '$subjectName', '$classDay', '$classLink', '$startTime', '$endTime')";

            if ($conn->query($sql_insert) === TRUE) {
                echo '<script>alert("Data inserted successfully"); window.location.href = "profilemysubj.php";</script>';
            } else {
                echo "Error: " . $sql_insert . "<br>" . $conn->error;
            }
        } else {
            echo "Error: Unable to find teacherID for the logged-in user.";
        }
    } else {
        echo "Error: Unable to find userID for the logged-in user.";
    }

    $conn->close();
}
?>
