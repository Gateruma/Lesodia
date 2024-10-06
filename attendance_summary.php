<?php
include('config.php');

session_start();



if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Retrieve subject_ID from the URL
$subjectID = isset($_GET['subjectID']) ? $_GET['subjectID'] : null;

// Fetch the selected students marked present
$sql = "SELECT user.firstname AS first_name, user.lastname AS last_name
        FROM attendance
        INNER JOIN user ON attendance.userID = user.user_ID
        WHERE attendance.subjectid = ? AND attendance.date = ? AND attendance.status = 'Present'";

$stmt = $conn->prepare($sql);
$subjectIDParam = $subjectID; // Assign $subjectID to a variable
$dateParam = date("Y-m-d"); // Assign the current date to a variable
$stmt->bind_param("is", $subjectIDParam, $dateParam);
$stmt->execute();
$result = $stmt->get_result();

$selectedStudents = [];

while ($row = $result->fetch_assoc()) {
    $selectedStudents[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Summary</title>
    <!-- Add your stylesheets or other head content here -->
</head>

<body>
    <h1>Attendance Summary</h1>

    <!-- Display Subject ID -->
    <div>Subject ID: <?php echo $subjectID; ?></div>

    <!-- Display selected students -->
    <table border="1">
        <tr>
            <th>Last Name</th>
            <th>First Name</th>
        </tr>
        <?php foreach ($selectedStudents as $student) { ?>
            <tr>
                <td><?php echo $student['last_name']; ?></td>
                <td><?php echo $student['first_name']; ?></td>
            </tr>
        <?php } ?>
    </table>

    <!-- Add other content or links as needed -->

</body>

</html>
