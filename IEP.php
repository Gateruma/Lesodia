<?php
// Start or resume the session
session_start();

// Include the database connection
include('config.php');

// Initialize variables
$studentID = "";
$studentName = "";
$studentAge = "";
$parentContact = "";
$iepData = [
    'strength' => '',
    'weakness' => '',
    'longgoals' => '',
    'comments' => '',
    'suggestion' => ''
];

// Get the studentID from the URL parameter
if (isset($_GET['studentID'])) {
    $studentID = $_GET['studentID'];

    // Fetch student details along with user table information
    $sql_student = "SELECT s.studentID, u.firstname, u.lastname, u.dateofbirth, u.gcontact 
                    FROM student s 
                    JOIN user u ON s.userID = u.user_ID 
                    WHERE s.studentID = ?";
    $stmt_student = $conn->prepare($sql_student);
    $stmt_student->bind_param("i", $studentID);
    $stmt_student->execute();
    $result_student = $stmt_student->get_result();

    if ($row_student = $result_student->fetch_assoc()) {
        $studentName = $row_student['firstname'] . ' ' . $row_student['lastname'];
        $studentAge = date_diff(date_create($row_student['dateofbirth']), date_create('today'))->y;
        $parentContact = $row_student['gcontact'];
    } else {
        echo "Student not found.";
    }

    $stmt_student->close();

    // Fetch IEP data for the student
    $sql_iep = "SELECT * FROM iep WHERE studentID = ?";
    $stmt_iep = $conn->prepare($sql_iep);
    $stmt_iep->bind_param("i", $studentID);
    $stmt_iep->execute();
    $result_iep = $stmt_iep->get_result();

    if ($row_iep = $result_iep->fetch_assoc()) {
        $iepData = $row_iep;
    }

    $stmt_iep->close();
}

// Handle form submission for saving IEP data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $strengths = $_POST['strengths'];
    $weaknesses = $_POST['weaknesses'];
    $longTermGoals = $_POST['longTermGoals'];
    $comments = $_POST['comments'];
    $parentSuggestions = $_POST['parentSuggestions'];

    // Check if an IEP record already exists
    $sql_check = "SELECT COUNT(*) AS count FROM iep WHERE studentID = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $studentID);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();

    if ($row_check['count'] > 0) {
        // Update existing IEP record
        $sql_update = "UPDATE iep SET 
                        strength = ?, 
                        weakness = ?, 
                        longgoals = ?, 
                        comments = ?, 
                        suggestion = ? 
                        WHERE studentID = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sssssi", $strengths, $weaknesses, $longTermGoals, $comments, $parentSuggestions, $studentID);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        // Insert new IEP record
        $sql_insert = "INSERT INTO iep (studentID, strength, weakness, longgoals, comments, suggestion) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("isssss", $studentID, $strengths, $weaknesses, $longTermGoals, $comments, $parentSuggestions);
        $stmt_insert->execute();
        $stmt_insert->close();
    }

    // Redirect to avoid form resubmission
    header("Location: IEP.php?studentID=" . $studentID);
    exit();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IEP for Online Tutoring</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }
        textarea {
            width: 100%;
            padding: 10px;
            margin: 5px 0 20px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            resize: vertical;
        }
        p {
            margin: 5px 0 20px 0;
        }
        h2, h3 {
            color: #333;
        }
        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

    <h2>IEP for Online Tutoring</h2>

    <!-- Student Information -->
    <h3>Student Information</h3>
    <p><strong>Student Name:</strong> <?php echo htmlspecialchars($studentName); ?></p>
    <p><strong>Age:</strong> <?php echo htmlspecialchars($studentAge); ?></p>
    <p><strong>Parent/Guardian Contact Info:</strong> <?php echo htmlspecialchars($parentContact); ?></p>

    <!-- Form for Editing IEP -->
    <form action="IEP.php?studentID=<?php echo htmlspecialchars($studentID); ?>" method="POST">
        <!-- Strengths -->
        <h3>Strengths</h3>
        <label for="strengths">Key Strengths:</label>
        <textarea id="strengths" name="strengths" rows="4"><?php echo htmlspecialchars($iepData['strength']); ?></textarea>

        <!-- Weaknesses -->
        <h3>Weaknesses</h3>
        <label for="weaknesses">Key Weaknesses:</label>
        <textarea id="weaknesses" name="weaknesses" rows="4"><?php echo htmlspecialchars($iepData['weakness']); ?></textarea>

        <!-- Long-term Goals -->
        <h3>Long-term Goals</h3>
        <label for="long-term-goals">Long-term Tutoring Goals:</label>
        <textarea id="long-term-goals" name="longTermGoals" rows="4"><?php echo htmlspecialchars($iepData['longgoals']); ?></textarea>

        <!-- Comments -->
        <h3>Comments</h3>
        <label for="comments">Additional Comments:</label>
        <textarea id="comments" name="comments" rows="4"><?php echo htmlspecialchars($iepData['comments']); ?></textarea>

        <!-- Parent/Guardian Suggestions -->
        <h3>Parent/Guardian Suggestions</h3>
        <label for="parent-suggestions">Suggestions from Parent/Guardian:</label>
        <textarea id="parent-suggestions" name="parentSuggestions" rows="4"><?php echo htmlspecialchars($iepData['suggestion']); ?></textarea>

        <!-- Save Button -->
        <button type="submit">Save</button>
    </form>

</body>
</html>
