<?php
include('config.php');

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}



$user_id = $_SESSION['user_ID'];

// Query to get the student ID and teacher ID
$sql_student = "SELECT studentID, teacherID FROM student WHERE userID = $user_id";
$result_student = $conn->query($sql_student);

if ($result_student->num_rows > 0) {
    $row_student = $result_student->fetch_assoc();
    $studentID = $row_student['studentID'];
    $teacherID = $row_student['teacherID'];

    // Query to get the teacher's user ID
    $sql_teacher_userID = "SELECT user_ID FROM teacher WHERE teacher_id = $teacherID";
    $result_teacher_userID = $conn->query($sql_teacher_userID);

    if ($result_teacher_userID->num_rows > 0) {
        $row_teacher_userID = $result_teacher_userID->fetch_assoc();
        $teacher_user_ID = $row_teacher_userID['user_ID'];

        // Query to get the subjects assigned to the teacher
        $sql_subjects = "SELECT subjectID, subjectName, startTime, endTime, classLink
                         FROM subject
                         WHERE userID = $teacherID";

        $result_subjects = $conn->query($sql_subjects);

        if ($result_subjects->num_rows > 0) {
            $subjects = $result_subjects->fetch_all(MYSQLI_ASSOC);
        } else {
            $subjects = [];
        }
    } else {
        $teacher_user_ID = null;
        $subjects = [];
    }
} else {
    $studentID = null;
    $teacherID = null;
    $teacher_user_ID = null;
    $subjects = [];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KlaseMo</title>
    <link rel="stylesheet" href="studentclass.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
</head>

<body>

    <div class="header">
        <img src="logo.png" alt="Logo">
    </div>

    <button class="dashboard" onclick="redirectToDashboard()"> Dashboard </button>
    <button class="classrecord" onclick="redirectToClassrecord()">Classes</button>
    <button class="myprofile" onclick="redirectToMyprofile()"> My profile </button>
    <button class="playground" onclick="redirectToMyTutor()"> My tutor </button> <!-- Changed here -->

    <script>
        function redirectToDashboard() {
            window.location.href = 'studentdashboard.php';
        }

        function redirectToClassrecord() {
            window.location.href = '';
        }

        function redirectToClasses() {
            window.location.href = 'your_classes_page.php';
        }

        function redirectToMyprofile() {
            window.location.href = 'studentprofile.php';
        }

        function redirectToMyTutor() { // Added this function
            window.location.href = 'mytutor.php';
        }

        function redirectToClass(subjectID, classLink) {
            if (classLink) {
                window.open(classLink, '_blank');
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'insertClass.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        console.log(xhr.responseText);
                    }
                };
                xhr.send(`subjectID=${subjectID}&classID=<?php echo time(); ?>&userID=<?php echo $_SESSION['user_ID']; ?>&date=<?php echo date('Y-m-d'); ?>`);
            } else {
                alert("Class link not available for Subject ID: " + subjectID);
            }
        }

        function redirectToClassLink(classLink) {
            if (classLink) {
                window.open(classLink, '_blank');
            } else {
                alert("Class link not available");
            }
        }
    </script>

    <?php

    if (!empty($subjects)) {
        echo "<table class='table'>";
        echo "<tr>";
        echo "<th>Subject</th>";
        echo "<th>Time</th>";
        echo "<th>Link</th>";
        echo "</tr>";

        foreach ($subjects as $subject) {
            echo "<tr onclick=\"redirectToClass({$subject['subjectID']}, '{$subject['classLink']}')\">";
            echo "<td>" . $subject['subjectName'] . "</td>";
            echo "<td>" . $subject['startTime'] . "-" . $subject['endTime'] . "</td>";
            echo "<td>" . $subject['classLink'] . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p>No subjects available</p>";
    }
    ?>

</body>

</html>

<?php
$conn->close();
?>
