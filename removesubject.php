<?php
include('config.php');

// Check if the removeSubjectName parameter is set
if (isset($_POST['removeSubjectName'])) {
    // Get the subject name to be removed
    $subjectName = $_POST['removeSubjectName'];



    // Prepare the SQL statement to delete the subject
    $sql = "DELETE FROM storedsubj WHERE subjectname = ?";

    // Prepare and bind the parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $subjectName);

    // Execute the statement
    if ($stmt->execute()) {
        echo alert("Subject '$subjectName' removed successfully.");
    } else {
        echo "Error: Unable to remove subject.";
    }

    // Close the statement and the connection
    $stmt->close();
    $conn->close();
} else {
    echo "Error: Subject name not provided.";
}
?>
