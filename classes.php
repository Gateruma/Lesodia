<?php
include('config.php');
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KlaseMo</title>
    <link rel="stylesheet" href="classes.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>

<body>

    <div class="header">
        <img src="logo.png" alt="Logo">
    </div>
    <button class="dashboard" onclick="redirectToDashboard()"> Dashboard</button>
    <button class="classrecord" onclick="redirectToClassrecord()"> Class record</button>
    <button class="class" onclick="redirectToClasses()"> Classes</button>
    <button class="myprofile" onclick="redirectToMyprofile()"> My profile </button>

    <script>
        function redirectToDashboard() {
            window.location.href = 'dashboard.php';
        }

        function redirectToClassrecord() {
            window.location.href = 'classrecord.php';
        }

        function redirectToClasses() {
            window.location.href = 'your_classes_page.php';
        }

        function redirectToMyprofile() {
            window.location.href = 'profile.php';
        }

        function redirectToClass(subjectID, classLink) {
            if (classLink) {
                var generatedClassID = Date.now() % 100000;

                window.open(classLink, '_blank');

                var harinoLink = `harino.php?subjectID=${subjectID}&classID=${generatedClassID}`;
                window.open(harinoLink, '_blank');

                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'insertClass.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        console.log(xhr.responseText);
                    }
                };

                xhr.send(`subjectID=${subjectID}&classID=${generatedClassID}&userID=<?php echo $_SESSION['user_ID']; ?>&date=<?php echo date('Y-m-d'); ?>`);
            } else {
                alert("Class link not available for Subject ID: " + subjectID);
            }
        }
    </script>
    <div class="table-container">

    <table class="table table-hover"> <!-- Added table-hover class here -->
        <tr>
            <th>Subject</th>
            <th>Time</th>
            <th>Class Day</th>
            <th>Link</th>
        </tr>

        <?php
        $username = $_SESSION['username'];
        $sql_user = "SELECT user_ID FROM user WHERE username = '$username'";
        $result_user = $conn->query($sql_user);

        if ($result_user->num_rows > 0) {
            $row_user = $result_user->fetch_assoc();
            $userID = $row_user['user_ID'];

            $sql_teacher = "SELECT teacher_id FROM teacher WHERE user_ID = '$userID'";
            $result_teacher = $conn->query($sql_teacher);

            if ($result_teacher->num_rows > 0) {
                $row_teacher = $result_teacher->fetch_assoc();
                $teacherID = $row_teacher['teacher_id'];

                $sql_subjects = "SELECT * FROM subject WHERE userID = '$teacherID'";
                $result_subjects = $conn->query($sql_subjects);

                if ($result_subjects->num_rows > 0) {
                    while ($row_subject = $result_subjects->fetch_assoc()) {
                        $startTime = date("h:i A", strtotime($row_subject['startTime']));
                        $endTime = date("h:i A", strtotime($row_subject['endTime']));

                        // Create a clickable row
                        echo "<tr onclick=\"redirectToClass('{$row_subject['subjectID']}', '{$row_subject['classLink']}')\">"; // Removed classID as it's not needed
                        echo "<td>" . htmlspecialchars($row_subject['subjectName']) . "</td>";
                        echo "<td>{$startTime} - {$endTime}</td>";
                        echo "<td>" . htmlspecialchars($row_subject['classDay']) . "</td>";
                        echo "<td>" . htmlspecialchars($row_subject['classLink']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No subjects available</td></tr>";
                }
            } else {
                echo "<tr><td colspan='4'>Teacher ID not found for the logged-in user</td></tr>";
            }
        } else {
            echo "<tr><td colspan='4'>User not found</td></tr>";
        }
        ?>
    </table>
    </div>
</body>

</html>

<?php
$conn->close();
?>
