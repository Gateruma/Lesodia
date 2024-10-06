<?php
include('config.php');
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Get the user ID based on the logged-in username
$sql_user = "SELECT user_ID FROM user WHERE username = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $username);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $row_user = $result_user->fetch_assoc();
    $userID = $row_user['user_ID'];

    // Get the tutor ID based on the user ID
    $sql_tutor = "SELECT tutorid FROM tutor WHERE userID = ?";
    $stmt_tutor = $conn->prepare($sql_tutor);
    $stmt_tutor->bind_param("i", $userID);
    $stmt_tutor->execute();
    $result_tutor = $stmt_tutor->get_result();

    if ($result_tutor->num_rows > 0) {
        $row_tutor = $result_tutor->fetch_assoc();
        $tutorID = $row_tutor['tutorid'];

        // Get the students assigned to this tutor, along with their schedule details
        $sql_students = "
            SELECT s.studentID, u.firstname, u.lastname, 
                   sc.start_time, sc.end_time, sc.classlink, 
                   p.session, 
                   DATE_FORMAT(sc.start_time, '%M %d, %h:%i %p') as formatted_start_time, 
                   DATE_FORMAT(sc.end_time, '%M %d, %h:%i %p') as formatted_end_time
            FROM student s 
            JOIN user u ON s.userID = u.user_ID 
            JOIN paymentreq p ON s.studentID = p.studentID 
            JOIN schedule sc ON s.studentID = sc.studentID AND sc.tutorID = s.tutorid
            WHERE s.tutorid = ? AND p.status = 'accepted'";
        
        $stmt_students = $conn->prepare($sql_students);
        $stmt_students->bind_param("i", $tutorID);
        $stmt_students->execute();
        $result_students = $stmt_students->get_result();
    } else {
        echo "No tutor information found.";
        exit();
    }
} else {
    echo "User not found.";
    exit();
}

$stmt_user->close();
$stmt_tutor->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="tutorstudent.css">
    <title>Tutor Students</title>
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Students Assigned to You</h1>
    </div>

    <div class="students-list">
        <h2>My Students</h2>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Session</th>
                    <th>Class Link</th> <!-- New column for Class Link -->
                    <th>Time Left</th>
                </tr>
            </thead>
            <tbody>
<?php
if ($result_students->num_rows > 0) {
    while ($student = $result_students->fetch_assoc()) {
        $start_time = new DateTime($student['start_time']);
        $current_time = new DateTime();
        $interval = $current_time->diff($start_time);

        // Calculate time left in hours and minutes
        if ($interval->invert == 0) {
            $hours_left = $interval->h + ($interval->days * 24);
            $minutes_left = $interval->i;
            $time_left = $hours_left . " hours " . $minutes_left . " minutes";
        } else {
            $time_left = 'Class has started';
        }

        // Check if session is empty or null
        $session_display = empty($student['session']) ? "Today only" : htmlspecialchars($student['session']);

        // Check if class link exists
        $classlink_display = empty($student['classlink']) ? 
            '<button onclick="openModal(event, ' . htmlspecialchars($student['studentID']) . ')">Add Class Link</button>' : 
            '<a href="' . htmlspecialchars($student['classlink']) . '" target="_blank">Join Class</a>';

        // Display student information in a table row with tutorID and studentID in the URL
        echo '<tr onclick="window.location=\'tutorsubj.php?tutorID=' . htmlspecialchars($tutorID) . '&studentID=' . htmlspecialchars($student['studentID']) . '\'">'; // Enable row click to go to tutorsubj
        echo '<td>' . htmlspecialchars($student['firstname']) . ' ' . htmlspecialchars($student['lastname']) . '</td>';
        echo '<td>' . htmlspecialchars($student['formatted_start_time']) . '</td>';
        echo '<td>' . htmlspecialchars($student['formatted_end_time']) . '</td>';
        echo '<td>' . $session_display . '</td>'; // Display session or "today only"
        echo '<td>' . $classlink_display . '</td>'; // Display class link or "Add Class Link" button
        echo '<td>' . htmlspecialchars($time_left) . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="6">No students assigned.</td></tr>'; 
}
?>
            </tbody>
        </table>
    </div>

    <!-- Modal for adding class link -->
    <div id="classLinkModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add Class Link</h2>
            <form id="classLinkForm" method="POST" action="add_classlink.php">
                <input type="hidden" id="studentID" name="studentID">
                <label for="classlink">Class Link:</label>
                <input type="url" id="classlink" name="classlink" required>
                <button type="submit">Save</button>
            </form>
        </div>
    </div>

    <button onclick="window.history.back()">Back</button>

    <script>
        // Get the modal
        var modal = document.getElementById("classLinkModal");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks the button, open the modal and set the studentID
        function openModal(event, studentID) {
            event.stopPropagation(); // Prevent row click from triggering
            document.getElementById("studentID").value = studentID;
            modal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
