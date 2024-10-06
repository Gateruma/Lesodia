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
    $conn = new mysqli($host, $username, $password, $database);

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

    if (file_exists($targetFile)) {
        echo '<script>alert("Sorry, file already exists.");</script>';
        $uploadOk = 0;
    }

    // Check file size (if needed)
    // Check file type (if needed)

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
