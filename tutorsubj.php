<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Lessons</title>
    <link rel="stylesheet" href="tutorsubj.css"> <!-- Your CSS file -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <style>
        /* Custom body background color */
        body {
            background-color: #edf5fd; /* Set the body color */
        }
        .lesson-title {
            cursor: pointer; /* Change cursor to pointer */
            color: #007bff; /* Bootstrap primary color */
            text-decoration: underline; /* Underline text */
        }
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="Logo">
    </div>

    <div class="table-container">
        <table class="table table-hover"> <!-- Added table-hover class here -->
            <thead>
                <tr>
                    <th>Title</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include('config.php');
                session_start();

                if (!isset($_SESSION['username'])) {
                    header("Location: login.php");
                    exit();
                }
                // Fetch lessons based on tutorID and studentID
                $tutorID = $_GET['tutorID'];
                $studentID = $_GET['studentID'];

                $sql = "SELECT title FROM tutorlesson WHERE tutorid = ? AND studentid = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $tutorID, $studentID);
                $stmt->execute();
                $result = $stmt->get_result();

                // Display titles
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td class='lesson-title' onclick=\"window.location.href='tutorlesson.php?title=" . urlencode($row['title']) . "&tutorID=" . $tutorID . "&studentID=" . $studentID . "'\">" . htmlspecialchars($row['title']) . "</td>
                          </tr>";
                }

                // Close the connection
                $stmt->close();
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <div>
        <button id="addBtn">Add</button>
        <button onclick="window.history.back()">Back</button>
    </div>

    <!-- The Modal for Add Options -->
    <div id="optionModal" class="modal">
        <div class="modal-content">
            <span id="closeOptionModal" class="close">&times;</span>
            <h2>Add Option</h2>
            <button id="addLessonBtn">Add Lesson</button>
            <button id="addEssayTypeBtn">Add Essay Type</button>
        </div>
    </div>

    <!-- The Modal for Adding Lesson -->
    <div id="lessonModal" class="modal">
        <div class="modal-content">
            <span id="closeLessonModal" class="close">&times;</span>
            <h2>Add Lesson</h2>
            <form id="lessonForm" action="process_lesson.php" method="POST">
                <input type="hidden" id="tutorid" name="tutorid">
                <input type="hidden" id="studentid" name="studentid">
                
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
                
                <label for="content">Content:</label>
                <textarea id="content" name="content" required></textarea>
                
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <script>
        // Get the modals
        var optionModal = document.getElementById("optionModal");
        var lessonModal = document.getElementById("lessonModal");

        // Get the button that opens the option modal
        var addBtn = document.getElementById("addBtn");

        // Get the <span> elements that close the modals
        var closeOptionModal = document.getElementById("closeOptionModal");
        var closeLessonModal = document.getElementById("closeLessonModal");

        // When the user clicks the button, open the option modal
        addBtn.onclick = function() {
            // Get the URL parameters
            const params = new URLSearchParams(window.location.search);
            const tutorID = params.get('tutorID');
            const studentID = params.get('studentID');

            // Set the hidden input fields
            document.getElementById("tutorid").value = tutorID;
            document.getElementById("studentid").value = studentID;

            optionModal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the option modal
        closeOptionModal.onclick = function() {
            optionModal.style.display = "none";
        }

        // When the user clicks on <span> (x), close the lesson modal
        closeLessonModal.onclick = function() {
            lessonModal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modals, close them
        window.onclick = function(event) {
            if (event.target == optionModal) {
                optionModal.style.display = "none";
            } else if (event.target == lessonModal) {
                lessonModal.style.display = "none";
            }
        }

        // Handle adding lesson
        document.getElementById("addLessonBtn").onclick = function() {
            optionModal.style.display = "none"; // Close option modal
            lessonModal.style.display = "block"; // Open lesson modal
        }

        // Handle form submission
        document.getElementById("lessonForm").onsubmit = function(event) {
            event.preventDefault(); // Prevent default form submission

            // Optionally handle data (e.g., send it to the server)
            this.submit(); // Submit the form to the processing page
            lessonModal.style.display = "none"; // Close the lesson modal
        }

        // You can add functionality for "Add Essay Type" similarly if needed
        document.getElementById("addEssayTypeBtn").onclick = function() {
            alert("Add Essay Type functionality is not implemented yet.");
            optionModal.style.display = "none"; // Close option modal
        }
    </script>
</body>
</html>
