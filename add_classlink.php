<?php
include('config.php');
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['classlink'], $_POST['studentID'])) {
    $classlink = $_POST['classlink'];
    $studentID = $_POST['studentID'];

    // Validate the class link as a URL
    if (filter_var($classlink, FILTER_VALIDATE_URL) && !empty($studentID)) {
        // Update the class link in the schedule table
        $sql_update = "UPDATE schedule SET classlink = ? WHERE studentID = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $classlink, $studentID);

        // Execute and handle errors
        if ($stmt_update->execute()) {
            // Use JavaScript alert to show success
            echo "<script>alert('Class link updated successfully.'); window.location.href='tutorstudent.php';</script>";
        } else {
            // Use JavaScript alert to show error
            echo "<script>alert('Error updating class link.'); window.location.href='tutorstudent.php';</script>";
        }

        $stmt_update->close();
    } else {
        // Use JavaScript alert for invalid input
        echo "<script>alert('Invalid class link or missing student ID.'); window.location.href='tutorstudent.php';</script>";
    }
} else {
    // Use JavaScript alert for no data submitted
    echo "<script>alert('No data submitted.'); window.location.href='tutorstudent.php';</script>";
}
?>
