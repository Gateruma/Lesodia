<?php
// Start or resume the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_ID'])) {
    // Redirect to the login page or handle unauthorized access
    header("Location: login.php");
    exit(); // Ensure script execution stops here
}

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

// Check if a teacher_id was fetched
if ($row_teacher_id = $result_teacher_id->fetch_assoc()) {
    $teacherID = $row_teacher_id['teacher_id'];

    // Fetch student IDs belonging to the current teacher
    $sql_students = "SELECT s.studentID, u.firstname, u.lastname FROM student s JOIN user u ON s.userID = u.user_ID WHERE s.teacherID = ?";
    $stmt_students = $conn->prepare($sql_students);
    $stmt_students->bind_param("i", $teacherID);
    $stmt_students->execute();
    $result_students = $stmt_students->get_result();
} else {
    // Handle case where teacher ID is not found for the logged-in user
    echo "Teacher ID not found for the logged-in user.";
}

// Close the statement and database connection
$stmt_teacher_id->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="classrecord.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <title>Class Record</title>

</head>
<style>
    body {
    background-color: #edf5fd; /* Warm color - Light Salmon */
    
}
</style>
<body>
    <div class="header">
        <img src="logo.png" alt="Logo">
    </div>
    <button class="dashboard" onclick="redirectToDashboard()"> Dashboard </button>
    <button class="classrecord"> Class record </button>
    <button class="class" onclick="redirectToClasses()">Classes</button>
    <button class="myprofile" onclick="redirectToMyprofile()"> My profile </button>

    <!-- Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p>This is the modal content</p>
        </div>
    </div>

    <div class="table-container">
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">First Name</th>  <!-- Separate column for First Name -->
                <th scope="col">Last Name</th>   <!-- Separate column for Last Name -->
            </tr>
        </thead>
        <tbody>
            <?php
            // Check if there are rows returned from the student query
            if ($result_students->num_rows > 0) {
                // Output data of each student
                while ($row_student = $result_students->fetch_assoc()) {
                    // Create the link URL
                    $link = "progress.php?studentID=" . $row_student['studentID'] . "&studentName=" . urlencode($row_student['firstname'] . ' ' . $row_student['lastname']);
                    echo "<tr onclick=\"window.location.href='$link'\" style='cursor: pointer;'>"; // Make the whole row clickable
                    // Display first name and last name in separate columns
                    echo "<td>" . $row_student['firstname'] . "</td>";
                    echo "<td>" . $row_student['lastname'] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2'>No students found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>


    <script>
        function redirectToDashboard() {
            window.location.href = 'dashboard.php';
        }

        function redirectToClasses() {
            window.location.href = 'classes.php';
        }

        function redirectToMyprofile() {
            window.location.href = 'profile.php';
        }

        function openModal() {
            var modal = document.getElementById('myModal');
            modal.style.display = 'block';
        }

        function closeModal() {
            var modal = document.getElementById('myModal');
            modal.style.display = 'none';
        }
    </script>
</body>

</html>
