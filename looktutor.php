<?php
include('config.php');
session_start();

// Get parent ID from URL
$currentUserParentID = isset($_GET['parentid']) ? $_GET['parentid'] : null;

// Fetch the student ID based on the parent ID
$studentID = null;
if ($currentUserParentID) {
    $sqlStudent = "SELECT studentID FROM parent WHERE parentID = ?";
    $stmt = $conn->prepare($sqlStudent);
    $stmt->bind_param("i", $currentUserParentID);
    $stmt->execute();
    $stmt->bind_result($studentID);
    $stmt->fetch();
    $stmt->close();
}

// Fetch all videos with their captions, uploader information, upload date, rate, and billinfo
$sqlVideos = "
SELECT tutorvideo.video, tutorvideo.caption, tutorvideo.date, user.firstname, user.lastname, user.profpic, tutor.tutorid, tutor.rate, tutor.billinfo
FROM tutorvideo
JOIN teacher ON tutorvideo.tutorid = teacher.teacher_id
JOIN user ON teacher.user_ID = user.user_ID
JOIN tutor ON tutor.teacherid = teacher.teacher_id
";

$result = $conn->query($sqlVideos);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="looktutor.css">
    <title>Tutor Videos</title>
</head>
<body>
    <header>
        Tutor Videos
    </header>
    <div class="videos-container">
    <?php
    if ($result->num_rows > 0) {
        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            echo '<div class="video-item">';
            echo '<div class="video-wrapper">';
            echo '<video controls>';
            echo '<source src="videos/' . htmlspecialchars($row['video']) . '" type="video/mp4">';
            echo 'Your browser does not support the video tag.';
            echo '</video>';
            echo '</div>';
            echo '<div class="info-wrapper">';
            if (!empty($row['profpic'])) {
                $imageData = base64_encode($row['profpic']);
                echo '<div class="uploader-info">';
                echo '<img src="data:image/jpeg;base64,' . $imageData . '" alt="Uploader Profile Picture">';
                echo '<div><strong>' . htmlspecialchars($row['firstname']) . ' ' . htmlspecialchars($row['lastname']) . '</strong></div>';
                echo '</div>';
            } else {
                echo '<div class="uploader-info">';
                echo '<img src="PE.png" alt="Uploader Profile Picture">'; // Default picture
                echo '<div><strong>' . htmlspecialchars($row['firstname']) . ' ' . htmlspecialchars($row['lastname']) . '</strong></div>';
                echo '</div>';
            }

            echo '<div class="video-info">';
            echo '<div class="caption">' . htmlspecialchars($row['caption']) . '</div>';
            echo '<div class="upload-date">' . htmlspecialchars(date('F j, Y', strtotime($row['date']))) . '</div>'; // Format the date
            
            // Display the tutor ID and rate
            echo '<div>Rate: ' . htmlspecialchars($row['rate']) . '</div>'; // Display rate
            
            echo '<div class="button-container">'; // Button container
            echo '<button class="open-modal" data-tutorid="' . htmlspecialchars($row['tutorid']) . '" data-billinfo="' . htmlspecialchars($row['billinfo']) . '">Request Payment</button>';
            echo '<button>Button 2</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo "<div class='caption'>No videos found.</div>";
    }
    $conn->close();
    ?>
    </div>

    <!-- Modal Structure -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Submit Payment Request and Schedule</h2>
            <p id="billinfoDisplay">Send the payment to this number: <span id="modal-billinfo"></span></p>
            <!-- Combined Payment and Schedule Form -->
            <form action="submit-schedule.php" method="post" enctype="multipart/form-data">
                <!-- Payment Section -->
                <label for="receipt">Upload Receipt:</label>
                <input type="file" name="receipt" id="receipt" accept="image/*" required>

                <label for="amount">Amount:</label>
                <input type="number" name="amount" id="amount" required step="0.01">

                <label for="message">Message:</label>
                <textarea name="message" id="message" rows="4" required></textarea>

                <!-- Schedule Section -->
                <label for="start_time">Start Time:</label>
                <input type="datetime-local" name="start_time" id="start_time" required>

                <label for="end_time">End Time:</label>
                <input type="datetime-local" name="end_time" id="end_time" required>

                <!-- Session Section -->
                <label for="session">Number of Sessions:</label>
                <input type="number" name="session" id="session" min="1">

                <!-- Hidden Fields -->
                <input type="hidden" name="status" value="Pending">
                <input type="hidden" name="tutorid" id="modal-tutorid" value="">
                <input type="hidden" name="parentid" value="<?php echo htmlspecialchars($currentUserParentID); ?>">
                <input type="hidden" name="studentID" value="<?php echo htmlspecialchars($studentID); ?>">

                <button type="submit">Submit Payment and Schedule</button>
            </form>
        </div>
    </div>

    <script>
        // Get the modal
        var modal = document.getElementById("myModal");

        // Get the button that opens the modal
        var buttons = document.querySelectorAll(".open-modal");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks on a button, open the modal and set the tutorid and billinfo
        buttons.forEach(function(button) {
            button.onclick = function() {
                var tutorId = this.getAttribute('data-tutorid');
                var billInfo = this.getAttribute('data-billinfo'); // Get the billinfo
                document.getElementById('modal-tutorid').value = tutorId; // Set the tutorid
                document.getElementById('modal-billinfo').textContent = billInfo; // Set the billinfo
                modal.style.display = "flex";
            };
        });

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        };

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        };
    </script>

</body>
</html>
