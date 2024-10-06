<?php
include('config.php');

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_ID'])) {
    header("Location: login.php");
    exit();
}

// Retrieve studentID and subjectID from URL parameters
if (isset($_GET['studentID']) && isset($_GET['subjectID'])) {
    $studentID = $_GET['studentID'];
    $subjectID = $_GET['subjectID'];
} else {
    // Redirect or show error message
}

// Fetch essay, block, and guess submissions for the specified subject, along with scores and submission date
$sqlFetchSubmissions = "
    SELECT DISTINCT response.essayid AS submissionID, essay_type.title, response.score, response.date AS submissionDate, 'essay' AS type
    FROM response
    JOIN essay_type ON response.essayid = essay_type.essayid
    WHERE response.studentID = '$studentID' AND essay_type.subjectID = '$subjectID'
    
    UNION ALL
    
    SELECT DISTINCT response.blockid AS submissionID, blocks_type.title, response.score, response.date AS submissionDate, 'block' AS type
    FROM response
    JOIN blocks_type ON response.blockid = blocks_type.blockid
    WHERE response.studentID = '$studentID' AND blocks_type.subjectID = '$subjectID'
    
    UNION ALL
    
    SELECT DISTINCT response.guessid AS submissionID, guesstype.title, response.score, response.date AS submissionDate, 'guess' AS type
    FROM response
    JOIN guesstype ON response.guessid = guesstype.guessid
    WHERE response.studentID = '$studentID' AND guesstype.subjectid = '$subjectID'
";

$resultSubmissions = $conn->query($sqlFetchSubmissions);

// Initialize array to store submission data
$submissions = [];

// Check if there are submissions responded to by the student for the specified subject
if ($resultSubmissions->num_rows > 0) {
    // Iterate through each row of submission data
    while ($rowSubmission = $resultSubmissions->fetch_assoc()) {
        // Add submission data to the array
        $submissions[] = $rowSubmission;
    }
}

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="aftersubject.css">
    <title>Submissions by Student</title>
    <script>
        function showModal(responseData, score, submissionDate) {
            const modal = document.getElementById('responseModal');
            document.getElementById('modalContent').innerText = responseData;
            document.getElementById('modalScore').innerText = "Score: " + score;
            document.getElementById('modalDate').innerText = "Submission Date: " + submissionDate;
            modal.style.display = 'block';
        }

        function closeModal() {
            const modal = document.getElementById('responseModal');
            modal.style.display = 'none';
        }
    </script>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="Logo">
    </div>
    
    <h1 class="line" style="text-align: center; color: #333;">Submissions by Student</h1>
    
    <div class="container">
        <?php if (!empty($submissions)) : ?>
            <table class="essays-table">
                <thead>
                    <tr>
                        <th>Title</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission) : ?>
                        <tr>
                            <td>
                                <?php if ($submission['type'] === 'essay'): ?>
                                    <a href="parentcheckanswer.php?studentID=<?php echo $studentID; ?>&essayID=<?php echo $submission['submissionID']; ?>">
                                        <?php echo $submission['title']; ?>
                                    </a>
                                <?php elseif ($submission['type'] === 'block'): ?>
                                    <a href="javascript:void(0);" onclick="showModal('Response data for block ID: <?php echo $submission['submissionID']; ?>', '<?php echo $submission['score']; ?>', '<?php echo $submission['submissionDate']; ?>')">
                                        <?php echo $submission['title']; ?>
                                    </a>
                                <?php else: ?>
                                    <a href="javascript:void(0);" onclick="showModal('Response data for guess ID: <?php echo $submission['submissionID']; ?>', '<?php echo $submission['score']; ?>', '<?php echo $submission['submissionDate']; ?>')">
                                        <?php echo $submission['title']; ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p>Total Items: <?php echo count($submissions); ?></p>
        <?php else : ?>
            <p class="no-essays">No submissions responded to by the student for this subject.</p>
        <?php endif; ?>
    </div>

    <div class="dashboard-button">
        <button onclick="window.location.href='parentdashboard.php'" class="dashboard">Return</button>
    </div>

    <!-- Modal for displaying response -->
    <div id="responseModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5);">
        <div style="background:white; margin:10% auto; padding:20px; width:80%; max-width:500px; position:relative;">
            <span onclick="closeModal()" style="cursor:pointer; position:absolute; top:10px; right:10px;">&times;</span>
            <h2>Response Details</h2>
            <div id="modalContent"></div>
            <div id="modalScore" style="margin-top: 10px;"></div>
            <div id="modalDate" style="margin-top: 10px;"></div>
        </div>
    </div>
</body>
</html>
