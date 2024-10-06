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

// Retrieve the teacher ID of the logged-in teacher
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

    // If the student exists
    if ($checkResult->num_rows > 0) {
        $studentData = $checkResult->fetch_assoc();
        // If the student already has the current logged-in teacher's teacherID assigned
        if ($studentData['teacherID'] == $teacherID) {
            // Display an alert that the student is already assigned to the current teacher
            echo "<script>alert('Student is already assigned to you.');</script>";
        } else {
            // If the student exists but is not assigned to the current teacher, update the teacherID
            $updateSql = "UPDATE student SET teacherID = ? WHERE studentID = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("is", $teacherID, $studentID);
            $updateStmt->execute();
            $updateStmt->close();
            // Alert that the student has been successfully added to the class
            echo "<script>alert('Student added to your class successfully.');</script>";
        }
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
        $teacherID = $teacherIdRow['teacher_id'];

        // Call the function to add the student
        addStudent($studentID, $teacherID, $conn);

        // Call the function to fetch and display student data
        $newStudentData = getStudentData($studentID, $conn);
    }

    // Check if the form is the updateStudentForm
    if (isset($_POST["updateStudentForm"])) {
        $studentIDToUpdate = $_POST["studentIDToUpdate"];

        // Get the current teacher's ID
        $teacherID = $teacherIdRow['teacher_id'];

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

// Modify your SQL query to fetch only the students assigned to the current logged-in teacher
$sql = "SELECT student.studentID, user.firstname AS student_first, user.lastname AS student_last
        FROM student
        INNER JOIN user ON student.userID = user.user_ID
        WHERE student.teacherID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacherIdRow['teacher_id']); // Assuming teacher_id is an integer
$stmt->execute();
$result = $stmt->get_result();

// Close the statement after fetching data
$stmt->close();

// Fetched students' data will be stored in this array
$students = [];

while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}


// Get the user ID based on the logged-in username
$sql_user = "SELECT user_ID, profpic FROM user WHERE username = '$username'";
$result_user = $conn->query($sql_user);

if ($result_user->num_rows > 0) {
    $row_user = $result_user->fetch_assoc();
    $userID = $row_user['user_ID'];

    // Get the teacher ID based on the user ID
    $sql_teacher = "SELECT teacher_id FROM teacher WHERE user_ID = '$userID'";
    $result_teacher = $conn->query($sql_teacher);

    if ($result_teacher->num_rows > 0) {
        $row_teacher = $result_teacher->fetch_assoc();
        $teacherID = $row_teacher['teacher_id'];

        // Fetch subjects data based on the teacher ID
        $sql_subjects = "SELECT subjectName, startTime, endTime FROM subject WHERE userID = '$teacherID'";
        $result_subjects = $conn->query($sql_subjects);
        $data = array();

        if ($result_subjects->num_rows > 0) {
            while ($row_subject = $result_subjects->fetch_assoc()) {
                $data[] = $row_subject;
            }
        }
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;700&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    
    <title>Profile Page</title>
</head>
<body>
<div class="header">
        <img src="logo.png" alt="Logo"> </div>

    <button class="return" onclick="returnToDashboard()">Return</button>
    <button class="buttonn">Requests</button>

    <table>
        <tr>
            <th>StudentID</th>
            <th>Last Name</th>
            <th>First Name</th>
        </tr>
        <?php
        // Display students in the table
        foreach ($students as $student) {
            echo "<tr>";
            echo "<td>{$student['studentID']}</td>";
            echo "<td>{$student['student_last']}</td>";
            echo "<td>{$student['student_first']}</td>";
            echo "</tr>";
        }
        ?>
    </table>

    <button class="addsubject" onclick="openUpdateModal()">Edit student</button>
    <button class="updatestudent" onclick="openUpdateModal()">Update student</button>

    <!-- Modal HTML for adding student -->
    <div id="addStudentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <div class="edit" ><h2>Add Student</h2> </div>
            <form id="addStudentForm" onsubmit="submitAddForm()" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <input type="text" name="studentID" id="searchInput" onkeyup="getSearchSuggestions(this.value)" placeholder="Search...">
        <div id="searchSuggestions"></div>
                <!-- Add a hidden input to identify the form submission -->
                <input type="hidden" name="addStudentForm" value="1">
                <button type="submit" class="buttonsub" >Add</button>
            </form>
        </div>
    </div>

    



    <!-- Modal HTML for updating student -->
    <div id="select" class="modal" >
        <div class="modal-content">
            <span class="close" onclick="closeUpdateModal()">&times;</span>
            <div class="edit" ><h2 >Edit Students</h2> </div>
            <button onclick="openAddModal()" style="background-color:#B2F2AD;">Add</button>
            <button onclick="openRemoveModal()" style="background-color:#F4A2A2;">Remove</button>
        </div>
    </div>

    <!-- Modal HTML for removing student -->
    <div id="removeStudentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRemoveModal()">&times;</span>
            <div class="edit" ><h2>Remove Student</h2> </div>
            <form id="removeStudentForm" onsubmit="submitRemoveForm()" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <input type="text" name="studentIDToRemove" required>
                <p>Input student ID to remove</p>
                <!-- Add a hidden input to identify the form submission -->
                <input type="hidden" name="removeStudentForm" value="1">
                <button type="submit" class="buttonsub">Remove</button>
            </form>
        </div>
    </div>

    <?php
    // Display the newly added student data
    if (isset($newStudentData)) {
        echo "<div>";
        echo "<h3>Newly Added Student:</h3>";
        echo "<p>StudentID: {$newStudentData['studentID']}</p>";
        echo "<p>Last Name: {$newStudentData['student_last']}</p>";
        echo "<p>First Name: {$newStudentData['student_first']}</p>";
        echo "</div>";
    }

    // Display the updated student data
    if (isset($updatedStudentData)) {
        echo "<div>";
        echo "<h3>Updated Student:</h3>";
        echo "<p>StudentID: {$updatedStudentData['studentID']}</p>";
        echo "<p>Last Name: {$updatedStudentData['student_last']}</p>";
        echo "<p>First Name: {$updatedStudentData['student_first']}</p>";
        echo "</div>";
    }
    ?>
<script>

    function returnToDashboard() {
            window.location.href = "dashboard.php";
        }

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
            window.location.href = "profilemysubj.php";
        }

                function getSearchSuggestions(input) {
            if (input.length == 0) {
                document.getElementById("searchSuggestions").innerHTML = "";
                return;
            } else {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById("searchSuggestions").innerHTML = this.responseText;
                    }
                };
                xhttp.open("GET", "get_search_suggestions.php?input=" + input, true);
                xhttp.send();
            }
        }

        function selectSuggestion(suggestion) {
            // Split the suggestion to extract studentID
            var parts = suggestion.split(' - ');
            var studentID = parts[0];
            // Set the value of the search input field to the selected studentID
            document.getElementById('searchInput').value = studentID;
            // Hide the search suggestions container
            document.getElementById('searchSuggestions').style.display = 'none';
        }

        function openProfilePicModal() {
        var modal = document.getElementById('profilePicModal');
        modal.style.display = 'block';
    }

    function closeProfilePicModal() {
        var modal = document.getElementById('profilePicModal');
        modal.style.display = 'none';
    }

    function changePicture() {
        // Your code for changing the picture
        console.log("Change picture clicked");
    }

    function removePicture() {
        // Your code for removing the picture
        console.log("Remove picture clicked");
    }
</script>

    <button class="logout" onclick="logout()">Logout</button>
    <button class="mystudents"> My students </button>
    <button class="myclasses"> My classes </button>
    <button class="mysubjects" onclick="redirectToMySubjects()"> My subjects </button>

    <div class="profilepic" onclick="openProfilePicModal()">
    <?php
    if ($row_user && isset($row_user['profpic']) && !empty($row_user['profpic'])) {
        $imageData = base64_encode($row_user['profpic']);
        $src = 'data:image/jpeg;base64,' . $imageData;
        echo '<img src="' . $src . '" alt="Profile Picture">';
    } else {
        echo '<button class="add-profile-pic" onclick="openModal()">+</button>';
    }
    ?>
</div>
<div id="profilePicModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeProfilePicModal()">&times;</span>
        <?php
        if ($row_user && isset($row_user['profpic']) && !empty($row_user['profpic'])) {
            $imageData = base64_encode($row_user['profpic']);
            $src = 'data:image/jpeg;base64,' . $imageData;
            echo '<img src="' . $src . '" alt="Profile Picture" style="max-width: 100%; max-height: 100%;">';
        }
        ?>
        <form id="uploadForm" method="post" enctype="multipart/form-data" action="upload_profile_pic.php">
            <input type="file" name="fileToUpload" id="fileToUpload" accept="image/*">
            <button type="submit" >Upload Picture</button>
        </form>
        <button  onclick="closeProfilePicModal()">Cancel</button>
    </div>
</div>

    <div class="profiletext">
    <?php echo $firstName . ' ' . $lastName; ?> 
    <p>Teacher ID: <?php echo $teacherIdRow['teacher_id']; ?></p>
    <p class="p2">Email:<?php echo $email; ?></p>
</div>

</body>
</html>
