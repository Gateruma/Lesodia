<?php
include('config.php');

// Assuming you already have session management in place
session_start();

// Ensure the user is logged in or has the right permissions

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paymentId = $_POST['paymentid'];

    // Automatically set the current date and time
    $date = date('Y-m-d H:i:s'); // Get the current date and time in 'YYYY-MM-DD HH:MM:SS' format

    // Update the payment status and set date in the database
    $sql_update = "UPDATE paymentreq SET status = 'Accepted', date = ? WHERE paymentid = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $date, $paymentId);
    
    if ($stmt_update->execute()) {
        echo "Payment status updated successfully.";
    } else {
        echo "Error updating payment status: " . $conn->error;
    }

    $stmt_update->close();
}
$conn->close();
?>
