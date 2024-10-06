<?php
include('config.php');
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Get the user ID based on the logged-in username
$sql_user = "SELECT user_ID FROM user WHERE username = '$username'";
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

        // Fetch subjects data including subjectID based on the teacher ID
        $sql_subjects = "SELECT subjectID, subjectName FROM subject WHERE userID = '$teacherID' ORDER BY subjectID";
        $result_subjects = $conn->query($sql_subjects);
        $subjects = array();

        if ($result_subjects->num_rows > 0) {
            while ($row_subject = $result_subjects->fetch_assoc()) {
                // Store subject name and ID in separate arrays
                $subjects[] = array(
                    'subjectName' => $row_subject['subjectName'],
                    'subjectID' => $row_subject['subjectID']
                );
            }
        }

        // Check if the user is already a tutor
        $sql_check_tutor = "SELECT * FROM tutor WHERE userid = '$userID'";
        $result_check_tutor = $conn->query($sql_check_tutor);
        $isTutor = $result_check_tutor->num_rows > 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lilita+One&display=swap" rel="stylesheet">
    <title>Dashboard</title>
    <style>
        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Black with opacity */
        }
        .modal-content {
            background-color: #fff;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 300px; /* Could be more or less, depending on screen size */
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="logo.png" alt="Logo">
    </div>

    <?php if (count($subjects) > 0) : ?>
        <?php for ($i = 0; $i < min(8, count($subjects)); $i++) : ?>
            <div class="box<?php echo $i + 1; ?>" onclick="redirectToSubject('<?php echo $subjects[$i]['subjectName']; ?>', '<?php echo $subjects[$i]['subjectID']; ?>')">
                <p><?php echo $subjects[$i]['subjectName']; ?></p>
            </div>
        <?php endfor; ?>
    <?php else : ?>
        <p>No subjects available</p>
    <?php endif; ?>

    <button class="dashboard" onclick="redirectToDashboard()"> Dashboard </button>
    <button class="classrecord" onclick="redirectToClassrecord()"> Class record </button>
    <button class="class" onclick="redirectToClasses()">Classes</button>
    <button class="myprofile" onclick="redirectToMyprofile()"> My profile </button>
    
    <!-- Conditional button for tutor status -->
    <button class="myservice" onclick="<?php echo $isTutor ? 'redirectToTutorDashboard()' : 'checkIfTutor()'; ?>">
        <?php echo $isTutor ? 'My Service' : 'Be a Tutor'; ?>
    </button>

    <div class="boxer">Dashboard</div>

    <!-- Modal for offering service -->
    <div id="offerServiceModal" class="modal">
        <div class="modal-content">
            <p>Do you want to offer your services as a tutor?</p>
            <button onclick="confirmOfferService()">Yes</button>
            <button onclick="closeModal()">No</button>
        </div>
    </div>

    <script>
        function redirectToDashboard() {
            window.location.href = 'dashboard.php';
        }

        function redirectToClassrecord() {
            window.location.href = 'classrecord.php';
        }

        function redirectToSubject(subject, subjectID) {
            window.location.href = `aftersubject.php?subject=${subject}&subjectID=${subjectID}`;
        }

        function redirectToClasses() {
            window.location.href = 'classes.php';
        }

        function redirectToMyprofile() {
            window.location.href = 'profile.php';
        }

        function redirectToTutorDashboard() {
            window.location.href = 'tutordashboard.php';
        }

        function openModal() {
            document.getElementById('offerServiceModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('offerServiceModal').style.display = 'none';
        }

        // Check if the current user is a tutor
        function checkIfTutor() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'check_tutor_status.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // If not a tutor, open the modal
                    if (xhr.responseText === 'not_tutor') {
                        openModal();
                    }
                }
            };
            xhr.send();
        }

        // Handle the confirmation to offer service
        function confirmOfferService() {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'offer_service.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert(xhr.responseText);
                    closeModal();
                    // Reload the page to update the button text
                    location.reload();
                }
            };
            xhr.send();
        }
    </script>
</body>

</html>
