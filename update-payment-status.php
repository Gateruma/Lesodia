<?php
include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paymentId = $_POST['paymentid'];
    $newStatus = $_POST['status'];

    // Update the payment request status
    $sql_update_payment = "UPDATE paymentreq SET status = ? WHERE paymentid = ?";
    $stmt_update_payment = $conn->prepare($sql_update_payment);
    $stmt_update_payment->bind_param("si", $newStatus, $paymentId);
    
    if ($stmt_update_payment->execute()) {
        // If the status is 'Accepted', update the tildate
        if ($newStatus === 'Accepted') {
            // Set tildate to the current date/time
            $date = date("Y-m-d H:i:s");
            $sql_update_date = "UPDATE paymentreq SET date = ? WHERE paymentid = ?";
            $stmt_update_date = $conn->prepare($sql_update_date);
            $stmt_update_date->bind_param("si", $date, $paymentId);
            $stmt_update_date->execute();
            $stmt_update_date->close();
        }

        // Get the student ID and tutor ID from the payment request
        $sql_payment_info = "SELECT studentID, tutorid FROM paymentreq WHERE paymentid = ?";
        $stmt_payment_info = $conn->prepare($sql_payment_info);
        $stmt_payment_info->bind_param("i", $paymentId);
        $stmt_payment_info->execute();
        $result_payment_info = $stmt_payment_info->get_result();

        if ($result_payment_info->num_rows > 0) {
            $payment_info = $result_payment_info->fetch_assoc();
            $studentID = $payment_info['studentID'];
            $tutorID = $payment_info['tutorid'];

            // Update the student table
            $sql_update_student = "UPDATE student SET tutorid = ? WHERE studentID = ?";
            $stmt_update_student = $conn->prepare($sql_update_student);
            $stmt_update_student->bind_param("ii", $tutorID, $studentID);

            if ($stmt_update_student->execute()) {
                $response = [
                    'status' => 'success',
                    'message' => 'Payment status updated and student record modified.',
                    'studentID' => $studentID,
                    'tutorID' => $tutorID
                ];
            } else {
                $response = ['status' => 'error', 'message' => 'Failed to update student record.'];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Payment information not found.'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Failed to update payment status.'];
    }

    // Return response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>
