<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="viewsubmission.css">
    <style>
        /* Add your inline CSS styles here */
    </style>
    <title>Parent Check Answer</title>
</head>

<body>
<div class="header">
        <img src="logo.png" alt="Logo">
    </div>
    <!-- PHP code for fetching and displaying essay details -->
    <?php
    include('config.php');

    session_start();

    // Check if the user is logged in
    if (!isset($_SESSION['user_ID'])) {
        header("Location: login.php");
        exit();
    }


    // Retrieve essayID and studentID from URL parameters
    if (isset($_GET['essayID']) && isset($_GET['studentID'])) {
        $essayID = $_GET['essayID'];
        $studentID = $_GET['studentID'];
    } else {
        // Redirect or show error message
    }

    // Fetch essay details
    $sqlFetchEssay = "SELECT * FROM essay_type WHERE essayid = ?";
    $stmtFetchEssay = $conn->prepare($sqlFetchEssay);
    $stmtFetchEssay->bind_param("i", $essayID);
    $stmtFetchEssay->execute();
    $resultEssay = $stmtFetchEssay->get_result();

    // Fetch student's answer for the essay
    $sqlFetchResponse = "SELECT * FROM response WHERE essayid = ? AND studentID = ?";
    $stmtFetchResponse = $conn->prepare($sqlFetchResponse);
    $stmtFetchResponse->bind_param("ii", $essayID, $studentID);
    $stmtFetchResponse->execute();
    $resultResponse = $stmtFetchResponse->get_result();
    ?>

<button class="returnb" type="button" onclick="returnToPreviousPage()">Back</button>

    <!-- Display essay details -->
    <div class="submission-container">
        <?php if ($resultEssay->num_rows > 0) : ?>
            <?php $essay = $resultEssay->fetch_assoc(); ?>
            <div class="essay-details">
            <p class="linames" style="vertical-align: text-top; font-size: 42px;"><?php echo $essay['title']; ?></p>
                <p class="question-content" style="font-size: 22px;"><?php echo $essay['question']; ?></p>
            </div>
        <?php else : ?>
            <p class="no-data">No essay found.</p>
        <?php endif; ?>

        <!-- Display student's answer -->
        <?php if ($resultResponse->num_rows > 0) : ?>
            <ul class="student-answers">
                <?php while ($response = $resultResponse->fetch_assoc()) : ?>
                    <li>
                        <div class="box">
                            <div class="answer-content"><?php echo $response['answer']; ?></div>
                            <div class="score-value"><?php echo $response['score']; ?></div>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else : ?>
            <p class="no-data">No response found for this essay.</p>
        <?php endif; ?>
    </div>

    <!-- Close database connection and prepared statements -->
    <?php
    $stmtFetchEssay->close();
    $stmtFetchResponse->close();
    $conn->close();
    ?>

    <script> 
        function returnToPreviousPage() {
        window.history.back();
    }
    </script>
</body>

</html>
