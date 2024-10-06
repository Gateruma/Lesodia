<?php
session_start();

// Your database connection and configuration
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data from the session
$username = $_SESSION['username'];
$firstName = $_SESSION['firstname'];
$lastName = $_SESSION['lastname'];
$email = $_SESSION['email'];

$teacherIdQuery = "SELECT teacher_id FROM teacher WHERE user_ID = ?";
$teacherIdStmt = $conn->prepare($teacherIdQuery);
$teacherIdStmt->bind_param("s", $_SESSION['user_ID']);
$teacherIdStmt->execute();
$teacherIdResult = $teacherIdStmt->get_result();
$teacherIdRow = $teacherIdResult->fetch_assoc();
$teacherIdStmt->close();

// Function to update the teacherID for a student
function updateStudentTeacherID($studentID, $teacherID, $conn) {
    // Ensure that the student with $studentID exists
    $checkSql = "SELECT * FROM student WHERE studentID = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $studentID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    // If the student exists, update the teacherID
    if ($checkResult->num_rows > 0) {
        $sql = "UPDATE student SET teacherID = ? WHERE studentID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $teacherID, $studentID);
        $stmt->execute();
        $stmt->close();
    } else {
        // Handle the case where the student does not exist
        // You may want to log an error or take appropriate action
        echo "Error: Student with ID $studentID does not exist.";
    }

    $checkStmt->close();
}

// Function to add a student or update teacherID if student already exists
function addStudent($studentID, $teacherID, $conn) {
    // Check if the student with the given ID exists
    $checkSql = "SELECT * FROM student WHERE studentID = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $studentID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    // If the student exists, update the teacherID
    if ($checkResult->num_rows > 0) {
        $updateSql = "UPDATE student SET teacherID = ? WHERE studentID = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("is", $teacherID, $studentID);
        $updateStmt->execute();
        $updateStmt->close();
    } else {
        // If the student does not exist, display an alert
        echo "<script>alert('Student not found');</script>";
    }

    $checkStmt->close();
}

// Function to remove a student by setting teacherID to NULL
function removeStudent($studentID, $conn) {
    // Ensure that the student with $studentID exists
    $checkSql = "SELECT * FROM student WHERE studentID = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $studentID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    // If the student exists, set teacherID to NULL
    if ($checkResult->num_rows > 0) {
        $sql = "UPDATE student SET teacherID = NULL WHERE studentID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $studentID);
        $stmt->execute();
        $stmt->close();
    } else {
        // Handle the case where the student does not exist
        // You may want to log an error or take appropriate action
        echo "Error: Student with ID $studentID does not exist.";
    }

    $checkStmt->close();
}

// Function to fetch student data based on studentID
function getStudentData($studentID, $conn) {
    $sql = "SELECT student.studentID, user.firstname AS student_first, user.lastname AS student_last
            FROM student
            INNER JOIN user ON student.userID = user.user_ID
            WHERE student.studentID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $studentID);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    return $result->fetch_assoc();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the form is the addStudentForm
    if (isset($_POST["addStudentForm"])) {
        $studentID = $_POST["studentID"];

        // Get the current teacher's ID
        $teacherID = $_SESSION['user_ID'];

        // Call the function to add the student
        addStudent($studentID, $teacherID, $conn);

        // Call the function to fetch and display student data
        $newStudentData = getStudentData($studentID, $conn);
    }

    // Check if the form is the updateStudentForm
    if (isset($_POST["updateStudentForm"])) {
        $studentIDToUpdate = $_POST["studentIDToUpdate"];

        // Get the current teacher's ID
        $teacherID = $_SESSION['user_ID'];

        // Call the function to update the teacherID for the specific student
        updateStudentTeacherID($studentIDToUpdate, $teacherID, $conn);

        // Call the function to fetch and display student data
        $updatedStudentData = getStudentData($studentIDToUpdate, $conn);
    }

    // Check if the form is the removeStudentForm
    if (isset($_POST["removeStudentForm"])) {
        $studentIDToRemove = $_POST["studentIDToRemove"];

        // Call the function to remove the student
        removeStudent($studentIDToRemove, $conn);

        // Optionally, you can fetch and display removed student data
        $removedStudentData = getStudentData($studentIDToRemove, $conn);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="profile.css">
    <title>Profile Page</title>
</head>
<body>
    <p class="header">KlaseMo</p>

    <button class="return" id="returnButton">Return</button>

    <div class="header">
        <img src="logo.png" alt="Logo">
    </div>

<script>
    document.getElementById('returnButton').addEventListener('click', function() {
        window.history.back();
    });

    function logout() {
        window.location.href = "logout.php";
    }

    function openAddModal() {
        closeUpdateModal();  // Close update modal if open
        document.getElementById('addStudentModal').style.display = 'block';
    }

    function closeAddModal() {
        document.getElementById('addStudentModal').style.display = 'none';
    }

    function submitAddForm() {
        closeAddModal();
        return false;
    }

    function openUpdateModal() {
        closeAddModal();  // Close add modal if open
        document.getElementById('select').style.display = 'block';
    }

    function closeUpdateModal() {
        document.getElementById('select').style.display = 'none';
    }

    function submitUpdateForm() {
        closeUpdateModal();
        return false;
    }

    function openRemoveModal() {
        closeUpdateModal();  // Close update modal if open
        closeAddModal();     // Close add modal if open
        document.getElementById('removeStudentModal').style.display = 'block';
    }

    function closeRemoveModal() {
        document.getElementById('removeStudentModal').style.display = 'none';
    }

    function submitRemoveForm() {
        closeRemoveModal();
        return false;
    }

    function redirectToMySubjects() {
            window.location.href = "";
        }
</script>

    <button class="logout" onclick="logout()">Logout</button>

    <div class="profilepic">
    </div>

    <div class="profiletext">
    <?php echo $firstName . ' ' . $lastName; ?> 
    <!-- <p class="p2">Email: <?php echo $email; ?></p> -->
    <?php
    // Retrieve studentID based on the current logged-in userID
    $studentIdQuery = "SELECT studentID FROM student WHERE userID = ?";
    $studentIdStmt = $conn->prepare($studentIdQuery);
    $studentIdStmt->bind_param("s", $_SESSION['userID']);
    $studentIdStmt->execute();
    $studentIdResult = $studentIdStmt->get_result();
    $studentIdRow = $studentIdResult->fetch_assoc();
    $studentIdStmt->close();

    // Display studentID if found
    if ($studentIdRow) {
        echo "<p>Student ID: " . $studentIdRow['studentID'] . "</p>";
    } else {
        echo "<p>Student ID not found</p>";
    }
$conn->close();
    ?>
</div>
</body>
</html>
