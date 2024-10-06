<?php
// Your database connection and configuration
include("config.php");

// Get the input from the request
$input = $_GET['input'];

// Prepare and execute the SQL query to fetch search suggestions
$sql = 'SELECT student.studentID, user.firstname, user.lastname
        FROM student
        INNER JOIN user ON student.userID = user.user_ID
        WHERE student.studentID LIKE ? OR user.firstname LIKE ? OR user.lastname LIKE ?';
$stmt = $conn->prepare($sql);
$searchTerm = '%' . $input . '%';
$stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// Initialize an array to store unique suggestions
$suggestions = array();

// Process search suggestions
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Create a suggestion with student ID and full name
        $suggestion = $row['studentID'] . '-' . $row['firstname'] . ' ' . $row['lastname'] . ' ';
        // Add the suggestion to the array if it's not already present
        if (!in_array($suggestion, $suggestions)) {
            $suggestions[] = $suggestion;
        }
    }
}

$stmt->close();
$conn->close();

// Check if any suggestions are found
if (count($suggestions) > 0) {
    // Output the suggestions with a white background
    echo '<div style="width: 100%; background-color: #ffffff;">';
    foreach ($suggestions as $suggestion) {
        // Echo each suggestion as a clickable div with JavaScript to handle click event
        echo '<div style="width: 100%; padding: 10px; border-bottom: 1px solid #ccc; cursor: pointer;" onclick="selectSuggestion(\'' . $suggestion . '\')">' . $suggestion . '</div>';
    }
    echo '</div>';
} else {
    // If no suggestions are found, display "No students found"
    echo 'No students data';
}
?>
