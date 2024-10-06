<?php
// Start or resume the session
session_start();

// Check if the score is provided in the form submission
if (!isset($_POST['score']) || empty($_POST['score'])) {
    echo json_encode(array("success" => false, "message" => "Error: Score value is not provided or empty."));
    exit();
}

// Sanitize the score value
$score = filter_var($_POST['score'], FILTER_SANITIZE_NUMBER_FLOAT);

// Validate the score
if (!is_numeric($score) || $score < 0 || $score > 100) {
    echo json_encode(array("success" => false, "message" => "Error: Invalid score value. Score must be a number between 0 and 100."));
    exit();
}

// Include the database connection
include('config.php');

// Check if the response ID is set in the URL parameters
if (!isset($_GET['responseid'])) {
    echo json_encode(array("success" => false, "message" => "Error: Response ID is not provided in the URL parameters."));
    exit();
}

// Retrieve the response ID from the URL parameters
$responseID = $_GET['responseid'];

// Update the score in the response table
$sql_update_score = "UPDATE response SET score = ? WHERE responseid = ?";
$stmt_update_score = $conn->prepare($sql_update_score);
$stmt_update_score->bind_param("di", $score, $responseID);

// Execute the SQL statement
if ($stmt_update_score->execute()) {
    // Score updated successfully
    $response = array("success" => true, "message" => "Score updated successfully.");
} else {
    // Failed to update score
    $response = array("success" => false, "message" => "Error updating score: " . $conn->error);
}

// Close statements and database connection
$stmt_update_score->close();
$conn->close();

// Return JSON response
echo json_encode($response);
?>
