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

// Retrieve parentid based on the current logged-in userID
$parentIdQuery = "SELECT parentid, userID FROM parent WHERE userID = ?";
$parentIdStmt = $conn->prepare($parentIdQuery);
$parentIdStmt->bind_param("s", $_SESSION['user_ID']);
$parentIdStmt->execute();
$parentIdResult = $parentIdStmt->get_result();
$parentIdRow = $parentIdResult->fetch_assoc();
$parentIdStmt->close();

// Fetch teacherID based on the current logged-in userID
$teacherIdQuery = "SELECT teacher_id FROM teacher WHERE user_ID = ?";
$teacherIdStmt = $conn->prepare($teacherIdQuery);
$teacherIdStmt->bind_param("s", $_SESSION['user_ID']);
$teacherIdStmt->execute();
$teacherIdResult = $teacherIdStmt->get_result();
$teacherIdRow = $teacherIdResult->fetch_assoc();
$teacherIdStmt->close();

$conn->close();
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
    // Display Parent ID and User ID if found
    if ($parentIdRow) {
        echo "<p>User ID: " . $_SESSION['user_ID'] . "</p>";
        echo "<p>Parent ID: " . $parentIdRow['parentid'] . "</p>";
    } else {
        echo "<p>User ID: Not found</p>";
        echo "<p>Parent ID: Not found</p>";
    }
    ?>
</div>
</body>
</html>
