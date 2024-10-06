<?php
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assessmentID  = null; // Auto-incremented in the database
    $title = $_POST["questionTitle"];
    $description = $_POST["questionContent"];
    $deadline = $_POST["deadline"];
    $score = $_POST["score"];

    $sql = "INSERT INTO assessment (title, description, deadline, score) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $title, $description, $deadline, $score);

    if ($stmt->execute()) {
        echo "Essay question added successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
