<?php
include('config.php');

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the form is submitted with student IDs and teacherID
    if (isset($_POST['studentID']) && is_array($_POST['studentID']) && isset($_POST['teacherID'])) {
        $teacher_id = $_POST['teacherID']; // Retrieve teacher ID from the form
        $subject_id = $_GET['subjectID']; // Assuming subjectID is passed via GET parameter
        $essay_id = $_GET['id']; // Assuming essay ID is passed via GET parameter
        
        // Prepare the SQL statement to insert selected students into the remedial table
        $sql = "INSERT INTO remedial (teacherid, subjectid, studentid, essayid) VALUES (?, ?, ?, ?)";
        
        // Output debug information
        echo "SQL Query: $sql<br>";
        echo "Teacher ID: $teacher_id<br>";
        echo "Subject ID: $subject_id<br>";
        echo "Essay ID: $essay_id<br>";
        echo "Selected Students:<br>";
        foreach ($_POST['studentID'] as $student_id) {
            echo "- Student ID: $student_id<br>";
            
            // Execute the SQL statement
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiii", $teacher_id, $subject_id, $student_id, $essay_id);
            $stmt->execute();
            $stmt->close();
        }
        
        // Redirect back to the page after assigning students
        header("Location: {$_SERVER['HTTP_REFERER']}");
        exit();
    }
}

// If the form is not submitted properly, redirect back to the previous page
header("Location: {$_SERVER['HTTP_REFERER']}");
exit();
?>
