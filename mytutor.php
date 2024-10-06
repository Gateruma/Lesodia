<?php
include('config.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Retrieve the userID of the logged-in user
$userID = $_SESSION['userID'];

// Query to retrieve the tutor assigned to the current student along with the tutor ID
$tutorQuery = "
    SELECT t.tutorid, t.teacherid, u.firstname, u.lastname, u.email, u.address, u.gender, u.dateofbirth 
    FROM student s
    JOIN tutor t ON s.teacherID = t.teacherid  /* Change this line */
    JOIN user u ON t.userid = u.user_ID  /* Use the correct column name */
    WHERE s.userID = ?";

$tutorStmt = $conn->prepare($tutorQuery);
$tutorStmt->bind_param("i", $userID);
$tutorStmt->execute();
$tutorResult = $tutorStmt->get_result();
$tutor = $tutorResult->fetch_assoc();
$tutorStmt->close();

// If a tutor is found, retrieve the lessons assigned to the tutor
$lessonTitles = [];
if ($tutor) {
    $tutorID = $tutor['tutorid']; // Get the tutor ID

    // Query to retrieve the lesson titles assigned to the tutor from the tutorlesson table
    $lessonQuery = "
        SELECT title 
        FROM tutorlesson 
        WHERE tutorid = ?";

    $lessonStmt = $conn->prepare($lessonQuery);
    $lessonStmt->bind_param("i", $tutorID);
    $lessonStmt->execute();
    $lessonResult = $lessonStmt->get_result();

    // Fetch the lesson titles
    while ($row = $lessonResult->fetch_assoc()) {
        $lessonTitles[] = $row['title'];
    }
    
    $lessonStmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="mytutor.css">
    <title>My Tutor</title>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="Logo">
    </div>

    <div class="tutor-info">
        <?php if ($tutor): ?>
            <h2>Your Assigned Tutor</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($tutor['firstname'] . ' ' . $tutor['lastname']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($tutor['email']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($tutor['address']); ?></p>
            <p><strong>Gender:</strong> <?php echo htmlspecialchars($tutor['gender']); ?></p>
            <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($tutor['dateofbirth']); ?></p>
            <p><strong>Tutor ID:</strong> <?php echo htmlspecialchars($tutorID); ?></p> <!-- Displaying the tutor ID -->
            
            <h3>Lessons Assigned:</h3>
            <ul>
                <?php if (!empty($lessonTitles)): ?>
                    <?php foreach ($lessonTitles as $title): ?>
                        <li><?php echo htmlspecialchars($title); ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No lessons assigned.</li>
                <?php endif; ?>
            </ul>
        <?php else: ?>
            <p>You do not have an assigned tutor at the moment.</p>
        <?php endif; ?>
    </div>

    <button onclick="window.location.href='studentdashboard.php'">Back to Dashboard</button>
</body>
</html>
