<?php
include('config.php');

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Replace getSubjectID() with your logic to retrieve the subject ID
$subjectID = isset($_GET['subjectID']) ? $_GET['subjectID'] : null;

$sql = "SELECT student.studentID, user.user_ID, user.firstname AS first_name, user.lastname AS last_name
        FROM student
        INNER JOIN user ON student.userID = user.user_ID
        INNER JOIN teacher ON student.teacherID = teacher.teacher_id
        WHERE teacher.user_ID = (
            SELECT user_ID FROM user WHERE username = ?
        )";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$students = [];

while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="checkattendance.css">
    <title>Attendance Management</title>
    <style>
        table {
            border-collapse: collapse;
            position: relative;
            width: 65%;
            margin-top: 230px;
            left: 18%;
        }

        th,
        td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Style for the Remarks cells */
        td.remarks {
            background-color: #8FE381;
            color: white;
            vertical-align: middle;
            text-align: center;
            cursor: pointer;
        }

        .checked {
            background-color: white !important;
            color: #8FE381;
            font-weight: bold;
        }

        .return-button {
            position: absolute;
            top: 18%;
            left: 4%;
            width: 8%;
            height: 6%;
            border-radius: 15px;
        }

        .submit-button {
            position: absolute;
            top: 24%;
            left: 4%;
            width: 8%;
            height: 6%;
            border-radius: 15px;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }

        .confirmation-message {
            margin-top: 20px;
            text-align: center;
            display: none;
        }

        .loading-spinner {
            display: none;
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left: 4px solid #8FE381;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        /* New style for the selected row */
        tr.selected {
            background-color: #c7e1e4;
        }

        #subjectIDDisplay {
            margin-top: 20px;
            text-align: center;
            font-size: 18px;
        }
    </style>
</head>

<body>

    <form id="attendanceForm" method="POST" action="submitattendance.php">
        <?php foreach ($students as $student) { ?>
            <input type="hidden" name="user_IDs[]" value="<?php echo $student['user_ID']; ?>">
            <input type="hidden" name="subject_ID" value="<?php echo $subjectID; ?>">
        <?php } ?>

        <table>
            <tr>
                <th>Last Name</th>
                <th>First Name</th>
                <th class="remarks">Remarks</th>
            </tr>
            <?php foreach ($students as $student) { ?>
                <tr data-student-id="<?php echo $student['user_ID']; ?>">
                    <td><?php echo $student['last_name']; ?></td>
                    <td><?php echo $student['first_name']; ?></td>
                    <td class="remarks" onclick="toggleCheckedAndSubmit(this, '<?php echo $student['user_ID']; ?>')">Present</td>
                </tr>
            <?php } ?>
        </table>

        <button class="return-button" type="button" onclick="returnToDashboard()">Return to Dashboard</button>

        <!-- Add the submit button -->
        <button class="submit-button" type="submit">Submit Attendance</button>

        <div class="loading-spinner" id="loadingSpinner"></div>
        <div class="confirmation-message" id="confirmationMessage"></div>
    </form>

    <div id="subjectIDDisplay">Subject ID: <?php echo $subjectID; ?></div>

    <script>
        // Array to store selected students
        var selectedStudents = [];

        function returnToDashboard() {
            // Submit the attendance data before redirecting
            submitAttendanceData();
            // Redirect to the dashboard
            window.location.href = 'dashboard.php';
        }

        function toggleCheckedAndSubmit(cell, studentID) {
            toggleChecked(cell, studentID);
            displaySelectedStudents();
        }

        function toggleChecked(cell, studentID) {
            // Check if the student is already selected
            var index = selectedStudents.indexOf(studentID);

            if (index === -1) {
                // If not selected, add to the array
                selectedStudents.push(studentID);
            } else {
                // If already selected, remove from the array
                selectedStudents.splice(index, 1);
            }

            // Remove the 'selected' class from all rows
            document.querySelectorAll('tr').forEach(row => {
                row.classList.remove('selected');
            });

            // Add 'selected' class to the clicked row
            cell.parentNode.classList.add('selected');

            // Toggle the 'checked' class
            cell.classList.toggle('checked');

            // Update the attendance status text
            cell.innerHTML = cell.classList.contains('checked') ? 'Checked' : 'Present';
            cell.style.color = cell.classList.contains('checked') ? 'black' : 'white';

            // Set data attributes to track the student's attendance status
            cell.setAttribute('data-attendance-status', cell.classList.contains('checked') ? 'Present' : 'Absent');
            cell.setAttribute('data-student-id', studentID);
        }

        async function submitAttendanceData() {
            document.getElementById('loadingSpinner').style.display = 'block';

            var formData = new FormData();

            selectedStudents.forEach(studentID => {
                // Check if the student is marked as present
                var cell = document.querySelector(`tr[data-student-id='${studentID}'] td.remarks`);
                if (cell.classList.contains('checked')) {
                    var status = cell.getAttribute('data-attendance-status');
                    formData.append('user_IDs[]', studentID);
                    formData.append('statuses[]', status);
                }
            });

            try {
                var response = await fetch('submitattendance.php', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    // Handle success as needed
                } else {
                    alert('Error submitting attendance data.');
                }
            } catch (error) {
                alert('Network error. Please try again.');
            } finally {
                document.getElementById('loadingSpinner').style.display = 'none';
            }
        }

        function displaySelectedStudents() {
            // Display the selected student IDs at the top of the page
            document.getElementById('subjectIDDisplay').innerHTML = 'Subject ID: <?php echo $subjectID; ?> - Selected Student IDs: ' + selectedStudents.join(', ');
        }

        function submitAttendance(event) {
            event.preventDefault();
            // Submit the attendance data
            submitAttendanceData();
            // Redirect to the dashboard
            window.location.href = 'dashboard.php';
        }

        // Add this function to send attendance data when the page loads
        window.onload = function () {
            submitAttendanceData();
        };
    </script>
</body>

</html>
