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

// Fetch subject data based on the URL parameter
$subject = $_GET['subject'];

// Fetch teacher ID of the currently logged-in user
$sql_teacher_id = "SELECT teacher_id FROM teacher WHERE user_ID = (SELECT user_ID FROM user WHERE username = '$username')";
$result_teacher_id = $conn->query($sql_teacher_id);

if ($result_teacher_id->num_rows > 0) {
    $row_teacher_id = $result_teacher_id->fetch_assoc();
    $teacherID = $row_teacher_id['teacher_id'];

    // Fetch subject ID based on the subject name
    $sql_subject_id = "SELECT subjectID FROM subject WHERE subjectName = '$subject' LIMIT 1";
    $result_subject_id = $conn->query($sql_subject_id);

    if ($result_subject_id->num_rows > 0) {
        $row_subject_id = $result_subject_id->fetch_assoc();
        $subjectID = $row_subject_id['subjectID'];

        // Fetch lessons based on the teacher ID and subject ID
        $sql_lessons = "SELECT lesson.lessonid, lesson.title, NULL AS deadline FROM lesson
                        JOIN subject ON lesson.subjectid = subject.subjectID
                        WHERE subject.subjectName = '$subject' AND lesson.teacherid = '$teacherID'";
        $result_lessons = $conn->query($sql_lessons);

        $lessons = array();

        if ($result_lessons->num_rows > 0) {
            while ($row_lesson = $result_lessons->fetch_assoc()) {
                $row_lesson['deadline'] = ''; // Set the deadline to an empty string for lessons
                $lessons[] = $row_lesson;
            }
        }

        // Fetch essays based on the teacher ID and subject ID
        $sql_essays = "SELECT essay_type.essayid, essay_type.title, essay_type.deadline FROM essay_type
        JOIN subject ON essay_type.subjectID = subject.subjectID
        WHERE subject.subjectName = '$subject' AND essay_type.teacherID = '$teacherID'";

        $result_essays = $conn->query($sql_essays);

        $essays = array();

        if ($result_essays->num_rows > 0) {
            while ($row_essay = $result_essays->fetch_assoc()) {
                $essays[] = $row_essay;
            }
        }

// Fetch guesstype data based on the teacher ID and subject ID
$sql_guesstype = "SELECT guessid, title, deadline FROM guesstype WHERE subjectid = '$subjectID' AND teacherid = '$teacherID'";
$result_guesstype = $conn->query($sql_guesstype);

$guesstype_title = ""; // Initialize variable to hold the guesstype title
$guesstype_deadline = ""; // Initialize variable to hold the guesstype deadline
$guesstype_id = ""; // Initialize variable to hold the guesstype ID

if ($result_guesstype->num_rows > 0) {
    $row_guesstype = $result_guesstype->fetch_assoc();
    $guesstype_title = $row_guesstype['title'];
    $guesstype_deadline = $row_guesstype['deadline'];
    $guesstype_id = $row_guesstype['guessid']; // Assign guesstype ID
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


    } else {
        echo "Subject not found.";
        exit();
    }
} else {
    echo "Teacher not found.";
    exit();
}

include('config.php');
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="aftersubject.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <title>Document</title>
</head>

<body>
    <div class="header">
        <img src="logo.png" alt="Logo">
    </div>

    <button class="dashboard" onclick="redirectToDashboard()"> Dashboard </button>
    <button class="classrecord"> Class record </button>
    <button class="class" onclick="redirectToClasses()">Classes</button>
    <button class="myprofile" onclick="redirectToMyprofile()"> My profile </button>

    <div class="line">
        <!-- <div class="addbutton" onclick="openModal()"> Add </div> -->
        <div class="editbutton" onclick="openModal()"> Add </div>
    </div>

    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>

            <p class="box1" onclick="openBlocksModal()">Blocks</p>
            <p class="box2" onclick="openVirtualPerceptionModal()">Visual Perception</p>
            <p class="box3" onclick="openBox1Modal()">Essay Question Type</p>
            <p class="box5" onclick="openLessonModal()">Lesson</p>
        </div>
    </div>

<div id="blocksModal" class="blocksmodal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeBlocksModal()">&times;</span>
        <form id="blocksForm" method="post" action="insert_blocks.php?subject=<?php echo urlencode($subject); ?>&subjectID=<?php echo urlencode($_GET['subjectID']); ?>" enctype="multipart/form-data">
            <input type="text" name="blocksTitle" placeholder="Enter the block title.." required>
            <input type="text" name="blocksInstruction" placeholder="Enter block instructions.." required>
            <input type="file" name="blocksImage" accept="image/jpeg, image/png" required>
            <input type="number" name="blocksScore" placeholder="Enter the score" required>
            <input type="datetime-local" name="blocksDeadline" required>

            <button type="submit" class="add">Add Block</button>
        </form>
    </div>
</div>


    <!-- Add a new modal for box1 -->
    <div id="box1Modal" class="box1modal">
        <div class="modal-content">
            <span class="close" onclick="closeBox1Modal()">&times;</span>
            <!-- Modify the form action to include the subject ID -->
            <form id="essayForm" method="post" action="insert_essay.php?subject=<?php echo urlencode($subject); ?>&subjectID=<?php echo urlencode($_GET['subjectID']); ?>">
                <input type="text" name="essayTitle" placeholder="Enter your text..">
                <p class="shape"></p>
                <textarea name="essayQuestion" class="container" rows="4" cols="50" placeholder="Enter your text..."></textarea>
                <p class="assessment">Assessment title</p>
                <p class="instruction">Content</p>
                <p class="score1"> Score</p>
                <p class="deadline1"> Deadline</p>

                <div class="shape">
                    <input type="datetime-local" class="deadline" name="essayDeadline">
                    <input type="number" name="essayScore" class="score" placeholder="Score">
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

    <table>
        <tr>
            <th>Title</th>
            <th>Deadline</th>
        </tr>

        <?php
// Ensure subjectID is present in the URL
if(isset($_GET['subjectID'])) {
    $subjectID = $_GET['subjectID'];

    // Fetch guesstype data based on the teacher ID and subject ID
    $sql_guesstype = "SELECT guessid, title, deadline FROM guesstype WHERE subjectid = '$subjectID' AND teacherid = '$teacherID'";
    $result_guesstype = $conn->query($sql_guesstype);

    if ($result_guesstype->num_rows > 0) {
        while ($row_guesstype = $result_guesstype->fetch_assoc()) {
            $guesstype_id = $row_guesstype['guessid'];
            $guesstype_title = $row_guesstype['title'];
            $guesstype_deadline = $row_guesstype['deadline'];
?>
            <!-- Display guesstype title -->
            <tr>
                <td>
                    <a href="openfile.php?type=guesstype&id=<?php echo $guesstype_id; ?>&subjectID=<?php echo urlencode($subjectID); ?>&teacherID=<?php echo $teacherID; ?>">
                        <?php echo $guesstype_title; ?>
                    </a>
                </td>
                <td><?php echo ($guesstype_deadline !== '') ? $guesstype_deadline : 'No Deadline'; ?></td>
            </tr>
<?php
        }
    } 
} else {
    // If subjectID is not present in the URL, display an error message
    echo "<tr><td colspan='2'>SubjectID not found in the URL.</td></tr>";
}
?>

        <!-- Display lessons -->
        <?php foreach ($lessons as $lesson) : ?>
            <tr>
                <td>
                    <a href="openfile.php?type=lesson&id=<?php echo $lesson['lessonid']; ?>&subjectID=<?php echo urlencode($_GET['subjectID']); ?>&teacherID=<?php echo $teacherID; ?>">
                        <?php echo $lesson['title']; ?>
                    </a>
                </td>
                <td><?php echo ($lesson['deadline'] !== '') ? $lesson['deadline'] : 'No Deadline'; ?></td>
            </tr>
        <?php endforeach; ?>

<!-- Display block problems -->
<?php foreach ($block_types as $block_type) : ?>
    <tr>
        <td>
            <a href="openfile.php?type=block&id=<?php echo $block_type['blockid']; ?>&subjectID=<?php echo urlencode($_GET['subjectID']); ?>&teacherID=<?php echo $teacherID; ?>">
                <?php echo $block_type['title']; ?>
            </a>
        </td>
        <td><?php echo ($block_type['deadline'] !== '') ? $block_type['deadline'] : 'No Deadline'; ?></td>
    </tr>
<?php endforeach; ?>


<div id="virtualPerceptionModal" class="virtual-perception-modal">
    <div class="modal-content">
        <span class="close" onclick="closeVirtualPerceptionModal()">&times;</span>
        <form id="virtualPerceptionForm" method="post" action="insert_virtual_perception.php">
            <input type="text" name="title" placeholder="Enter title.." required> <!-- New field for the title -->
            <input type="text" name="itemTitle" placeholder="Enter item " required>
            <input type="hidden" name="subjectID" value="<?php echo $subjectID; ?>">
            <input type="datetime-local" name="itemDeadline" required>
            <button type="submit" class="add">Add Item</button>
        </form>
    </div>
</div>

        <!-- Display essays -->
        <?php foreach ($essays as $essay) : ?>
            <tr>
                <td>
                    <a href="openfile.php?type=essay&id=<?php echo $essay['essayid']; ?>&subjectID=<?php echo urlencode($_GET['subjectID']); ?>&teacherID=<?php echo $teacherID; ?>">
                        <?php echo $essay['title']; ?>
                    </a>
                </td>
                <?php
                // Splitting deadline into date and time
                $deadline_parts = explode(" ", $essay['deadline']);
                $deadline_date = $deadline_parts[0];
                $deadline_time = $deadline_parts[1];

                // Converting date to date names
                $date_name = date("l, F jS, Y", strtotime($deadline_date));

                // Getting the time in AM/PM format
                $time_name = date("h:i A", strtotime($deadline_time));
                ?>
                <td>
                    <?php echo $date_name; ?>, at <?php echo $time_name; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <script>
function openVirtualPerceptionModal() {
    var virtualPerceptionModal = document.getElementById('virtualPerceptionModal');
    virtualPerceptionModal.style.display = 'block';
}

function closeVirtualPerceptionModal() {
    var virtualPerceptionModal = document.getElementById('virtualPerceptionModal');
    virtualPerceptionModal.style.display = 'none';
}


        function redirectToClasses() {
            window.location.href = 'classes.php';
        }

        function redirectToDashboard() {
            window.location.href = 'dashboard.php';
        }

        function openModal() {
            var modal = document.getElementById('myModal');
            modal.style.display = 'block';
        }

        function closeModal() {
            var modal = document.getElementById('myModal');
            modal.style.display = 'none';
        }

        function openBox1Modal() {
            var box1Modal = document.getElementById('box1Modal');
            box1Modal.style.display = 'block';
        }

        function closeBox1Modal() {
            var box1Modal = document.getElementById('box1Modal');
            box1Modal.style.display = 'none';
        }

        function openLessonModal() {
            var lessonModal = document.getElementById('lessonModal');
            lessonModal.style.display = 'block';
        }

        function closeLessonModal() {
            var lessonModal = document.getElementById('lessonModal');
            lessonModal.style.display = 'none';
        }

        function openBlocksModal() {
            var blocksModal = document.getElementById('blocksModal');
            blocksModal.style.display = 'block';
        }

        function closeBlocksModal() {
            var blocksModal = document.getElementById('blocksModal');
            blocksModal.style.display = 'none';
        }
    </script>
</body>

</html>
