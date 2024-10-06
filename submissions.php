<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="submissions.css"> <!-- Include your CSS file here -->
    <title>KlaseMo - File Details</title>
    <style>
        /* CSS for centering the table */
        .center-table {
            margin: 0 auto; /* This centers the div horizontally */
            width: 80%; /* Adjust the width of the table */
            top: 200px;
        }

        .green-cell {
            background-color: lightgreen;
        }

        .red-cell {
            background-color: lightcoral;
        }

        .gray-cell {
            background-color: lightgray;
        }
    </style>
</head>

<body>
<div class="header">
        <img src="logo.png" alt="Logo">
    </div>
    <div>
        <?php
        // Include the database connection
        include('config.php');

        // Retrieve the essay ID and score from the URL parameters
        $essayID = isset($_GET['id']) ? $_GET['id'] : null;
        $targetScore = isset($_GET['score']) ? $_GET['score'] : null;
        $type = isset($_GET['type']) ? $_GET['type'] : null;

        // Display the essay ID
        echo "<p>Current Essay ID: " . ($essayID ?? 'Not specified') . "</p>";

        // Fetch students assigned to the current teacher who have submissions for the activity
        $teacherID = $_SESSION['user_ID']; // Assuming the session variable holds the teacher's user ID
        $sql_students_assigned = "SELECT DISTINCT u.firstname, u.lastname, s.studentID, r.score
                                   FROM user u
                                   JOIN student s ON u.user_ID = s.userID
                                   JOIN response r ON s.studentID = r.studentID
                                   WHERE r.essayid = ? AND u.user_ID = ?";
        $stmt_students_assigned = $conn->prepare($sql_students_assigned);
        $stmt_students_assigned->bind_param("ii", $essayID, $teacherID);
        $stmt_students_assigned->execute();
        $result_students_assigned = $stmt_students_assigned->get_result();
        ?>

        <div>
            <p>Students assigned to you:</p>
            <ul>
                <?php
                // Display the list of students assigned to the teacher
                while ($row_assigned = $result_students_assigned->fetch_assoc()) {
                    echo "<li>" . $row_assigned['firstname'] . " " . $row_assigned['lastname'] . "</li>";
                }
                ?>
            </ul>
        </div>
    </div>

    <button class="return-button" type="button" onclick="returnToDashboard()">Return to Dashboard</button>

    <div class="submissions">
        <p>Submissions</p>
    </div>

    <div class="center-table">
        <table>
            <!-- Table header -->
            <tr>
                <th>Student Name</th>
                <th>Date Submitted</th> <!-- New column for date -->
                <?php if ($type === 'guesstype') echo "<th>Score</th>"; ?> <!-- New column for score if type is guesstype -->
            </tr>
            <?php
            // Fetch student names and response details related to the activity ID and type
            $sql_students = null;
            if ($type === 'essay') {
                $sql_students = "SELECT DISTINCT u.firstname, u.lastname, r.studentID, r.responseid, r.date, r.score
                                 FROM user u
                                 JOIN student s ON u.user_ID = s.userID
                                 JOIN response r ON s.studentID = r.studentID
                                 WHERE r.essayid = ?";
            } elseif ($type === 'block') {
                $sql_students = "SELECT DISTINCT u.firstname, u.lastname, r.studentID, r.responseid, r.date, r.score
                                 FROM user u
                                 JOIN student s ON u.user_ID = s.userID
                                 JOIN response r ON s.studentID = r.studentID
                                 WHERE r.blockid = ?";
            } elseif ($type === 'guesstype') {
                $sql_students = "SELECT DISTINCT u.firstname, u.lastname, r.studentID, r.responseid, r.date, r.score
                                 FROM user u
                                 JOIN student s ON u.user_ID = s.userID
                                 JOIN response r ON s.studentID = r.studentID
                                 WHERE r.guessid = ?";
            }

            if ($sql_students) {
                $stmt_students = $conn->prepare($sql_students);
                $stmt_students->bind_param("i", $essayID);
                $stmt_students->execute();
                $result_students = $stmt_students->get_result();

                // Display student names with links and submission dates
                while ($row_students = $result_students->fetch_assoc()) {
                    // Check if the student's score is below 75% of the target score
                    $score = $row_students['score'];
                    $rowClass = ''; // Default row class
                    if ($score !== null && $score < ($targetScore * 0.75)) {
                        $rowClass = 'red-cell'; // If score below 75%, set row to red
                    } elseif ($score !== null) {
                        $rowClass = 'green-cell'; // If score exists, set row to green
                    } else {
                        $rowClass = 'gray-cell'; // If no score, set row to gray
                    }

                    echo "<tr class='$rowClass'>";
                    if ($type === 'essay') {
                        echo "<td><a href='viewsubmission.php?essayid=$essayID&studentid={$row_students['studentID']}&responseid={$row_students['responseid']}&subjectID=" . urlencode($_GET['subjectID']) . "'>" . $row_students['firstname'] . " " . $row_students['lastname'] . "</a></td>";
                    } elseif ($type === 'block') {
                        echo "<td><a href='viewsubmissionblock.php?id=$essayID&type=block&studentid={$row_students['studentID']}&responseid={$row_students['responseid']}&score=$targetScore&subjectID=" . urlencode($_GET['subjectID']) . "'>" . $row_students['firstname'] . " " . $row_students['lastname'] . "</a></td>";
                        } elseif ($type === 'guesstype') {
                        // Display student name and score
                        echo "<td>{$row_students['firstname']} {$row_students['lastname']}</td>";
                        echo "<td>{$score}</td>";
                        }
                        echo "<td>" . $row_students['date'] . "</td>"; // Display submission date
                        echo "</tr>";
                        }
     // Close statement
                                    $stmt_students->close();
                                }
                                ?>
                            </table>
                        </div>
                        
                        <script>
                            function returnToDashboard() {
                                window.location.href = 'dashboard.php';
                            }
                        </script>
                        </body>
                        </html>

                        
                        
                        
                        
                        
