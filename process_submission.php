<?php
// Start or resume the session
session_start();

// Check if the score is provided in the form submission
if (!isset($_POST['score']) || empty($_POST['score'])) {
    echo "<script>alert('Error: Score value is not provided or empty.');</script>"; // Alert message if score is not provided or empty
    echo "<script>window.location.href = 'dashboard.php';</script>"; // Redirect to submissions.php
    exit();
}

// Sanitize the score value
$score = filter_var($_POST['score'], FILTER_SANITIZE_NUMBER_FLOAT);

// Validate the score
if (!is_numeric($score) || $score < 0 || $score > 100) {
    echo "<script>alert('Error: Invalid score value. Score must be a number between 0 and 100.');</script>"; // Alert message for invalid score value
    echo "<script>window.location.href = 'dashboard.php';</script>"; // Redirect to submissions.php
    exit();
}

// Include the database connection
include('config.php');

// Check if the response ID is set in the URL parameters
if (!isset($_GET['responseid'])) {
    echo "<script>alert('Error: Response ID is not provided in the URL parameters.');</script>"; // Alert message if response ID is not provided
    echo "<script>window.location.href = 'dashboard.php';</script>"; // Redirect to submissions.php
    exit();
}

// Retrieve the response ID from the URL parameters
$responseID = $_GET['responseid'];

// Fetch the maximum score for the selected essay
$sql_max_score = "SELECT score FROM essay_type WHERE essayid IN (SELECT essayid FROM response WHERE responseid = ?)";
$stmt_max_score = $conn->prepare($sql_max_score);
$stmt_max_score->bind_param("i", $responseID);
$stmt_max_score->execute();
$result_max_score = $stmt_max_score->get_result();

// Get the maximum score
$maxScore = 0; // Default value
if ($row_max_score = $result_max_score->fetch_assoc()) {
    $maxScore = $row_max_score['score'];
}

// Calculate the percentage
$percentage = ($score / $maxScore) * 100;

// Update the score in the response table
$sql_update_score = "UPDATE response SET score = ? WHERE responseid = ?";
$stmt_update_score = $conn->prepare($sql_update_score);
$stmt_update_score->bind_param("di", $score, $responseID);

// Execute the SQL statement
if ($stmt_update_score->execute()) {
    // Display the percentage
    echo "<script>alert('Score: " . $score . " / Max Score: " . $maxScore . " - Percentage: " . number_format($percentage, 2) . "%');</script>";
} else {
    echo "<script>alert('Error updating score: " . $conn->error . "');</script>"; // Error alert message if score update fails
}

// Close statements and database connection
$stmt_max_score->close();
$stmt_update_score->close();
$conn->close();
?>

<script>
    // Redirect to dashboard.php after displaying the alert message
    window.location.href = 'dashboard.php';
</script>
