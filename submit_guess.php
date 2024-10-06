<?php
// Include the config file
include('config.php');

// Function to output JavaScript alert and redirect
function jsAlertRedirect($message, $redirectUrl) {
    echo "<script type='text/javascript'>
        alert('$message');
        window.location.href = '$redirectUrl';
    </script>";
}

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the POST data
    $studentID = $_POST['studentID'];
    $guessID = isset($_POST['guessid']) ? $_POST['guessid'] : null;
    $date = $_POST['date'];
    $score = $_POST['score'];

    // Check connection
    if ($conn->connect_error) {
        die("<script type='text/javascript'>alert('Connection failed: " . addslashes($conn->connect_error) . "');</script>");
    }

    // Validate if guessid exists in the guesstype table
    $sql_check_guessid = "SELECT guessid FROM guesstype WHERE guessid = ?";
    $stmt_check_guessid = $conn->prepare($sql_check_guessid);
    $stmt_check_guessid->bind_param("i", $guessID);
    $stmt_check_guessid->execute();
    $result_check_guessid = $stmt_check_guessid->get_result();

    if ($result_check_guessid->num_rows > 0) {
        // Prepare SQL statement to insert data into the response table
        $sql = "INSERT INTO response (studentID, guessid, date, score) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bind_param("iiss", $studentID, $guessID, $date, $score);

        // Execute the statement
        if ($stmt->execute()) {
            // Data inserted successfully
            jsAlertRedirect("Response submitted successfully.", "studentdashboard.php");
        } else {
            // Error in insertion
            jsAlertRedirect("Error submitting response: " . addslashes($stmt->error), "studentdashboard.php");
        }

        // Close statement
        $stmt->close();
    } else {
        // guessid does not exist in guesstype table
        jsAlertRedirect("Invalid guessid. The specified guessid does not exist.", "studentdashboard.php");
    }

    // Close check statement and connection
    $stmt_check_guessid->close();
    $conn->close();
} else {
    // Invalid request method
    jsAlertRedirect("Invalid request method.", "studentdashboard.php");
}
?>
