<?php
include('config.php');
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Prepare and sanitize inputs
    $tutorid = $conn->real_escape_string($_POST['tutorid']);
    $studentid = $conn->real_escape_string($_POST['studentid']);
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);

    // SQL query to insert the lesson into the database
    $sql = "INSERT INTO tutorlesson (tutorid, studentid, title, content) VALUES ('$tutorid', '$studentid', '$title', '$content')";

    if ($conn->query($sql) === TRUE) {
        $message = "New lesson added successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }

    // Close the connection
    $conn->close();

    // Use JavaScript to alert the message and redirect back to the lesson page
    echo "<script>
            alert('$message');
            window.location.href = 'tutorsubj.php?tutorID=$tutorid&studentID=$studentid';
          </script>";
    exit();
}
?>
