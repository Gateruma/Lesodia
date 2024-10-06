<?php
include('config.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $tutorID = filter_input(INPUT_POST, 'tutorid', FILTER_VALIDATE_INT);
    $newRate = filter_input(INPUT_POST, 'rate', FILTER_VALIDATE_INT); // Change to FILTER_VALIDATE_INT

    // Debugging: Check input values
    if ($tutorID === false || $newRate === false) {
        echo "Invalid input.";
        exit();
    }

    // Debugging: Check what is being sent
    echo "Tutor ID: " . htmlspecialchars($tutorID) . "<br>";
    echo "New Rate: " . htmlspecialchars($newRate) . "<br>";

    // Prepare the SQL statement to update the rate
    $sql_update = "UPDATE tutor SET rate = ? WHERE tutorid = ?";
    $stmt_update = $conn->prepare($sql_update);

    if ($stmt_update) {
        $stmt_update->bind_param("ii", $newRate, $tutorID); // Change to "ii" for two integers

        if ($stmt_update->execute()) {
            header("Location: tutordashboard.php");
            exit();
        } else {
            echo "Error updating rate: " . $stmt_update->error; // Show error message
        }

        $stmt_update->close();
    } else {
        echo "Error preparing statement: " . $conn->error; // Log the specific error
    }
}
$conn->close();
?>
