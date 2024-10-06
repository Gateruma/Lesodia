<?php
include('config.php');

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Query to retrieve the userID of the logged-in user
$userIDQuery = "SELECT user_ID FROM user WHERE username = ?";
$userIDStmt = $conn->prepare($userIDQuery);
$userIDStmt->bind_param("s", $username);
$userIDStmt->execute();
$userIDResult = $userIDStmt->get_result();
$userIDRow = $userIDResult->fetch_assoc();
$userIDStmt->close();

// Store userID in session if found
if ($userIDRow) {
    $_SESSION['userID'] = $userIDRow['user_ID'];
}

// Retrieve teacher ID based on the current logged-in userID
$teacherIdQuery = "SELECT teacherID FROM student WHERE userID = ?";
$teacherIdStmt = $conn->prepare($teacherIdQuery);
$teacherIdStmt->bind_param("i", $_SESSION['userID']);
$teacherIdStmt->execute();
$teacherIdResult = $teacherIdStmt->get_result();
$teacherIdRow = $teacherIdResult->fetch_assoc();
$teacherIdStmt->close();

// Retrieve subjects assigned to the teacherID of the current logged-in student
$studentTeacherId = isset($teacherIdRow['teacherID']) ? $teacherIdRow['teacherID'] : null;

$sql_subjects = "SELECT subjectID, subjectName, classDay, startTime, endTime FROM subject WHERE userID = ? ORDER BY subjectID";
$subjectsStmt = $conn->prepare($sql_subjects);
$subjectsStmt->bind_param("i", $studentTeacherId);
$subjectsStmt->execute();
$result_subjects = $subjectsStmt->get_result();
$subjects = array();

if ($result_subjects->num_rows > 0) {
    while ($row_subject = $result_subjects->fetch_assoc()) {
        $subjects[] = array(
            'subjectID' => $row_subject['subjectID'],
            'subjectName' => $row_subject['subjectName'],
            'classDay' => $row_subject['classDay'],
            'startTime' => $row_subject['startTime'],
            'endTime' => $row_subject['endTime']
        );
    }
}

$subjectsStmt->close();

// Retrieve teachers if no teacher is assigned to the student
if (!$studentTeacherId) {
    $teachersQuery = "
        SELECT t.teacher_id, u.firstname, u.lastname 
        FROM teacher t
        JOIN user u ON t.user_ID = u.user_ID";
    $teachersResult = $conn->query($teachersQuery);
    $teachers = array();

    if ($teachersResult->num_rows > 0) {
        while ($row_teacher = $teachersResult->fetch_assoc()) {
            $teachers[] = array(
                'teacherID' => $row_teacher['teacher_id'],
                'teacherName' => $row_teacher['firstname'] . ' ' . $row_teacher['lastname']
            );
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="studentdashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <title>Student Dashboard</title>
    <style>
        /* Modal styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px; 
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; 
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
    <img src="logo.png" alt="Logo">
</div>

<?php if (count($subjects) > 0) : ?>
    <div class="dashboard-boxes">
        <?php for ($i = 0; $i < min(6, count($subjects)); $i++) : ?>
            <div class="box<?php echo $i + 1; ?>" onclick="redirectToSubject('<?php echo $subjects[$i]['subjectName']; ?>', '<?php echo $subjects[$i]['subjectID']; ?>')">
                <p><?php echo $subjects[$i]['subjectName']; ?></p>
            </div>
        <?php endfor; ?>
    </div>
<?php else : ?>
    <?php if (!$studentTeacherId && isset($teachers) && count($teachers) > 0) : ?>
        <div class="teacher-selection">
            <h2>No tutor yet, want to select one?</h2>
            <table>
                <thead>
                    <tr>
                        <th>Teacher Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $teacher) : ?>
                        <tr>
                            <td><?php echo $teacher['teacherName']; ?></td>
                            <td>
                                <button onclick="viewTeacherDetails('<?php echo $teacher['teacherID']; ?>')">Select</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <p>No teachers available at the moment.</p>
    <?php endif; ?>
<?php endif; ?>

<div class="boxer">Dashboard</div>

<button class="dashboard" onclick="redirectToDashboard()"> Dashboard </button>
<?php if ($studentTeacherId) : ?>
    <button class="classrecord" onclick="redirectToClasses()">Classes</button>
<?php endif; ?>
<button class="myprofile" onclick="redirectToMyprofile()"> My profile </button>


<button class="mytutor" onclick="redirectToMyTutor()"> My tutor </button>

<div class="profiletext">
    <?php
    // Display teacher ID if found
    if ($teacherIdRow) {
        echo "<p>Teacher ID: " . $teacherIdRow['teacherID'] . "</p>";
    } else {
        echo "<p>Teacher ID not found</p>";
    }
    ?>
</div>

<!-- Modal for teacher details -->
<div id="teacherModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Teacher Details</h2>
        <p id="teacherName"></p>
        <p id="teacherEmail"></p>
        <p id="teacherAddress"></p>
        <p id="teacherGender"></p>
        <p id="teacherDOB"></p>
    </div>
</div>

<script>
    function redirectToMyTutor() {
        window.location.href = 'mytutor.php';
    }

    // Existing functions...
    function redirectToDashboard() {
        window.location.href = 'studentdashboard.php';
    }

    function redirectToSubject(subject, subjectID) {
        window.location.href = 'studentaftersubj.php?subject=' + encodeURIComponent(subject) + '&subjectID=' + encodeURIComponent(subjectID);
    }

    function redirectToClasses() {
        window.location.href = 'studentclass.php';
    }

    function redirectToMyprofile() {
        window.location.href = 'studentprofile.php';
    }

    // Existing modal and teacher details functions...
    function viewTeacherDetails(teacherID) {
        // Fetch teacher details and display in the modal
        fetch('get_teacher_details.php?teacherID=' + teacherID)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }

                document.getElementById('teacherName').innerText = 'Name: ' + data.firstname + ' ' + data.lastname;
                document.getElementById('teacherEmail').innerText = 'Email: ' + data.email;
                document.getElementById('teacherAddress').innerText = 'Address: ' + data.address;
                document.getElementById('teacherGender').innerText = 'Gender: ' + data.gender;
                document.getElementById('teacherDOB').innerText = 'Date of Birth: ' + data.dateofbirth;

                // Show the modal
                document.getElementById('teacherModal').style.display = 'block';
            })
            .catch(error => console.error('Error fetching teacher details:', error));
    }

    function closeModal() {
        document.getElementById('teacherModal').style.display = 'none';
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        if (event.target == document.getElementById('teacherModal')) {
            closeModal();
        }
    }
</script>


</body>

</html>
