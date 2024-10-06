<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Checking</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        .header {
    background-color: #1F6C52;
    color: black;
    font-family: 'Tahoma', sans-serif;
    font-size: 39px; 
    font-weight: bolder;
    text-align: start;
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1;
    padding: 10px; /* Added padding for spacing around the image */
} 

        .header img {
            width: 330px;
            /* Adjust the width of the image */
            height: 90px;
            /* Allow the height to adjust proportionally */
            display: block;
            /* Ensures proper block-level display */
            margin: 0 auto;
            /* Center the image horizontally */
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #edf5fd; /* Warm color - Light Salmon */

        }

        table {
            border-collapse: collapse;
            width: 50%;
            margin: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .return-button {
            position: absolute;
            top: 18%;
            left: 4%;
            width: 8%;
            height: 6%;
            border-radius: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        button {
            padding: 10px 15px;
            cursor: pointer;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 4px;
        }

        button.absent {
            background-color: #f44336;

        }

        button.clicked {
            background-color: #9c27b0;
        }

        button:disabled {
            background-color: #ddd;
            cursor: not-allowed;
        }
    </style>
</head>
<div class="header">
        <img src="logo.png" alt="Logo">
    </div>
<div style="position: fixed; bottom: 10px; left: 10px;">
    <p>Class ID: <?php echo isset($_GET['classID']) ? $_GET['classID'] : 1; ?></p>
</div>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('config.php');

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_ID'])) {
    header("Location: login.php");
    exit();
}

$classID = isset($_GET['classID']) ? $_GET['classID'] : 1; // Default to 1 if not provided

// Now you can safely access $_SESSION['user_ID'] and other session variables
$loggedInUserID = $_SESSION['user_ID'];

// Connect to the database
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to mark attendance and submit data to the database
function markAttendance($conn, $studentId, $status)
{
    // Your markAttendance function code here
}

// Fetch student data from the database for the logged-in teacher
$sql = "SELECT student.studentID, user.firstname AS first_name, user.lastname AS last_name
        FROM student
        INNER JOIN user ON student.userID = user.user_ID
        INNER JOIN teacher ON student.teacherID = teacher.teacher_id
        WHERE teacher.user_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $loggedInUserID);
$stmt->execute();
$result = $stmt->get_result();

// Display the table with students and a button to mark attendance
if ($result->num_rows > 0) {
    echo '<table>';
    echo '<tr><th>ID</th><th>Name</th><th>Action</th></tr>';

    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['studentID'] . '</td>';
        echo '<td>' . $row['first_name'] . ' ' . $row['last_name'] . '</td>';
        echo '<td data-student-id="' . $row['studentID'] . '"><button onclick="markAttendance(' . $row['studentID'] . ', \'Present\')" class="present">Mark Present</button>';
        echo '<button onclick="markAttendance(' . $row['studentID'] . ', \'Absent\')" class="absent">Mark Absent</button></td>';
        echo '</tr>';
    }

    echo '</table>';
} else {
    echo 'No students found in the database for the logged-in teacher.';
}

// Close the database connection
$stmt->close();
$conn->close();
?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Check if there is a stored table content in sessionStorage
        var storedTableContent = sessionStorage.getItem("tableContent");

        // If there is stored content, update the table
        if (storedTableContent) {
            var table = document.querySelector('table');
            table.innerHTML = storedTableContent;

            // Reattach event listeners or any additional logic if needed
        }

        function returnToDashboard() {
            window.location.href = 'dashboard.php';
        }

        function markAttendance(studentId, status) {
            // Your existing markAttendance function

            // Save the current table content to sessionStorage
            var table = document.querySelector('table');
            sessionStorage.setItem("tableContent", table.innerHTML);
        }

        function undoAttendance(studentId) {
            // Your existing undoAttendance function

            // Save the current table content to sessionStorage
            var table = document.querySelector('table');
            sessionStorage.setItem("tableContent", table.innerHTML);
        }

        // Add an event listener to clear sessionStorage on leaving the page
        window.addEventListener('beforeunload', function () {
            sessionStorage.removeItem("tableContent");
        });
    });

    function markAttendance(studentId, status) {
        // Send an AJAX request to mark attendance in the database
        var xhr = new XMLHttpRequest();
        var subjectId = <?php echo isset($_GET['subjectID']) ? $_GET['subjectID'] : 1; ?>;
        var classID = <?php echo isset($_GET['classID']) ? $_GET['classID'] : 1; ?>;

        xhr.open('POST', 'markattendance.php?subjectID=' + subjectId, true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    // Handle the response if needed
                    console.log(xhr.responseText);
                    // Display a confirmation message

                    // Update the button container to display "Undo" button
                    var buttonContainer = document.querySelector('td[data-student-id="' + studentId + '"]');
                    if (buttonContainer) {
                        buttonContainer.innerHTML = '<button onclick="undoAttendance(' + studentId + ')" class="undo">Undo</button>';
                    }
                } else {
                    // Handle errors if any
                    console.error('Failed to submit attendance. Status: ' + xhr.status);
                }
            }
        };

        // Include classID in the data sent with the request
        var requestData = 'studentId=' + studentId + '&status=' + status + '&classID=' + classID;

        xhr.send(requestData);
    }

    function undoAttendance(studentId) {
        // Send an AJAX request to undo attendance in the database
        var xhr = new XMLHttpRequest();
        var subjectId = <?php echo isset($_GET['subjectID']) ? $_GET['subjectID'] : 1; ?>;
        var classID = <?php echo isset($_GET['classID']) ? $_GET['classID'] : 1; ?>;

        xhr.open('POST', 'markattendance.php?subjectID=' + subjectId, true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    // Handle the response if needed
                    console.log(xhr.responseText);
                    // Display a confirmation message
                    alert('Attendance undone successfully for userID: ' + studentId);

                    // Update the button container to display "Mark Present" and "Mark Absent" buttons
                    var buttonContainer = document.querySelector('td[data-student-id="' + studentId + '"]');
                    if (buttonContainer) {
                        buttonContainer.innerHTML = '<button onclick="markAttendance(' + studentId + ', \'Present\')" class="present">Mark Present</button>' +
                            '<button onclick="markAttendance(' + studentId + ', \'Absent\')" class="absent">Mark Absent</button>';
                    }
                } else {
                    // Handle errors if any
                    console.error('Failed to undo attendance. Status: ' + xhr.status);
                }
            }
        };

        // Include classID in the data sent with the request
        var requestData = 'studentId=' + studentId + '&status=Undo&classID=' + classID;

        xhr.send(requestData);
    }
</script>

<button class="return-button" type="button" onclick="returnToDashboard()">Return to Dashboard</button>

</body>

</html>
