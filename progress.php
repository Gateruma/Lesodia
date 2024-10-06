<?php
// Start or resume the session
session_start();

// Include the database connection
include('config.php');

// Initialize variables for student ID and name
$studentID = "";
$studentName = "";

// Get the studentID from the URL parameter
if (isset($_GET['studentID'])) {
    $studentID = $_GET['studentID'];
    $studentName = ""; // Initialize to empty string

    // Check if studentName is also provided in the URL
    if (isset($_GET['studentName'])) {
        $studentName = urldecode($_GET['studentName']);
    }

    // Fetch student details based on the studentID
    $sql_student = "SELECT * FROM student WHERE studentID = ?";
    $stmt_student = $conn->prepare($sql_student);
    $stmt_student->bind_param("i", $studentID);
    $stmt_student->execute();
    $result_student = $stmt_student->get_result();

    // Check if student exists
    if ($row_student = $result_student->fetch_assoc()) {
        // Update studentID in case it is modified in the database
        $studentID = $row_student['studentID'];
        // You can access other columns as needed
    } else {
        echo "Student not found.";
    }

    // Close the statement
    $stmt_student->close();
} else {
    echo "StudentID parameter not provided.";
}

// Fetch responses assigned to the studentID (if subjectID is provided)
if (isset($_GET['subjectID'])) {
    $subjectID = $_GET['subjectID'];
    $responses = [];
    $sql_responses = "SELECT * FROM response WHERE studentID = ? AND essayid IN (SELECT essayid FROM essay_type WHERE subjectID = ?)";
    $stmt_responses = $conn->prepare($sql_responses);
    $stmt_responses->bind_param("ii", $studentID, $subjectID);
    $stmt_responses->execute();
    $result_responses = $stmt_responses->get_result();

    while ($row_response = $result_responses->fetch_assoc()) {
        $responses[] = $row_response;
    }

    $stmt_responses->close();

    // Fetch attendance data for the selected subject
    $attendance = [];
    $sql_attendance = "SELECT * FROM attendance WHERE studentID = ? AND classID IN (SELECT classID FROM class WHERE subjectID = ?)";
    $stmt_attendance = $conn->prepare($sql_attendance);
    $stmt_attendance->bind_param("ii", $studentID, $subjectID);
    $stmt_attendance->execute();
    $result_attendance = $stmt_attendance->get_result();

    while ($row_attendance = $result_attendance->fetch_assoc()) {
        $attendance[] = $row_attendance;
    }

    $stmt_attendance->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="progress.css">
<title>Three Tables Example</title>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }
    label {
        font-weight: bold;
        margin-top: 10px;
        display: block;
    }
    textarea {
        width: 100%;
        padding: 10px;
        margin: 5px 0 20px 0;
        border-radius: 5px;
        border: 1px solid #ccc;
        resize: vertical;
    }
    p {
        margin: 5px 0 20px 0;
    }
    h2, h3 {
        color: #333;
    }
    button {
        padding: 10px 20px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        margin-bottom: 20px;
    }
    button:hover {
        background-color: #218838;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    table, th, td {
        border: 1px solid #ddd;
    }
    th, td {
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: #f4f4f4;
    }
</style>
</head>
<body>

<h2>Student ID: <?php echo $studentID; ?></h2>
<h2>Student Name: <?php echo $studentName; ?></h2>

<!-- IEP Button -->
<a href="IEP.php?studentID=<?php echo $studentID; ?>" class="button">IEP</a>

<h2>Assessments</h2>
<table>
    <tr>
        <th>Response ID</th>
        <th>Student ID</th>
        <th>Essay ID</th>
        <th>Block ID</th>
        <th>Question ID</th>
        <th>Date</th>
        <th>Answer</th>
        <th>File</th>
        <th>Score</th>
    </tr>
    <?php
    // Display response data if available
    if (isset($responses)) {
        foreach ($responses as $response) {
            echo "<tr>
                    <td>{$response['responseid']}</td>
                    <td>{$response['studentID']}</td>
                    <td>{$response['essayid']}</td>
                    <td>{$response['blockid']}</td>
                    <td>{$response['date']}</td>
                    <td>{$response['answer']}</td>
                    <td>{$response['file']}</td>
                    <td>{$response['score']}</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='9'>No responses available for the selected subject.</td></tr>";
    }
    ?>
</table>

<h2>Attendance</h2>
<table>
    <caption>Attendance table</caption>
    <tr>
        <th>ClassID</th>
        <th>Date</th>
        <th>Status</th>
    </tr>
    <?php
    // Display attendance data if available
    if (isset($attendance)) {
        foreach ($attendance as $attend) {
            echo "<tr>
                    <td>{$attend['classID']}</td>
                    <td>{$attend['date']}</td>
                    <td>{$attend['status']}</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='3'>No attendance records available.</td></tr>";
    }
    ?>
</table>

<!-- Dropdown for subjects -->
<div class="dropdown">
    <label for="subjects">Select Subject:</label>
    <select id="subjects" name="subjects" onchange="location = this.value;">
        <option value="">Select a subject</option>
        <?php
        // Include the database connection
        include('config.php');

        // Fetch the user ID of the logged-in user from the session
        $userID = $_SESSION['user_ID'];

        // Fetch the teacher_id from the teacher table based on the logged-in user's user_ID
        $sql_teacher_id = "SELECT teacher_id FROM teacher WHERE user_ID = ?";
        $stmt_teacher_id = $conn->prepare($sql_teacher_id);
        $stmt_teacher_id->bind_param("i", $userID);
        $stmt_teacher_id->execute();
        $result_teacher_id = $stmt_teacher_id->get_result();

        if ($row_teacher_id = $result_teacher_id->fetch_assoc()) {
            $teacherID = $row_teacher_id['teacher_id'];

            // Fetch subjects taught by the current teacher
            $sql_subjects = "SELECT subjectID, subjectName FROM subject WHERE userID = ?";
            $stmt_subjects = $conn->prepare($sql_subjects);
            $stmt_subjects->bind_param("i", $teacherID);
            $stmt_subjects->execute();
            $result_subjects = $stmt_subjects->get_result();

            // Populate the dropdown with subject names and IDs
            while ($row_subject = $result_subjects->fetch_assoc()) {
                $selected = isset($_GET['subjectID']) && $_GET['subjectID'] == $row_subject['subjectID'] ?
                "selected='selected'" : "";
               
                echo "<option value='progress.php?studentID=$studentID&subjectID={$row_subject['subjectID']}' $selected>{$row_subject['subjectName']}</option>";
            }
            } else {
            echo "<option value=''>No subjects found</option>";
            }
                // Close the statement and database connection
                $stmt_teacher_id->close();
                $conn->close();
                ?>
            </select>
            </div>
            </body>
            </html>
