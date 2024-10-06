<?php
include('config.php');
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Get the user ID based on the logged-in username
$sql_user = "SELECT user_ID FROM user WHERE username = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $username);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $row_user = $result_user->fetch_assoc();
    $userID = $row_user['user_ID'];
} else {
    echo "User not found.";
    exit();
}

// Get the teacher ID based on the user ID
$sql_teacher = "SELECT teacher_id FROM teacher WHERE user_ID = ?";
$stmt_teacher = $conn->prepare($sql_teacher);
$stmt_teacher->bind_param("i", $userID);
$stmt_teacher->execute();
$result_teacher = $stmt_teacher->get_result();

if ($result_teacher->num_rows > 0) {
    $row_teacher = $result_teacher->fetch_assoc();
    $tutorID = $row_teacher['teacher_id'];
} else {
    echo "Teacher ID not found.";
    exit();
}

// Handle video upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video'])) {
    $caption = $_POST['caption'];
    $video = $_FILES['video'];
    $videoName = $video['name'];
    $videoTmpName = $video['tmp_name'];
    $videoSize = $video['size'];
    $videoError = $video['error'];
    $videoType = $video['type'];

    // Define allowed file types and maximum file size
    $allowedTypes = ['video/mp4', 'video/mkv', 'video/avi'];
    $maxFileSize = 50000000; // 50MB

    if ($videoError === 0) {
        if (in_array($videoType, $allowedTypes) && $videoSize <= $maxFileSize) {
            // Define upload directory
            $uploadDir = 'videos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Move the video to the videos folder
            $videoPath = $uploadDir . basename($videoName);
            if (move_uploaded_file($videoTmpName, $videoPath)) {
                // Insert video info into the database
                $sql_insert = "INSERT INTO tutorvideo (tutorid, video, date, caption) VALUES (?, ?, NOW(), ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("iss", $tutorID, $videoName, $caption);
                if ($stmt_insert->execute()) {
                    header("Location: " . $_SERVER['HTTP_REFERER']);
                } else {
                    echo "Error: " . $stmt_insert->error;
                }
            } else {
                echo "Failed to move uploaded video.";
            }
        } else {
            echo "Invalid file type or file size too large.";
        }
    } else {
        echo "Error uploading file.";
    }
} else {
    echo "No file uploaded.";
}

$stmt_user->close();
$stmt_teacher->close();
$stmt_insert->close();
$conn->close();
?>
