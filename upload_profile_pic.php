<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check if file was uploaded without errors
if (isset($_FILES["fileToUpload"]) && $_FILES["fileToUpload"]["error"] == 0) {
    $user_ID = $_SESSION['user_ID'];
    $target_dir = "C:/xampp/htdocs/KLASEMO/uploads/"; // Update the path to the uploads directory
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

    // Move the uploaded file to the target directory without compression
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        // Update user profile picture in the database
        $servername = "localhost";
        $username_db = "root";
        $password_db = "";
        $dbname = "klasemo";

        $conn = new mysqli($servername, $username_db, $password_db, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare and execute SQL query to update profpic with raw image data
        $sql = "UPDATE user SET profpic=? WHERE user_ID=?";
        $stmt = $conn->prepare($sql);
        
        // Read the raw image data
        $imageData = file_get_contents($target_file);
        
        // Bind the raw image data to the parameter and execute the query
        $stmt->bind_param("si", $imageData, $user_ID);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        echo "The file " . basename($_FILES["fileToUpload"]["name"]) . " has been uploaded.";

        // Redirect to profilemysubj.php
        header("Location: profilemysubj.php");
        exit();
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
} else {
    echo "No file uploaded.";
}
?>
