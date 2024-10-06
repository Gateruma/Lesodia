<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "klasemo";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the POST data
$receipt = $_FILES['receipt']['name']; // Get the original file name
$target_dir = "receipt/"; // Directory to save the uploaded receipt
$target_file = $target_dir . basename($receipt); // Full path for the uploaded file
$amount = $_POST['amount'];
$message = $_POST['message'];
$status = $_POST['status'];
$tutorid = $_POST['tutorid'];
$parentID = $_POST['parentid'];
$studentID = $_POST['studentID'];
$startTime = $_POST['start_time']; // Get start time for schedule
$endTime = $_POST['end_time']; // Get end time for schedule
$session = $_POST['session']; // Get number of sessions

// Check if the parent ID exists
$checkParentSQL = "SELECT * FROM parent WHERE parentid = ?";
$stmt = $conn->prepare($checkParentSQL);
$stmt->bind_param("s", $parentID);
$stmt->execute();
$parentResult = $stmt->get_result();

if ($parentResult->num_rows > 0) {
    // Parent exists, proceed with the file upload
    if (move_uploaded_file($_FILES['receipt']['tmp_name'], $target_file)) {
        // Check if the tutor is available at the given time
        $checkScheduleSQL = "SELECT * FROM schedule WHERE tutorID = ? AND (
            (start_time < ? AND end_time > ?) OR 
            (start_time < ? AND end_time > ?) OR 
            (start_time >= ? AND end_time <= ?)
        )";
        $scheduleStmt = $conn->prepare($checkScheduleSQL);
        $scheduleStmt->bind_param("sssssss", $tutorid, $endTime, $startTime, $startTime, $startTime, $startTime, $endTime);
        $scheduleStmt->execute();
        $scheduleResult = $scheduleStmt->get_result();

        if ($scheduleResult->num_rows > 0) {
            // Tutor is not available
            echo "<script>alert('Error: The tutor is not available at the specified time.'); window.history.back();</script>";
        } else {
            // Tutor is available, proceed with inserting the schedule first
            $insertScheduleSQL = "INSERT INTO schedule (start_time, end_time, tutorID, studentID) VALUES (?, ?, ?, ?)";
            $scheduleInsertStmt = $conn->prepare($insertScheduleSQL);
            $scheduleInsertStmt->bind_param("ssii", $startTime, $endTime, $tutorid, $studentID);
            
            if ($scheduleInsertStmt->execute()) {
                // Get the last inserted schedule ID
                $scheduleID = $scheduleInsertStmt->insert_id;

                // Now insert the payment request with the retrieved schedule ID
                $insertSQL = "INSERT INTO paymentreq (receipt, amount, message, status, tutorid, parentid, studentID, scheduleid, session) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertSQL);
                $insertStmt->bind_param("ssssssssi", $receipt, $amount, $message, $status, $tutorid, $parentID, $studentID, $scheduleID, $session);
                
                if ($insertStmt->execute()) {
                    echo "<script>alert('Payment request and schedule submitted successfully.'); window.location.href='parentdashboard.php';</script>";
                } else {
                    echo "Error submitting payment request: " . $insertStmt->error . " (Error Code: " . $insertStmt->errno . ")";
                }
                $insertStmt->close();
            } else {
                echo "Error submitting schedule: " . $scheduleInsertStmt->error . " (Error Code: " . $scheduleInsertStmt->errno . ")";
            }
            $scheduleInsertStmt->close();
        }
        $scheduleStmt->close();
    } else {
        echo "Error uploading file.";
    }
} else {
    echo "Error: The provided Parent ID '$parentID' does not exist in the database.";
}

// Close connections
$stmt->close();
$conn->close();
?>
