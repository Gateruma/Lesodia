<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Lesson</title>
    <link rel="stylesheet" href="tutorsubj.css"> <!-- Your CSS file -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <style>
        /* Custom body background color */
        body {
            background-color: #edf5fd; /* Set the body color */
        }
        .lesson-content {
            margin: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="Logo">
    </div>

    <div class="lesson-content">
        <?php
        include('config.php');
        session_start();

        if (!isset($_SESSION['username'])) {
            header("Location: login.php");
            exit();
        }

        // Get title from the URL
        $title = $_GET['title'];
        $tutorID = $_GET['tutorID'];
        $studentID = $_GET['studentID'];

        // Fetch lesson content based on the title
        $sql = "SELECT content FROM tutorlesson WHERE title = ? AND tutorid = ? AND studentid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $title, $tutorID, $studentID);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the lesson was found
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "<h2>" . htmlspecialchars($title) . "</h2>";
            echo "<p>" . htmlspecialchars($row['content']) . "</p>";
        } else {
            echo "<h2>Lesson Not Found</h2>";
            echo "<p>The requested lesson content is not available.</p>";
        }

        // Close the connection
        $stmt->close();
        $conn->close();
        ?>
    </div>

    <div>
        <button onclick="window.history.back()">Back</button>
    </div>
</body>
</html>
