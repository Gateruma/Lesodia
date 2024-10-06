<?php
session_start();
include("config.php");

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $teacherID = $_POST['teacherID'];
    $essayTitle = $_POST['essayTitle'];
    $essayQuestion = $_POST['essayQuestion'];
    $essayDeadline = $_POST['essayDeadline'];
    // Check if 'essayScore' is provided and not empty
    $essayScore = isset($_POST['essayScore']) ? $_POST['essayScore'] : null;
    if (empty($essayScore)) {
        // Handle the case where 'essayScore' is empty
        echo "Error: Essay score is required.";
        exit();
    }
    $studentID = $_POST['studentID']; // Retrieve studentID

    // Check if $conn is defined (assuming it's your database connection)
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Set the type to "remedial"
    $type = "remedial";

    // Retrieve subjectID from the URL
    if (isset($_GET['subjectID'])) {
        $subjectID = $_GET['subjectID'];
    } else {
        echo "Error: Subject ID not provided in the URL.";
        exit();
    }

    // Insert into essay_type table
    $sql_essay = "INSERT INTO essay_type (subjectID, teacherid, title, question, deadline, score, type) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_essay = $conn->prepare($sql_essay);
    if ($stmt_essay) {
        $stmt_essay->bind_param("iisssds", $subjectID, $teacherID, $essayTitle, $essayQuestion, $essayDeadline, $essayScore, $type);
        if ($stmt_essay->execute()) {
            $last_essay_id = $stmt_essay->insert_id; // Get the last inserted essay ID

            // Insert into remedial table for the specific student
            $sql_remedial = "INSERT INTO remedial (teacherid, subjectid, studentid, essayid) VALUES (?, ?, ?, ?)";
            $stmt_remedial = $conn->prepare($sql_remedial);
            if ($stmt_remedial) {
                // Bind parameters and execute the statement
                $stmt_remedial->bind_param("iiii", $teacherID, $subjectID, $studentID, $last_essay_id);
                if ($stmt_remedial->execute()) {
                    // Redirect to the student dashboard
                    header("Location: dashboard.php");
                    exit(); // Make sure to exit after redirection
                } else {
                    echo "Error inserting into remedial table for Student ID: $studentID - " . $stmt_remedial->error . "<br>";
                }
                
                $stmt_remedial->close();
            } else {
                echo "Error preparing statement for remedial table: " . $conn->error;
            }
        } else {
            echo "Error inserting into essay_type: " . $stmt_essay->error;
        }
        $stmt_essay->close();
    } else {
        echo "Error preparing statement for essay_type: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
} else {
    echo "Form not submitted.";
}
?>
