<?php
include('config.php');

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the subject name from the POST request
    $newSubjectName = $_POST['newSubjectName'];


    // You should replace 'your_user_ID_here' with the actual user ID from the session
    $userID = 'your_user_ID_here';

    // Insert the new subject into the subject table
    $sql_insert = "INSERT INTO subject (userID, subjectName) VALUES ('$userID', '$newSubjectName')";

    if ($conn->query($sql_insert) === TRUE) {
        echo "Subject inserted successfully";
    } else {
        echo "Error: " . $sql_insert . "<br>" . $conn->error;
    }

    // Close the database connection
    $conn->close();
}
?>
