<?php
// Include the database connection
include('config.php');

// Check if subjectID and studentID are sent via POST
if (isset($_POST['subjectID']) && isset($_POST['studentID'])) {
    $subjectID = $_POST['subjectID'];
    $studentID = $_POST['studentID'];

    // Fetch class dates for the selected subject
    $sql_class_dates = "SELECT classID, date FROM class WHERE subjectid = ?";
    $stmt_class_dates = $conn->prepare($sql_class_dates);
    $stmt_class_dates->bind_param("i", $subjectID);
    $stmt_class_dates->execute();
    $result_class_dates = $stmt_class_dates->get_result();

    // Check if there are rows returned
    if ($result_class_dates->num_rows > 0) {
        // Output data of each row
        while ($row_class_date = $result_class_dates->fetch_assoc()) {
            $classID = $row_class_date['classID'];
            $date = $row_class_date['date'];

            // Fetch attendance status for the given studentID in this class
            $sql_attendance = "SELECT status FROM attendance WHERE studentID = ? AND classID = ?";
            $stmt_attendance = $conn->prepare($sql_attendance);
            $stmt_attendance->bind_param("ii", $studentID, $classID);
            $stmt_attendance->execute();
            $result_attendance = $stmt_attendance->get_result();

            // Check if there are rows returned
            if ($row_attendance = $result_attendance->fetch_assoc()) {
                $status = $row_attendance['status'];
                // Display class details and attendance status
                echo "<tr>";
                echo "<td>$classID</td>";
                echo "<td>$date</td>";
                echo "<td>" . ($status == 'Present' ? 'X' : '') . "</td>"; // Mark X for Present
                echo "<td>" . ($status == 'Absent' ? 'X' : '') . "</td>"; // Mark X for Absent
                echo "</tr>";
            } else {
                // No attendance record found, mark as Absent
                echo "<tr>";
                echo "<td>$classID</td>";
                echo "<td>$date</td>";
                echo "<td></td>"; // Not marked present
                echo "<td>X</td>"; // Marked absent
                echo "</tr>";
            }

            // Close attendance statement
            $stmt_attendance->close();
        }
    } else {
        echo "<tr><td colspan='4'>No classes found for this subject</td></tr>";
    }

    // Close the class dates statement and database connection
    $stmt_class_dates->close();
    $conn->close();
} else {
    // Redirect or handle error if subjectID or studentID is not provided
    echo "Error: Subject ID or Student ID is missing.";
}
?>
