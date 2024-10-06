<?php
include('config.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's username
$username = $_SESSION['username'];

// Fetch teacher ID of the currently logged-in user
$sql_teacher_id = "SELECT teacher_id FROM teacher WHERE user_ID = (SELECT user_ID FROM user WHERE username = ?)";
$stmt = $conn->prepare($sql_teacher_id);
$stmt->bind_param("s", $username);
$stmt->execute();
$result_teacher_id = $stmt->get_result();

if ($result_teacher_id->num_rows > 0) {
    $row_teacher_id = $result_teacher_id->fetch_assoc();
    $teacherID = $row_teacher_id['teacher_id'];

    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Check if all required fields are set
        if (isset($_POST['blocksTitle'], $_FILES['blocksImage']['name'], $_POST['blocksInstruction'], $_POST['blocksScore'], $_POST['blocksDeadline'], $_GET['subjectID'])) {
            $blocksTitle = $_POST['blocksTitle'];
            $blocksInstruction = $_POST['blocksInstruction'];
            $blocksScore = $_POST['blocksScore'];
            $blocksDeadline = $_POST['blocksDeadline'];
            $subjectID = $_GET['subjectID'];

            // Check if the file is an image
            $target_dir = "blocks/questions/";
            $target_file = $target_dir . basename($_FILES["blocksImage"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            $allowedTypes = array('jpg', 'jpeg', 'png');

            if (in_array($imageFileType, $allowedTypes)) {
                // Upload image to server
                if (move_uploaded_file($_FILES["blocksImage"]["tmp_name"], $target_file)) {
                    // File uploaded successfully, now insert data into the database
                    $imageContent = file_get_contents($target_file);

                    $sql = "INSERT INTO blocks_type (title, instruction, subjectID, teacherid, image, score, deadline) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $null = null;
                    $stmt->bind_param("sssibis", $blocksTitle, $blocksInstruction, $subjectID, $teacherID, $null, $blocksScore, $blocksDeadline);
                    $stmt->send_long_data(4, $imageContent); // Bind image as binary data

                    if ($stmt->execute()) {
                        // Display success alert
                        echo "<script>alert('New record created successfully');</script>";
                    } else {
                        // Display error alert
                        echo "<script>alert('Error: " . $stmt->error . "');</script>";
                    }
                } else {
                    // Display error alert
                    echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
                }
            } else {
                // Display error alert
                echo "<script>alert('Sorry, only JPG, JPEG, and PNG files are allowed.');</script>";
            }
        } else {
            // Display error alert
            echo "<script>alert('All fields are required');</script>";
        }
    } else {
        // Display error alert
        echo "<script>alert('Form not submitted');</script>";
    }
} else {
    // Display error alert
    echo "<script>alert('Teacher not found.');</script>";
}

// Redirect to dashboard
echo "<script>window.location.href = 'dashboard.php';</script>";

$stmt->close();
$conn->close();
?>
