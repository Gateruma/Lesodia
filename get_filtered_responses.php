<?php
// Start or resume the session
session_start();

// Include the database connection
include('config.php');

// Get the subjectID and studentID from the POST request
$subjectID = isset($_POST['subjectID']) ? intval($_POST['subjectID']) : 0;
$studentID = isset($_POST['studentID']) ? intval($_POST['studentID']) : 0;

if ($subjectID && $studentID) {
    // Fetch responses assigned to the studentID and subjectID
    $responses = [];
    $sql_responses = "SELECT response.* 
                      FROM response 
                      JOIN essay ON response.essayid = essay.essayid 
                      WHERE response.studentID = ? AND essay.subjectID = ?";
    $stmt_responses = $conn->prepare($sql_responses);
    $stmt_responses->bind_param("ii", $studentID, $subjectID);
    $stmt_responses->execute();
    $result_responses = $stmt_responses->get_result();

    while ($row_response = $result_responses->fetch_assoc()) {
        $responses[] = $row_response;
    }

    $stmt_responses->close();

    // Generate the HTML for the response table rows
    if (!empty($responses)) {
        foreach ($responses as $response) {
            echo "<tr>
                    <td>{$response['responseid']}</td>
                    <td>{$response['studentID']}</td>
                    <td>{$response['essayid']}</td>
                    <td>{$response['blockid']}</td>
                    <td>{$response['questionid']}</td>
                    <td>{$response['date']}</td>
                    <td>{$response['answer']}</td>
                    <td>{$response['file']}</td>
                    <td>{$response['score']}</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='9'>No responses found for the selected subject.</td></tr>";
    }
} else {
    echo "<tr><td colspan='9'>Invalid subject or student ID.</td></tr>";
}

$conn->close();
?>
