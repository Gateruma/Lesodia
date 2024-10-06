<?php
include('config.php');

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$firstName = $_SESSION['firstname'];
$lastName = $_SESSION['lastname'];
$email = $_SESSION['email'];


// Fetch student data based on the current logged-in user
$sql_student = "
    SELECT * FROM student 
    WHERE userID = (SELECT user_ID FROM user WHERE username = '$username' LIMIT 1)
";
$result_student = $conn->query($sql_student);

if ($result_student->num_rows > 0) {
    $row_student = $result_student->fetch_assoc();
    $studentID = $row_student['studentID'];
    $userID = $row_student['userID'];
    $teacherID = $row_student['teacherID'];
} else {
    echo "Student information not found.";
}

// Fetch subject data based on the URL parameter
$subject = $_GET['subject'];

// Fetch lessons
$sql_lessons = "SELECT lesson.lessonid, lesson.title, NULL AS deadline FROM lesson
                JOIN subject ON lesson.subjectid = subject.subjectID
                WHERE subject.subjectName = '$subject' AND lesson.teacherid = $teacherID";
$result_lessons = $conn->query($sql_lessons);

$lessons = array();

if ($result_lessons->num_rows > 0) {
    while ($row_lesson = $result_lessons->fetch_assoc()) {
        $row_lesson['deadline'] = ''; // Set the deadline to an empty string for lessons
        $lessons[] = $row_lesson;
    }
}

// Fetch block types based on the teacher ID and subject ID
$sql_block_types = "SELECT blocks_type.blockid, blocks_type.title, blocks_type.deadline 
                    FROM blocks_type 
                    JOIN subject ON blocks_type.subjectID = subject.subjectID 
                    WHERE subject.subjectName = '$subject' AND blocks_type.teacherid = '$teacherID'";
$result_block_types = $conn->query($sql_block_types);

$block_types = array();

if ($result_block_types->num_rows > 0) {
    while ($row_block_type = $result_block_types->fetch_assoc()) {
        $block_types[] = $row_block_type;
    }
}

// Fetch essays
$sql_essays = "SELECT et.title, et.deadline 
                FROM essay_type et
                JOIN subject s ON et.subjectID = s.subjectID
                LEFT JOIN remedial r ON et.essayid = r.essayid AND r.studentid = $studentID
                WHERE s.subjectName = '$subject' AND et.teacherid = $teacherID 
                AND (et.type <> 'remedial' OR r.studentid IS NOT NULL)";
$result_essays = $conn->query($sql_essays);

$essays = array();

if ($result_essays->num_rows > 0) {
    while ($row_essay = $result_essays->fetch_assoc()) {
        $essays[] = $row_essay;
    }
}

// Fetch guesstypes
$sql_guesstypes = "SELECT g.guessid, g.title, g.deadline, g.items
                    FROM guesstype g
                    JOIN subject s ON g.subjectID = s.subjectID
                    WHERE s.subjectName = '$subject' AND g.teacherid = $teacherID";
$result_guesstypes = $conn->query($sql_guesstypes);

$guesstypes = array();

if ($result_guesstypes->num_rows > 0) {
    while ($row_guesstype = $result_guesstypes->fetch_assoc()) {
        $guesstypes[] = $row_guesstype;
    }
}


$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="aftersubject.css">
    <title>Document</title>
    <style>
        #deadlineModal {
            display: none;
            position: fixed;
            z-index: 1;
            padding-top: 100px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 400px;
            height: 280px;
            text-align: center;
            /* Center the content horizontally */
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;

        }

        .close {
            color: #aaaaaa;
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .past-deadline-text {
            color: red;
        }

    </style>

</head>

<body>
    <div class="header">
        <img src="logo.png" alt="Logo">
    </div>
    <button class="dashboard" onclick="redirectToDashboard()"> Dashboard </button>
    <button class="classrecord" onclick="redirectToClasses()">Classes</button>
    <button class="myprofile" onclick="redirectToMyprofile()"> My profile </button>
    <script>
        function redirectToClasses() {
            window.location.href = '';
        }

        function redirectToDashboard() {
            window.location.href = 'studentdashboard.php'
        }

        function redirectToFilipino() {
            window.location.href = 'subject.html';
        }

        function redirectToMyprofile() {
            window.location.href = 'studentprofile.php';
        }

    </script>
    <div class="line" style="font-size: 60px; display: flex; justify-content: center; align-items: center;">
        <?php echo $subject; ?>
    </div>

    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p class="box1">Multiple Choice Type</p>
            <p class="box2">True/False Type</p>
            <p class="box3">Matching Type</p>
            <p class="box4">Short Answer Type</p>
            <p class="box5">Numerical Type</p>
            <p class="box6" onclick="openBox1Modal()">Essay Question Type</p>
            <p class="box7" onclick="openLessonModal()">Lesson</p>
        </div>
    </div>

    <!-- Add a new modal for box1 -->
    <div id="box1Modal" class="box1modal">
        <div class="modal-content">
            <span class="close" onclick="closeBox1Modal()">&times;</span>
            <form id="essayForm" method="post" action="insert_essay.php?subject=<?php echo urlencode($subject); ?>">
            <input type="text" name="essayTitle" placeholder="Enter your text..">
                <p class="shape"></p>
                <textarea name="essayQuestion" class="container" rows="4" cols="50" placeholder="Enter your text..."></textarea>
                <p class="assessment">Assessment title</p>
                <p class="instruction">Content</p>
                <div class="shape">
                    <input type="datetime-local" name="essayDeadline">
                    <input type="number" name="essayScore" placeholder="Score">
                    <button type="submit" class="add">Add</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add a new modal for the "Lesson" -->
    <div id="lessonModal" class="modal1">
        <div class="modal-content1">
            <span class="close" onclick="closeLessonModal()">&times;</span>
            <form id="lessonForm" method="post" action="insert_lesson.php?subject=<?php echo urlencode($subject); ?>" enctype="multipart/form-data">
                <h1>Adding lesson</h1>

                <label for="lessonTitle" class="instruction2">Title:</label>
                <input type="text" id="lessonTitle" name="lessonTitle" placeholder="Enter the lesson title" required>
                <div class="boxxx">
                    <label for="lessonContent" class="instruction1">Content:</label>
                    <input type="file" class="file" id="fileUpload" name="file" multiple accept=".pdf, .doc, .docx, .jpg, .jpeg, .png, .mp4">

                </div>
                <textarea class="container" id="lessonContent" name="lessonContent" rows="4" cols="50" placeholder="Enter the lesson content" required></textarea>
                <button type="submit" class="add">Add Lesson</button>

            </form>
        </div>
    </div>

    <div id="deadlineModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p style="font-size: 25px;">The deadline for this essay has passed!</p>
        </div>
    </div>

    <table>
        <tr>
            <th>Title</th>
            <th>Deadline</th>
        </tr>
        <!-- Display lessons -->
        <?php if (empty($lessons) && empty($essays) && empty($guesstypes)) : ?>
            <tr>
                <td colspan="2">No activities yet. Make one by adding!</td>
            </tr>
        <?php else : ?>
            <?php foreach ($lessons as $lesson) : ?>
                <tr>
                    <td>
                        <a href="openfile.php?type=lesson&id=<?php echo $lesson['lessonid']; ?>">
                            <?php echo $lesson['title']; ?>
                        </a>
                    </td>
                    <td>
                        <?php echo ($lesson['deadline'] !== '') ? $lesson['deadline'] : 'No Deadline'; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php foreach ($block_types as $block_type) : ?>
                <tr onclick="goToPlayground(<?php echo $block_type['blockid']; ?>)">
                    <td>
                        <?php echo $block_type['title']; ?>
                    </td>
                    <td><?php echo ($block_type['deadline'] !== '') ? $block_type['deadline'] : 'No Deadline'; ?></td>
                </tr>
            <?php endforeach; ?>

            <?php foreach ($essays as $essay) : ?>
                <?php
                $deadline_parts = explode(" ", $essay['deadline']);
                $deadline_date = $deadline_parts[0];
                $deadline_time = $deadline_parts[1];

                $date_name = date("l, F jS, Y", strtotime($deadline_date));

                $time_name = date("h:i A", strtotime($deadline_time));

                $current_date = date("Y-m-d");
                $current_time = date("H:i:s");

                $deadline_passed = ($current_date > $deadline_date) || ($current_date == $deadline_date && $current_time > $deadline_time);
                ?>
                <tr>
                    <td <?php echo ($deadline_passed) ? 'onclick="openDeadlineModal()"' : ''; ?>>
                        <a href="<?php echo ($deadline_passed) ? '#' : 'studentopenfile.php?type=essay&title=' . urlencode($essay['title']); ?>">
                            <?php echo $essay['title']; ?>
                        </a>
                    </td>
                    <td>
                        <?php echo ($deadline_passed) ? '<span class="past-deadline-text">Deadline: ' . $date_name . ', at ' . $time_name . '</span>' : 'Deadline: ' . $date_name . ', at ' . $time_name; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php foreach ($guesstypes as $guesstype) : ?>
                <?php
                $deadline_parts = explode(" ", $guesstype['deadline']);
                $deadline_date = $deadline_parts[0];
                $deadline_time = $deadline_parts[1];

                $date_name = date("l, F jS, Y", strtotime($deadline_date));

                $time_name = date("h:i A", strtotime($deadline_time));

                $current_date = date("Y-m-d");
                $current_time = date("H:i:s");

                $deadline_passed = ($current_date > $deadline_date) || ($current_date == $deadline_date && $current_time > $deadline_time);
                ?>
                <tr>
                <td <?php echo ($deadline_passed) ? 'onclick="openDeadlineModal()"' : ''; ?>>
                <a href="<?php echo ($deadline_passed) ? '#' : 'guessname.php?type=guesstype&title=' . urlencode($guesstype['title']) . '&items=' . urlencode($guesstype['items']) . '&guessid=' . urlencode($guesstype['guessid']); ?>">
    <?php echo $guesstype['title']; ?>
</a>

</td>

                    <td>
                        <?php echo ($deadline_passed) ? '<span class="past-deadline-text">Deadline: ' . $date_name . ', at ' . $time_name . '</span>' : 'Deadline: ' . $date_name . ', at ' . $time_name; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

        <?php endif; ?>
    </table>


    <script>
        function closeModal() {
            var deadlineModal = document.getElementById('deadlineModal');
            deadlineModal.style.display = 'none';
        }

        function goToPlayground(blockId) {
            window.location.href = "playground.php?blockid=" + blockId;
        }

        function openDeadlineModal() {
            var deadlineModal = document.getElementById('deadlineModal');
            deadlineModal.style.display = 'block';
        }
    </script>
</body>

</html>


