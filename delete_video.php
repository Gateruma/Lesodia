<?php
include('config.php');
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['videoid'])) {
    $videoid = $_POST['videoid'];

    // Retrieve the video file name based on videoid
    $sql_get_video = "SELECT video FROM tutorvideo WHERE videoid = '$videoid'";
    $result_get_video = $conn->query($sql_get_video);

    if ($result_get_video->num_rows > 0) {
        $video = $result_get_video->fetch_assoc();
        $videoFile = 'videos/' . $video['video'];

        // Delete the video file from the server
        if (file_exists($videoFile)) {
            unlink($videoFile);
        }

        $sql_delete = "DELETE FROM tutorvideo WHERE videoid = '$videoid'";
        if ($conn->query($sql_delete) === TRUE) {
            // Refresh the page if the deletion is successful
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            echo "Error deleting video: " . $conn->error;
        }
    } else {
        echo "Video not found.";
    }
} else {
    echo "No video ID specified.";
}
?>
