<?php
session_start();
include('config.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$firstName = $_SESSION['firstname'];
$lastName = $_SESSION['lastname'];
$email = $_SESSION['email'];

// Prepare and execute statement to get teacher ID
$teacherIdQuery = "SELECT teacher_id FROM teacher WHERE user_ID = ?";
$teacherIdStmt = $conn->prepare($teacherIdQuery);
$teacherIdStmt->bind_param("s", $_SESSION['user_ID']);
$teacherIdStmt->execute();
$teacherIdResult = $teacherIdStmt->get_result();
$teacherIdRow = $teacherIdResult->fetch_assoc();
$teacherIdStmt->close();

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
        $sql_subjects = "SELECT subjectName, classDay, startTime, endTime FROM subject WHERE userID = '$teacherID'";
        $result_subjects = $conn->query($sql_subjects);
        $data = array();

        if ($result_subjects->num_rows > 0) {
            while ($row_subject = $result_subjects->fetch_assoc()) {
                $row_subject['classDay'] = $row_subject['classDay'] ? explode(',', $row_subject['classDay']) : []; // Split stored string into an array or set empty array
                $data[] = $row_subject;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="profilemysubj.css">
    <title>Profile Page</title>
</head>
<body>
<div class="header">
        <img src="logo.png" alt="Logo"> </div>
    <button class="return" onclick="returnToDashboard()">Return</button>

    <button class="logout" onclick="logout()">Logout</button>
    <button class="mystudents" onclick="redirectToProfile()"> My students </button>
    <button class="myclasses"> My classes </button>
    <button class="mysubjects" > My subjects </button>
    <button class="addsubject" onclick="openModal()">Add subject</button>

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
                <button type="submit" class="modal-button">Upload Picture</button>
            </form>
            <button class="modal-button" onclick="closeProfilePicModal()">Cancel</button>
        </div>
    </div>

    <div class="profiletext">
        <?php echo $firstName . ' ' . $lastName; ?> 
        <p>Teacher ID: <?php echo $teacherIdRow['teacher_id']; ?></p>
        <p class="p2">Email:<?php echo $email; ?></p>
    </div>

    <div class="table-container">
        <table>
            <tr>
                <th>Subject Name</th>
                <th>Days</th>
                <th>Time</th>
            </tr>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?php echo $row['subjectName']; ?></td>
                    <td><?php echo implode(', ', $row['classDay']); ?></td>
                    <td><?php echo $row['startTime'] . ' - ' . $row['endTime']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div id="myModal" class="modal">
        <div class="modalbox">
            <span class="close" onclick="closeModal()">&times;</span>
            <form id="addSubjectForm" method="post" action="profilemysubjinsert.php">
                <label for="subjectName">Subject Name:</label>
                <select id="subjectName" name="subjectName">
                    <?php
                    // Fetch subject names from the storedsubj table
                    $sql_subjects = "SELECT subjectname FROM storedsubj";
                    $result_subjects = $conn->query($sql_subjects);

                    if ($result_subjects->num_rows > 0) {
                        while ($row_subject = $result_subjects->fetch_assoc()) {
                            echo "<option value='" . $row_subject['subjectname'] . "'>" . $row_subject['subjectname'] . "</option>";
                        }
                    }
                    ?>
                </select>
                <label>Class Day:</label><br>
                <?php
                // Generate checkboxes for each day of the week
                $daysOfWeek = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
                foreach ($daysOfWeek as $day) {
                    echo "<input type='checkbox' id='$day' name='classDay[]' value='$day'>";
                    echo "<label for='$day'>$day</label><br>";
                }
                ?>

                <label for="classLink">Class Link:</label>
                <input type="text" id="classLink" name="classLink" required>
                <br><br>
                <label for="startTime">Start Time:</label>
                <input type="time" id="startTime" name="startTime" required>
                <label for="endTime">End Time:</label>
                <input type="time" id="endTime" name="endTime" required>
                <button type="submit" class="add-button ">Add Subject</button>
            </form>
        </div>
    </div>

    <script>
        // JavaScript functions
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

        function openModal() {
            console.log("Opening modal");
            var modal = document.getElementById('myModal');
            modal.style.display = 'block';
        }

        function closeModal() {
            console.log("Closing modal");
            var modal = document.getElementById('myModal');
            modal.style.display = 'none';
        }

        function returnToDashboard() {
            window.location.href = "dashboard.php";
        }

        function logout() {
            window.location.href = "index.html";
        }

        function redirectToProfile() {
            window.location.href = 'profile.php';
        }
    </script>

</body>
</html>
