<?php
// Include the database connection
include('config.php');

session_start(); // Start the session

// Initialize variables for modal display
$showModal = false;

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from the form
    $essayID = $_POST['essayID'];
    $description = $_POST['description'];
    
    // Check if the user is logged in
    if (!isset($_SESSION['userID'])) {
        // Redirect the user to the login page if not logged in
        header("Location: login.html");
        exit();
    }

    // Retrieve the userID from the session
    $userID = $_SESSION['userID'];

    // Fetch the studentID from the student table based on the logged-in userID
    $sql_student = "SELECT studentID FROM student WHERE userID = ?";
    $stmt_student = $conn->prepare($sql_student);
    $stmt_student->bind_param("i", $userID);
    $stmt_student->execute();
    $stmt_student->store_result();
    
    // Check if the student exists
    if ($stmt_student->num_rows > 0) {
        $stmt_student->bind_result($studentID);
        $stmt_student->fetch();
        $stmt_student->close();

        $date = date("Y-m-d H:i:s"); // Current date and time

        // Prepare and execute the SQL INSERT query
        $sql = "INSERT INTO response (studentID, essayid, date, answer) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $studentID, $essayID, $date, $description);
        $stmt->execute();

        // Check if the query was successful
        if ($stmt->affected_rows > 0) {
            // Set $showModal to true to display the modal
            $showModal = true;
        } else {
            // If the query failed, display an error message
            echo "Error submitting essay. Please try again.";
        }

        // Close the statement
        $stmt->close();
    } else {
        // If the student does not exist, redirect to the login page
        header("Location: login.html");
        exit();
    }
}

// Fetch essay details from the database based on the title
$title = isset($_GET['title']) ? $_GET['title'] : null;
$essayID = isset($_GET['essayid']) ? $_GET['essayid'] : null;

$sql = "SELECT essayid, subjectID, title, question, deadline, score FROM essay_type WHERE title = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $title);
$stmt->execute();

// Check for errors
if ($stmt->error) {
    echo "Error executing SQL: " . $stmt->error;
}

$result = $stmt->get_result();

// Initialize $data array
$data = [];

if ($result->num_rows > 0) {
    // Fetch data
    $data = $result->fetch_assoc();
} else {
    echo "No data found for the given title: $title"; // Debugging statement
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="studentopenfile.css"> <!-- Include your CSS file here -->
    <title>KlaseMo - File Details</title>

    <!-- Bootstrap 5 CDN Link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Summernote CSS - CDN Link -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

    <!-- Custom CSS for modal -->
    <style>
        /* Center modal */
        .modal {
            display: flex !important;
            justify-content: center;
            align-items: center;
            
        }
        /* Adjust modal size */
        .modal-dialog {
            max-width: 70vw;
            max-height: 70vh;
        }
        /* Center modal content */
        .modal-content {
            text-align: center;
        }
    </style>

</head>

<body style="background-color: #edf5fd;">


<div class="header">
        <img src="logo.png" alt="Logo">
    </div> 
    <button class="return-button" type="button" onclick="returnToDashboard()">Return to Dashboard</button>

    <div class="line">

    <div class="title"><?php echo isset($data['title']) ? $data['title'] : ''; ?></div>
    <div class="deadline"><?php echo isset($data['deadline']) ? $data['deadline'] : ''; ?></div>

    </div>

    <div class="questioncont">
        <?php echo isset($data['question']) ? $data['question'] : ''; ?>
        <div class="question">
            <div class="card" style="top: 7%;">
                <form id="essayForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="essayID" value="<?php echo isset($data['essayid']) ? $data['essayid'] : ''; ?>">
                    <div>
                        <textarea name="description" id="your_summernote" rows="4" class="form-control" style="height: 200px;" placeholder="Put your answer here"></textarea>
                    </div>
                    <!-- Add a submit button -->
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <!-- Add a button for speech to text -->
                    <button type="button" id="startDictation" class="btn btn-secondary">Start Dictation</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for success message -->
<?php if ($showModal): ?>
    <div class="modal" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <img src="good-job-pink.gif" alt="Good Job" style="max-width: 100%; max-height: 100%;">
                    <p>Congratulations! Your essay has been successfully submitted.</p>
                    <button type="button" class="btn btn-primary" onclick="returnToDashboardFromModal()">Return to Dashboard</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

    <script>
        function returnToDashboard() {
            window.location.href = 'studentdashboard.php';
        }

        function returnToDashboardFromModal() {
            window.location.href = 'studentdashboard.php';
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Summernote JS - CDN Link -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#your_summernote").summernote();
            $('.dropdown-toggle').dropdown();
        });
    </script>
    <!-- //Summernote JS - CDN Link -->

    <!-- Web Speech API for Speech-to-Text -->
    <script>
        document.getElementById('startDictation').addEventListener('click', function() {
            var recognition = new webkitSpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = false;

            recognition.onstart = function() {
                console.log('Speech recognition started');
            };

            recognition.onresult = function(event) {
                var transcript = event.results[0][0].transcript;
                $('#your_summernote').summernote('code', transcript);
                console.log('Speech recognition result:', transcript);
            };

            recognition.onerror = function(event) {
                console.error('Speech recognition error', event.error);
            };

            recognition.onend = function() {
                console.log('Speech recognition ended');
            };

            recognition.start();
        });
    </script>

</body>

</html>
