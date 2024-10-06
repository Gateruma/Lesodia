<?php
// Include the database connection
include('config.php');

// Retrieve the student ID, response ID, block ID, and score from the URL parameters
$studentID = isset($_GET['studentid']) ? $_GET['studentid'] : null;
$responseID = isset($_GET['responseid']) ? $_GET['responseid'] : null;
$blockID = isset($_GET['id']) ? $_GET['id'] : null;
$score = isset($_GET['score']) ? $_GET['score'] : null;

// Update score if provided
if (!empty($score) && is_numeric($score)) {
    $sql_update_score = "UPDATE response SET score = ? WHERE responseid = ?";
    $stmt_update_score = $conn->prepare($sql_update_score);
    $stmt_update_score->bind_param("ii", $score, $responseID);
    $stmt_update_score->execute();
    $stmt_update_score->close();
}

// Fetch submission details
$sql_submission = "SELECT u.firstname AS student_firstname, u.lastname AS student_lastname, r.date, r.score, r.answer, r.file, r.essayid, r.blockid, r.guessid, b.title AS block_title
                   FROM user u
                   JOIN student s ON u.user_ID = s.userID
                   JOIN response r ON s.studentID = r.studentID
                   JOIN blocks_type b ON r.blockid = b.blockid
                   WHERE r.studentID = ? AND r.responseid = ?";
$stmt_submission = $conn->prepare($sql_submission);
$stmt_submission->bind_param("ii", $studentID, $responseID);
$stmt_submission->execute();
$result_submission = $stmt_submission->get_result();
$row_submission = $result_submission->fetch_assoc();

// Fetch student info
$sql_student_info = "SELECT u.firstname, u.lastname FROM user u JOIN student s ON u.user_ID = s.userID WHERE s.studentID = ?";
$stmt_student_info = $conn->prepare($sql_student_info);
$stmt_student_info->bind_param("i", $studentID);
$stmt_student_info->execute();
$result_student_info = $stmt_student_info->get_result();

// Display the student's info and ID with the block title
if ($row_student_info = $result_student_info->fetch_assoc()) {
    echo "<div class='linames' style='position: absolute; top: 30%; left: 50%; transform: translate(-50%, -50%); height: 50px; width: 80%; background-color: #D9D9D9; border-radius: 15px; font-size: 25px;'> " . $row_student_info['firstname'] . " " . $row_student_info['lastname'] . "'s submission to: " . $row_submission['block_title'] . "</div>";
} else {
    echo "<div class='student-id'>Unknown</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="submissions.css"> <!-- Include your CSS file here -->
    <title>Submission Details</title>
    <style>
body, html {
    height: 100%;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.submission-details {
    display: flex;
    position: absolute; /* Make it fixed position */
    top: 35%; /* Align to the top */
    left: 50%; /* Align to the center */
    transform: translateX(-50%); /* Move it back by half of its width */
    width: 80%; /* Take full width */
    height: 500px;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.submission-details img {
    width: 70%;
    height: 100%;
    border-radius: 10px;
}


.score-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-left: 20px;
}

    </style>
</head>

<body>
<button class="return-button" type="button" onclick="window.location.href='dashboard.php'" style="position: absolute;">Return to Dashboard</button>


<div class="header">
    <img src="logo.png" alt="Logo">
</div> 
    <div class="center-container">
        <?php
        if ($row_submission) {
            echo '<div class="submission-details">';
            if ($row_submission['file']) {
                // Convert the binary data to base64
                $imageData = base64_encode($row_submission['file']);
                // Create a data URL for the image
                $src = 'data:image/png;base64,' . $imageData; // Assuming PNG format
                echo '<img src="' . $src . '" alt="Submission Image">';
            } else {
                echo '<p>No file submitted</p>';
            }
            echo '<div class="score-box">';
            echo '<p><strong>Input Score</strong> ' . htmlspecialchars($row_submission['answer']) . '</p>';
            echo '<form method="get">';
            echo '<input type="hidden" name="studentid" value="' . $studentID . '">';
            echo '<input type="hidden" name="responseid" value="' . $responseID . '">';
            echo '<input type="hidden" name="id" value="' . $blockID . '">';
            echo '<input type="number" id="score" name="score" value="' . $row_submission['score'] . '">';
            echo '<input type="submit" value="Submit">';
            echo '</form>';
            echo '</div>';
            echo '</div>';
        } else {
            echo '<div class="submission-details">';
            echo '<p>No submission details found.</p>';
            echo '</div>';
        }

        // Fetch block details
        $sql_block = "SELECT * FROM blocks_type WHERE blockid = ?";
        $stmt_block = $conn->prepare($sql_block);
        $stmt_block->bind_param("i", $blockID);
        $stmt_block->execute();
        $result_block = $stmt_block->get_result();
        $row_block = $result_block->fetch_assoc();

        $stmt_submission->close();
        $stmt_student_info->close();
        $stmt_block->close();
        $conn->close();
        ?>
    </div>
</body>

</html>
