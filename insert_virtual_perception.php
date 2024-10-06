<?php
include('config.php');

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch teacher ID of the currently logged-in user
$sql_teacher_id = "SELECT teacher_id FROM teacher WHERE user_ID = (SELECT user_ID FROM user WHERE username = ?)";
$stmt_teacher_id = $conn->prepare($sql_teacher_id);
$stmt_teacher_id->bind_param('s', $username);
$stmt_teacher_id->execute();
$result_teacher_id = $stmt_teacher_id->get_result();

if ($result_teacher_id->num_rows > 0) {
    $row_teacher_id = $result_teacher_id->fetch_assoc();
    $teacherID = $row_teacher_id['teacher_id'];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $subjectID = $_POST['subjectID']; // Retrieve subjectID from the form
        $itemTitle = $_POST['itemTitle'];
        $title = $_POST['title']; // Retrieve title from the form
        $itemDeadline = $_POST['itemDeadline'];

        // Prepare the insert statement
        $sql_insert_guess = "INSERT INTO guesstype (subjectid, teacherid, deadline, items, title) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert_guess);
        $stmt_insert->bind_param('iisss', $subjectID, $teacherID, $itemDeadline, $itemTitle, $title);

        if ($stmt_insert->execute()) {
            // Data inserted successfully
            echo "<script>alert('Data inserted successfully.');</script>";
        } else {
            // Error inserting data
            $error_message = $stmt_insert->error;
            echo "<script>alert('Error: " . addslashes($error_message) . "');</script>";
        }

        $stmt_insert->close();
    }
} else {
    echo "<script>alert('Teacher not found.');</script>";
    exit();
}

$stmt_teacher_id->close();
$conn->close();
?>
