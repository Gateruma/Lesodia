<?php
session_start();

// Redirect user to login page if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include_once "config.php"; // Assuming you have a separate file for database connection

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $title = $_POST["lessonTitle"];
    $content = $_POST["lessonContent"];
    $subject = $_GET['subject']; // Get the subject from URL parameter

    // Get teacher ID of the current logged-in teacher
    $username = $_SESSION['username'];
    $sql_teacherid = "SELECT teacher_id FROM teacher WHERE user_ID = (SELECT user_ID FROM user WHERE username = ?)";
    $stmt = $conn->prepare($sql_teacherid);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result_teacherid = $stmt->get_result();

    if ($result_teacherid->num_rows > 0) {
        $row_teacherid = $result_teacherid->fetch_assoc();
        $teacherID = $row_teacherid['teacher_id'];

        // Prepare and execute SQL query to get subject ID
        $sql_subject = "SELECT subjectID FROM subject WHERE subjectName = ?";
        $stmt = $conn->prepare($sql_subject);
        $stmt->bind_param("s", $subject);
        $stmt->execute();
        $result_subject = $stmt->get_result();

        if ($result_subject->num_rows > 0) {
            $row_subject = $result_subject->fetch_assoc();
            $subjectID = $row_subject['subjectID'];

            // Handle file upload if a file is provided
            $file = null;
            if (!empty($_FILES["file"]["name"])) {
                $targetDir = "uploads/"; // Specify the directory where you want to store uploaded files
                $fileName = basename($_FILES["file"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

                // Check if the file is a valid type
                $allowedTypes = array("pdf", "doc", "docx", "mp4");
                if (in_array($fileType, $allowedTypes)) {
                    // Move the file to the specified directory
                    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
                        $file = $fileName; // Set the file name to be inserted into the database
                    } else {
                        echo "Error uploading file.";
                        exit();
                    }
                } else {
                    echo "Invalid file type. Only PDF, DOC, and DOCX files are allowed.";
                    exit();
                }
            }

            // Prepare and execute SQL query to insert the lesson into the database
            $sql_insert_lesson = "INSERT INTO lesson (subjectid, teacherid, title, content, file) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_insert_lesson);
            $stmt->bind_param("iisss", $subjectID, $teacherID, $title, $content, $file);
            if ($stmt->execute()) {
                // Successfully inserted, show alert and redirect to dashboard.php
                echo '<script>alert("Lesson is added on ' . $subject . ' with its title ' . $title . '"); window.location.href = "dashboard.php";</script>'; // Alert and redirect using JavaScript
                exit();
            } else {
                echo "Error: " . $stmt->error;
                exit();
            }
        } else {
            echo "Subject not found.";
            exit();
        }
    } else {
        echo "Teacher not found.";
        exit();
    }
}

// Close database connection
$conn->close();
?>
