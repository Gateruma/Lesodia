<?php
// Include the file containing the database connection code
include("config.php"); // Adjust the path as needed

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Start the session
    session_start();

    // Get the parent ID from the URL if available
    $parentId = isset($_GET['parentid']) ? $_GET['parentid'] : null;

    // Establish database connection

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Collect form data
    $studentName = $_POST['student_name'];

    // Handle file upload
    $targetDirectory = "uploads/"; // Directory where uploaded files will be stored
    $targetFile = $targetDirectory . basename($_FILES["file"]["name"]); // Path of the uploaded file
    $uploadOk = 1; // Flag to check if file upload is successful

    // Check if file already exists
    if (file_exists($targetFile)) {
        echo '<script>alert("Sorry, file already exists.");</script>';
        $uploadOk = 0;
    }

    // Check file size (example limit of 2MB)
    if ($_FILES["file"]["size"] > 2097152) {
        echo '<script>alert("Sorry, your file is too large.");</script>';
        $uploadOk = 0;
    }

    // Allow only specific file formats
    $allowedFileTypes = ['application/pdf', 'image/jpeg', 'image/png']; // Adjust file types as needed
    if (!in_array($_FILES["file"]["type"], $allowedFileTypes)) {
        echo '<script>alert("Sorry, only PDF, JPEG, and PNG files are allowed.");</script>';
        $uploadOk = 0;
    }

    // Upload file
    if ($uploadOk) {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
            // File uploaded successfully, proceed to insert data into request table
            $status = 'pending';

            // Prepare SQL statement
            $sql = "INSERT INTO request (parentid, student, file, status) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $parentId, $studentName, $targetFile, $status);

            // Execute the statement
            if ($stmt->execute()) {
                echo '<script>alert("Request submitted successfully."); window.location.href = "parentdashboard.php";</script>';
                // Redirect to parentdashboard.php after showing the alert
            } else {
                echo '<script>alert("Error: ' . $stmt->error . '");</script>';
            }

            // Close statement
            $stmt->close();
        } else {
            echo '<script>alert("Sorry, there was an error uploading your file.");</script>';
        }
    }

    // Close database connection
    $conn->close();
}
?>

<?php
include("config.php"); // Adjust the path as needed

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_ID'])) {
    header("Location: login.php");
    exit();
}

// Retrieve logged-in user's ID
$loggedInUserId = $_SESSION['user_ID'];

// Check if the user has a corresponding record in the parent table
$sqlCheckParent = "SELECT * FROM parent WHERE userID = '$loggedInUserId'";
$resultCheckParent = $conn->query($sqlCheckParent);

if ($resultCheckParent->num_rows > 0) {
    // Parent record found, retrieve student information
    $rowParent = $resultCheckParent->fetch_assoc();
    $studentId = $rowParent['studentid'];

    // Check if the parent has a student assigned
    if (!empty($studentId)) {
        // Fetch student's information from the student table
        $sqlFetchStudentInfo = "SELECT * FROM student WHERE studentID = '$studentId'";
        $resultFetchStudentInfo = $conn->query($sqlFetchStudentInfo);

        if ($resultFetchStudentInfo->num_rows > 0) {
            // Student information found, display student's data
            $studentData = $resultFetchStudentInfo->fetch_assoc();
        }
    }
}

// Fetch subjects assigned to the student's teacher
if (isset($studentData)) {
    $teacherId = $studentData['teacherID'];
    $sqlFetchSubjects = "SELECT * FROM subject WHERE userID = '$teacherId'";
    $subjects = $conn->query($sqlFetchSubjects);
}

// Fetch parentid of the logged-in user
$sqlFetchParentId = "SELECT parentid FROM parent WHERE userID = '$loggedInUserId'";
$resultFetchParentId = $conn->query($sqlFetchParentId);
if ($resultFetchParentId->num_rows > 0) {
    $rowParentId = $resultFetchParentId->fetch_assoc();
    $parentId = $rowParentId['parentid'];
} else {
    $parentId = "Parent ID not found"; // If no parentid found for the user
}

// Check if the current parent ID has a pending request in the request table
$sqlCheckPendingRequest = "SELECT * FROM request WHERE parentid = '$parentId' AND status = 'pending'";
$resultCheckPendingRequest = $conn->query($sqlCheckPendingRequest);
$isPendingRequest = $resultCheckPendingRequest->num_rows > 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="parentdashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lilita+One&display=swap" rel="stylesheet">
    <title>Dashboard</title>
    <style>
        /* Center the form */
        .parent-request {
    display: <?php echo isset($studentData) ? 'none' : 'block'; ?>; /* Hide parent account request form if student data exists */
    position: absolute;
    top: 50%;
    left: 50%;
    width: 30%;
    height: 40%;
    transform: translate(-50%, -50%);
    text-align: center;
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
}
    </style>
</head>

<body>
<div class="header">
        <img src="logo.png" alt="Logo">
    </div>

<?php if ($isPendingRequest) : ?>
    <!-- Display a modal indicating pending approval -->
    <div class="modal-content" style="background-color: #fefefe; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); padding: 20px; border: 1px solid #888; width: 80%; border-radius: 10px; box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1); text-align: center;">
    <h2>Pending Approval</h2>
    <p>Your request is pending approval. Please wait for admin's response.</p>
</div>

    </div>
<?php else : ?>
    <?php if (isset($studentData)) : ?>
        <!-- Display student's data -->
        <div class="student-data">
            <h2>Student's Data</h2>
            <p><strong>Student ID:</strong> <?php echo $studentData['studentID']; ?></p>
            <!-- Assign studentID to JavaScript variable -->
            <script>
                var studentID = <?php echo $studentData['studentID']; ?>;
            </script>
            <!-- Display more student data here -->
        </div>
    <?php else : ?>
        <!-- Parent Account Request Form -->
        <div class="parent-request" style="background-color: #f9f9f9; padding: 20px; border-radius: 10px; box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);">
            <h2 style="font-size: 24px; color: #333; margin-bottom: 20px;">Parent Account Request</h2>
            <form action="parentdashboard.php?parentid=<?php echo $parentId; ?>" method="post" enctype="multipart/form-data">
                <label for="student_name" style="font-size: 16px; color: #333;"></label><br>
                <div id="selectedSuggestion" style="font-size: 16px; margin-bottom: 10px;"></div>
                <input type="text" id="student_name" name="student_name" onkeyup="getSearchSuggestions(this.value)" style="width: 80%; height: 40px; border: 2px solid #ccc; border-radius: 5px; padding: 8px;" required><br>
                <div id="searchSuggestions" style="margin-top: 5px;"></div> <!-- Display search suggestions here -->
                <label for="file" style="font-size: 16px; color: #333; margin-top: 20px;">Upload File:</label><br>
                <input type="file" id="file" name="file" style="width: 80%; border: 2px solid #ccc; border-radius: 5px; padding: 8px; margin-top: 5px;" required><br><br>
                <input type="submit" value="Submit Request" style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
            </form>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (isset($subjects) && $subjects->num_rows > 0) : ?>
    <?php $i = 0; ?>
    <?php while ($row = $subjects->fetch_assoc()) : ?>
        <div class="box<?php echo $i + 1; ?>" onclick="redirectToSubject('<?php echo $row['subjectID']; ?>')">
            <p><?php echo $row['subjectName']; ?></p>
            <p style="font-size: 16px;">Subject ID: <?php echo $row['subjectID']; ?></p>
            <p style="font-size: 25px;"><?php echo "Class day: " . $row['classDay']; ?></p>
            <p style="font-size: 25px;"><?php echo "Class time: " . $row['startTime'] . ' - ' . $row['endTime']; ?></p>
        </div>
        <?php $i++; ?>
    <?php endwhile; ?>
<?php endif; ?>


<!-- Dashboard Navigation Buttons -->
<button class="dashboard" onclick="redirectToDashboard()">Dashboard</button>
<button class="classrecord" onclick="redirectToClassrecord()">Class Record</button>

<button class="tutor" onclick="redirectToMytutor()">Check tutor</button>

<button class="myprofile" onclick="redirectToMyprofile()">My Profile</button>

<script>
    
    function redirectToDashboard() {
        window.location.href = 'parentdashboard.php';
    }

    function redirectToClassrecord() {
        window.location.href = 'classrecord.php';
    }

    function redirectToMyprofile() {
        window.location.href = 'parentprofile.php';
    }
    function redirectToMytutor() {
    const parentId = <?php echo json_encode($parentId); ?>; // Get parent ID from PHP
    window.location.href = `looktutor.php?parentid=${parentId}`; // Redirect to mytutor.php with parentid
}


    function redirectToSubject(subjectID) {
        // Redirect to parentaftersubject.php with both subjectID and studentID parameters
        window.location.href = 'parentaftersubject.php?subjectID=' + subjectID + '&studentID=' + studentID;
    }

    function selectSuggestion(suggestion) {
        // Extract the student ID and name from the suggestion
        var parts = suggestion.split('-');
        var studentName = parts[1];
        // Set input field value to selected student name
        document.getElementById("student_name").value = studentName.trim();
        // Clear search suggestions
        document.getElementById("searchSuggestions").innerHTML = "";
    }

    function getSearchSuggestions(input) {
        if (input.length == 0) {
            document.getElementById("searchSuggestions").innerHTML = "";
            return;
        } else {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("searchSuggestions").innerHTML = this.responseText;
                }
            };
            xhttp.open("GET", "get_search_suggestions.php?input=" + input, true);
            xhttp.send();
        }
    }
</script>

</body>

</html>
