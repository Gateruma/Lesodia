<?php
session_start();
include('config.php');


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$subjectID = isset($_POST['subject_ID']) ? $_POST['subject_ID'] : null;
$studentIDs = isset($_POST['user_IDs']) ? $_POST['user_IDs'] : [];

echo "Subject ID: " . $subjectID . "<br>";
echo "Selected User IDs: " . implode(", ", $studentIDs) . "<br>";

$stmt = $conn->prepare("INSERT INTO attendance (subjectID, userID, status) VALUES (?, ?, 'Present')");

foreach ($studentIDs as $userID) {
    $stmt->bind_param("ii", $subjectID, $userID);
    $stmt->execute();
}

$stmt->close();

$sql = "SELECT user_ID, firstname, lastname FROM user WHERE user_ID IN (" . implode(',', $studentIDs) . ") AND user_ID IN (SELECT userID FROM attendance WHERE subjectID = ? AND status = 'Present')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $subjectID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<h2>Selected Students:</h2>";
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>{$row['firstname']} {$row['lastname']} (User ID: {$row['user_ID']})</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No selected students found with 'Present' status.</p>";
}

$stmt->close();
$conn->close();


header("refresh:3000;url=dashboard.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Submitted</title>
</head>

<body>
    <h1>Attendance Submitted</h1>
    <p>Redirecting to dashboard...</p>
</body>

</html>
