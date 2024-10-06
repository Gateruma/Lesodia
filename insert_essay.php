<?php
include('config.php');

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


// Get the teacher ID of the current logged-in user
$username = $_SESSION['username'];
$sql_teacher_id = "SELECT teacher_id FROM teacher WHERE user_ID = (SELECT user_ID FROM user WHERE username = ?)";
$stmt_teacher_id = $conn->prepare($sql_teacher_id);
$stmt_teacher_id->bind_param("s", $username);
$stmt_teacher_id->execute();
$result_teacher_id = $stmt_teacher_id->get_result();

if ($result_teacher_id->num_rows > 0) {
    $row_teacher_id = $result_teacher_id->fetch_assoc();
    $teacherID = $row_teacher_id['teacher_id']; // Set the teacherID
} else {
    echo "Teacher ID not found.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $title = $_POST["essayTitle"];
    $question = $_POST["essayQuestion"];
    $deadline = $_POST["essayDeadline"];
    $score = $_POST["essayScore"];

    // Get the subject ID from the URL
    $subjectID = $_GET['subjectID'];

    // Insert the new essay into the database
    $sql_insert_essay = "INSERT INTO essay_type (subjectID, teacherid, title, question, deadline, score)
                         VALUES (?, ?, ?, ?, ?, ?)";

    $stmt_insert_essay = $conn->prepare($sql_insert_essay);
    $stmt_insert_essay->bind_param("iisssd", $subjectID, $teacherID, $title, $question, $deadline, $score);

    if ($stmt_insert_essay->execute()) {
        // Successfully inserted, redirect to dashboard.php and show alert
        echo '<script>alert("Assessment is added with its title ' . $title . '"); window.location.href = "dashboard.php";</script>'; // Alert and redirect using JavaScript
        exit();
    } else {
        echo "Error: " . $stmt_insert_essay->error;
    }
}

$conn->close();
?>
