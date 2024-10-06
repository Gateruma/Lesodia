<?php
include('config.php');

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from the AJAX request
    $subjectID = $_POST['subjectID'];
    $generatedClassID = $_POST['classID'];
    $date = $_POST['date'];

    // Get the teacher ID based on the logged-in user
    $username = $_SESSION['username'];


    // Fetch teacher ID from teacher table
    $sql_teacher = "SELECT teacher_id FROM teacher WHERE user_ID = (SELECT user_ID FROM user WHERE username = '$username')";
    $result_teacher = $conn->query($sql_teacher);

    if ($result_teacher->num_rows > 0) {
        $row_teacher = $result_teacher->fetch_assoc();
        $teacherID = $row_teacher['teacher_id'];

        // Insert data into the "class" table
        $sql = "INSERT INTO class (classID, subjectid, userID, date) VALUES ('$generatedClassID', '$subjectID', '$teacherID', '$date')";

        if ($conn->query($sql) === TRUE) {
            echo "Data inserted successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Error: Teacher ID not found for the logged-in user";
    }

    $conn->close();
}
?>
