<?php
session_start();
include("config.php");

// Check if the user is logged in
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






// Close any database connections or other resources here if needed
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KlaseMo - View Submission</title>
    <link rel="stylesheet" href="viewsubmission.css">
</head>

<body>


<div class="header">
        <img src="logo.png" alt="Logo">
    </div>

    <?php
    include('config.php');

    // Retrieve the response ID from the URL parameters
    $responseID = isset($_GET['responseid']) ? $_GET['responseid'] : null;

    // Initialize placeholder for score
    $placeholderScore = "Score here";

    // Fetch the score for the selected essay
    $sql_score = "SELECT score FROM essay_type WHERE essayid IN (SELECT essayid FROM response WHERE responseid = ?)";
    $stmt_score = $conn->prepare($sql_score);
    $stmt_score->bind_param("i", $responseID);
    $stmt_score->execute();
    $result_score = $stmt_score->get_result();

    // Display the score in the input placeholder
    if ($row_score = $result_score->fetch_assoc()) {
        $placeholderScore = $row_score['score'];
    }

    // Close the score statement
    $stmt_score->close();
    
    ?>


<?php
// Include your database connection and necessary PHP code before this section

// Fetch the logged-in teacher's ID (you need to implement this based on your authentication system)
$teacherID = $teacherIdRow['teacher_id']; // Assign the value to $teacherID

// Fetch students assigned to the teacher
$sql_students = "SELECT u.firstname, u.lastname FROM user u 
                 INNER JOIN student s ON u.user_ID = s.userID 
                 WHERE s.teacherID = ?";
$stmt_students = $conn->prepare($sql_students);
$stmt_students->bind_param("i", $teacherID);
$stmt_students->execute();
$result_students = $stmt_students->get_result();
$students = array();

while ($row_students = $result_students->fetch_assoc()) {
    $students[] = $row_students['firstname'] . ' ' . $row_students['lastname'];
}
$stmt_students->close();
?>

<div id="myModal" class="modal" >
    <div class="modal-content2">
        <span class="close2" >&times;</span>
        <p style="text-align: center;">The score percentage is below 75%. Would you like to assign a remedial?</p>
        <div class="button-container" style="margin-top: 20px; text-align: center;">
            <button class="review-button" onclick="reviewSubmission()">Yes</button>
            <button class="cancel-button" onclick="submitScore()">No</button>
        </div>
    </div>
</div>


    <div class="linames"></div>
    <div class="content">
    <?php
    // Fetch the student's first name, last name, and studentID from the user and student tables
    $sql_student_info = "SELECT u.firstname, u.lastname, s.studentID FROM user u 
                        INNER JOIN student s ON u.user_ID = s.userID 
                        WHERE s.studentID IN (SELECT studentID FROM response WHERE responseid = ?)";
    $stmt_student_info = $conn->prepare($sql_student_info);
    $stmt_student_info->bind_param("i", $responseID);
    $stmt_student_info->execute();
    $result_student_info = $stmt_student_info->get_result();

            $essayID = $_GET['essayid'];

// Fetch the essay title based on the essayid
$sql_essay_title = "SELECT title FROM essay_type WHERE essayid = ?";
$stmt_essay_title = $conn->prepare($sql_essay_title);
$stmt_essay_title->bind_param("i", $essayID);
$stmt_essay_title->execute();
$result_essay_title = $stmt_essay_title->get_result();

// Check if the essay title is found
if ($row_essay_title = $result_essay_title->fetch_assoc()) {
    $essayTitle = $row_essay_title['title'];

    // Display the student's info and ID with the essay title
    if ($row_student_info = $result_student_info->fetch_assoc()) {
        $studentID = $row_student_info['studentID'];
        echo "<div class='student-id' >" . $row_student_info['firstname'] . " " . $row_student_info['lastname'] . "'s submission: " . $essayTitle . "</div>";
    } else {
        echo "<div class='student-id'>Unknown</div>";
        $studentID = "Unknown";
    }
} else {
    // Essay title not found
    $essayTitle = "Essay Title Not Found";
    echo "<div class='student-id'>Unknown</div>";
    $studentID = "Unknown";
}




// Close statements and result sets
$stmt_essay_title->close();
$result_student_info->close();


    // Fetch the question for the selected essay
    $sql_question = "SELECT question FROM essay_type WHERE essayid IN (SELECT essayid FROM response WHERE responseid = ?)";
    $stmt_question = $conn->prepare($sql_question);
    $stmt_question->bind_param("i", $responseID);
    $stmt_question->execute();
    $result_question = $stmt_question->get_result();

    // Display the question
    if ($row_question = $result_question->fetch_assoc()) {
        echo "<div class='question-content'>" . $row_question['question'] . "</div>";
    } else {
        echo "<div class='no-question'>No question found for this essay.</div>";
    }

    // Fetch the student's answer and date for the selected essay
    $sql_submission = "SELECT date, answer, score FROM response WHERE responseid = ?";
    $stmt_submission = $conn->prepare($sql_submission);
    $stmt_submission->bind_param("i", $responseID);
    $stmt_submission->execute();
    $result_submission = $stmt_submission->get_result();

    if ($row_submission = $result_submission->fetch_assoc()) {
        echo "<div class='submissiondate'>" . $row_submission['date'] . "</div>";
        echo "<div class='box'>";
        echo "<div class='answer-content'>" . $row_submission['answer'] . "</div>";
        echo "<div class='score-container'>";
        echo "<div class='score-value'>" .  $placeholderScore . "</div>";
        echo "<div class='dash'> / </div>"; // Inserting the dash div here

        echo "</div>"; // Close score-container
        echo "<div class='boxline'></div>";
        
        // Form HTML code is placed here
        echo "<form id='scoreForm' method='post' action='process_submission.php?responseid=" . $responseID . "'>";
        echo "<div class='score-input-container'>";
        echo "<input type='text' class='scorebox' name='score' placeholder='" . "' onkeypress='return isNumberKey(event)'>";
        echo "<input class='submitbutt' type='submit' value='Submit'>";
        echo "</div>"; // Close score-input-container
        echo "</form>";
        
        echo "</div>"; // Close the box div
        
        // JavaScript to populate the score in the scorebox
        echo "<script>document.querySelector('.scorebox').value = '" . $row_submission['score'] . "';</script>";
    } else {
        echo "<div class='no-submission'>No submission found for this response ID.</div>";
    }
    

    // Close statements
    $stmt_student_info->close();
    $stmt_question->close();
    $stmt_submission->close();
    ?>
</div>


    <button class="returnb" type="button" onclick="returnToPreviousPage()">Back</button>


    <div id="assessmentModal" class="modal1">
        <div class="modal-content1">
            <span class="close" onclick="closeAssessmentModal()">&times;</span>
            <p class="box1">Multiple Choice Type</p>
            <p class="box2">True/False Type</p>
            <p class="box3">Matching Type</p>
            <p class="box4">Short Answer Type</p>
            <p class="box5">Numerical Type</p>
            <p class="box6" onclick="openBox1Modal()">Essay Question Type</p>
            <p class="box7" onclick="openLessonModal()">Lesson</p>
        </div>
    </div>



    
    <div id="box1Modal" class="box1modal">
    <div>
        <span class="close" onclick="closeBox1Modal()">&times;</span>
        <!-- Update the form action to point to remedialessaysubmit.php -->
        <form id="essayForm" method="post" action="remedialessaysubmit.php?subjectID=<?php echo htmlspecialchars($_GET['subjectID']); ?>">
            <!-- Hidden input field to store the subjectID -->
            <input type="hidden" name="subjectID" value="105">
            
            <!-- Hidden input field to store the teacherID -->
            <input type="hidden" name="teacherID" value="<?php echo $teacherID; ?>">
            <!-- Hidden input field to store the studentID -->
            <input type="hidden" name="studentID" value="<?php echo $studentID; ?>">

            <!-- Other form inputs -->
            <input type="text" class="titletext" name="essayTitle" placeholder="Enter your text..">
            <p class="shape"></p>
            <textarea name="essayQuestion" class="container" rows="4" cols="50" placeholder="Enter your text..."></textarea>
            <p class="assessment">Assessment title</p>
            <p class="instruction">Content</p>
            <div class="shape">
                <input type="datetime-local" class="deadline" name="essayDeadline">
                <!-- Update the name attribute to 'essayScore' -->
                <input type="number" class="score" name="essayScore" placeholder="Score">
                <button type="button" class="add" onclick="processDataAndSubmit()">Add</button>
                <button type="button" onclick="openSecondModal()" class="selstudents">Select students</button>
            </div>
        </form>
    </div>
</div>


<div id="secondModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeSecondModal()">&times;</span>
        <h2>Students to You</h2>
        <form id="studentForm" method="post">
            <table>

            
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Score</th>
                        <th>Select</th>
                    </tr>
                </thead>
                <tbody id="studentList">
                    <?php
                    // Fetch students and their scores for the current activity
                    $sql_students_scores = "SELECT DISTINCT u.firstname, u.lastname, r.score FROM user u 
                                            INNER JOIN student s ON u.user_ID = s.userID 
                                            INNER JOIN response r ON s.studentID = r.studentID 
                                            WHERE s.teacherID = ? AND r.essayid = ?";
                    $stmt_students_scores = $conn->prepare($sql_students_scores);
                    $stmt_students_scores->bind_param("ii", $teacherID, $essayID);
                    $stmt_students_scores->execute();
                    $result_students_scores = $stmt_students_scores->get_result();

                    while ($row_students_scores = $result_students_scores->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row_students_scores['firstname']} {$row_students_scores['lastname']}</td>";
                        echo "<td>{$row_students_scores['score']}</td>";
                        echo "<td><input type='checkbox' name='selectedStudents[]' value='{$row_students_scores['firstname']} {$row_students_scores['lastname']}'></td>";
                        echo "</tr>";
                    }

                    // Close statement and database connection
                    $stmt_students_scores->close();
                    ?>
                </tbody>
            </table>

            <button type="submit" onclick="submitAndCloseSecondModal()">Submit</button>
        </form>
    </div>
</div>




<script>
function processDataAndSubmit() {
        // Process data from the first modal here
        // For simplicity, let's assume all form fields are valid

        // Trigger the submission of the first modal form
        document.getElementById("essayForm").submit();
    }

    function submitAndCloseSecondModal() {
    closeSecondModal(); // Close the second modal
}

    function openSecondModal() {
        var secondModal = document.getElementById('secondModal');
        secondModal.style.display = 'block';
    }

    function closeSecondModal() {
        var secondModal = document.getElementById('secondModal');
        secondModal.style.display = 'none';
    }

    // Function to handle form submission of the second modal
    document.getElementById("studentForm").addEventListener("submit", function(event) {
        event.preventDefault(); // Prevent the form from submitting
        // Get the selected student IDs and update the display in the first modal
        var selectedStudents = document.querySelectorAll('input[name="selectedStudents[]"]:checked');
        var selectedStudentIDs = [];
        selectedStudents.forEach(function(student) {
            selectedStudentIDs.push(student.value);
        });
        // Update the display in the first modal (you can customize this based on your layout)
        var studentListDisplay = document.getElementById('selectedStudentIDs');
        studentListDisplay.innerHTML = selectedStudentIDs.join(', '); // Display IDs separated by commas
        closeSecondModal(); // Close the second modal after submission
    });



        // Function to open box1 modal
function openBox1Modal() {
    var box1Modal = document.getElementById('box1Modal');
    box1Modal.style.display = 'block';
}

// Function to close box1 modal
function closeBox1Modal() {
    var box1Modal = document.getElementById('box1Modal');
    box1Modal.style.display = 'none';
}

    // Function to return to the previous page
    function returnToPreviousPage() {
        window.history.back();
    }




























    function reviewSubmission() {
    var scoreInput = document.querySelector('.scorebox');
    var scoreValue = parseInt(scoreInput.value);
    var placeholderScore = parseInt('<?php echo $placeholderScore; ?>');
    var percentage = (scoreValue / placeholderScore) * 100;

    if (percentage < 75) {
        displayAssessmentModal(); // Show assessment modal only if percentage is below 75%
    }
    
    // Submit the score via AJAX using update_score.php
    submitScoreAJAX(scoreValue);
    
    // Close the percentage modal regardless of the choice
    closeModal();
}





























// Function to submit the score via AJAX without refreshing the page
function submitScoreAJAX(scoreValue) {
    // Construct the data to be sent
    var formData = new FormData();
    formData.append('score', scoreValue);

    // Create an AJAX request
    var xhr = new XMLHttpRequest();

    // Define the request method, URL, and set it to asynchronous
    xhr.open('POST', 'update_score.php?responseid=<?php echo $responseID; ?>', true);

    // Define the onload callback function
    xhr.onload = function () {
        if (xhr.status === 200) {
            // Handle the response
            console.log('Score submitted successfully');
            // Optionally, you can display a message to the user indicating that the score has been updated
        } else {
            // Handle errors if any
            console.error('Error submitting score: ' + xhr.statusText);
        }
    };

    // Define the onerror callback function
    xhr.onerror = function () {
        // Handle errors
        console.error('Error submitting score');
    };

    // Send the request with the form data
    xhr.send(formData);
}



    // Function to display the assessment modal
    function displayAssessmentModal() {
        var assessmentModal = document.getElementById('assessmentModal');
        assessmentModal.style.display = 'block';
    }

    // Function to close the assessment modal
    function closeAssessmentModal() {
        var assessmentModal = document.getElementById('assessmentModal');
        assessmentModal.style.display = 'none';
    }

    // Function to submit the score
// Function to submit the score via AJAX
// Function to submit the score
// Function to submit the score
// Function to submit the score via AJAX
function submitScore() {
    // Get the score value
    var scoreInput = document.querySelector('.scorebox');
    var scoreValue = parseInt(scoreInput.value);

    // Construct the data to be sent
    var formData = new FormData();
    formData.append('score', scoreValue);

    // Create an AJAX request
    var xhr = new XMLHttpRequest();

    // Define the request method, URL, and set it to asynchronous
    xhr.open('POST', 'process_submission.php?responseid=<?php echo $responseID; ?>', true);

    // Define the onload callback function
    xhr.onload = function () {
        if (xhr.status === 200) {
            // Handle the response if needed
            console.log('Score submitted successfully');
            // Redirect to dashboard.php after submitting the score
            window.location.href = 'dashboard.php';
        } else {
            // Handle errors if any
            console.error('Error submitting score: ' + xhr.statusText);
        }
    };

    // Define the onerror callback function
    xhr.onerror = function () {
        // Handle errors
        console.error('Error submitting score');
    };

    // Send the request with the form data
    xhr.send(formData);
}


    // Function to close the modal
    function closeModal() {
        var modal = document.getElementById('myModal');
        modal.style.display = 'none';
    }

    // Function to display the modal
    function displayModal() {
        var modal = document.getElementById('myModal');
        modal.style.display = 'block';
    }

    // Function to validate the score input
    function validateScore() {
        var scoreInput = document.querySelector('.scorebox');
        var scoreValue = parseInt(scoreInput.value);
        var placeholderScore = parseInt('<?php echo $placeholderScore; ?>');

        if (isNaN(scoreValue) || scoreValue < 0 || scoreValue > placeholderScore) {
            alert('Please enter a valid score (between 0 and the maximum score of the essay)');
            return false; // Prevent form submission
        }

        // Calculate percentage
        var percentage = (scoreValue / placeholderScore) * 100;

        if (percentage < 75) {
            displayModal(); // Score below 75%, display modal
            return false; // Prevent form submission
        }

        return true; // Allow form submission
    }

    // Function to handle keypress event to allow only numbers
    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            evt.preventDefault();
            return false;
        }
        return true;
    }

    // Event listener to validate score before form submission
    document.getElementById('scoreForm').addEventListener('submit', function (event) {
        if (!validateScore()) {
            event.preventDefault(); // Prevent form submission if validation fails
        }
    });

    // Add more functions as needed for other modals or interactions
</script>

</body>

</html>