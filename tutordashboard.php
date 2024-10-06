<?php
include('config.php');
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Get the user ID based on the logged-in username
$sql_user = "SELECT user_ID FROM user WHERE username = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $username);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $row_user = $result_user->fetch_assoc();
    $userID = $row_user['user_ID'];

    // Get the teacher ID based on the user ID
    $sql_teacher = "SELECT teacher_id FROM teacher WHERE user_ID = ?";
    $stmt_teacher = $conn->prepare($sql_teacher);
    $stmt_teacher->bind_param("i", $userID);
    $stmt_teacher->execute();
    $result_teacher = $stmt_teacher->get_result();

    if ($result_teacher->num_rows > 0) {
        $row_teacher = $result_teacher->fetch_assoc();
        $tutorID = $row_teacher['teacher_id'];

        // Get the videos posted by the tutor
        $sql_videos = "SELECT * FROM tutorvideo WHERE tutorid = ? ORDER BY date DESC";
        $stmt_videos = $conn->prepare($sql_videos);
        $stmt_videos->bind_param("i", $tutorID);
        $stmt_videos->execute();
        $result_videos = $stmt_videos->get_result();

        // Fetch tutor rate from the tutor table
        $sql_tutor_rate = "SELECT rate FROM tutor WHERE tutorid = ?";
        $stmt_tutor_rate = $conn->prepare($sql_tutor_rate);
        $stmt_tutor_rate->bind_param("i", $tutorID);
        $stmt_tutor_rate->execute();
        $result_tutor_rate = $stmt_tutor_rate->get_result();

        if ($result_tutor_rate->num_rows > 0) {
            $tutor_rate_info = $result_tutor_rate->fetch_assoc();
            $tutorRate = $tutor_rate_info['rate'];
        } else {
            $tutorRate = "No rate available";
        }
    } else {
        echo "No teacher information found.";
        exit();
    }

    // Get tutor information
    $sql_tutor_info = "SELECT * FROM user WHERE user_ID = ?";
    $stmt_tutor_info = $conn->prepare($sql_tutor_info);
    $stmt_tutor_info->bind_param("i", $userID);
    $stmt_tutor_info->execute();
    $result_tutor_info = $stmt_tutor_info->get_result();

    if ($result_tutor_info->num_rows > 0) {
        $tutor_info = $result_tutor_info->fetch_assoc();
    } else {
        echo "No tutor information found.";
        exit();
    }
} else {
    echo "User not found.";
    exit();
}



// After fetching userID
$sql_tutor = "SELECT tutorid, rate FROM tutor WHERE userid = ?";
$stmt_tutor = $conn->prepare($sql_tutor);
$stmt_tutor->bind_param("i", $userID);
$stmt_tutor->execute();
$result_tutor = $stmt_tutor->get_result();

if ($result_tutor->num_rows > 0) {
    $row_tutor = $result_tutor->fetch_assoc();
    $tutorID = $row_tutor['tutorid'];
    $tutorRate = $row_tutor['rate']; // Assuming rate is also retrieved here
} else {
    echo "No tutor information found.";
    exit();
}


// Fetch payment requests for the current tutor
$sql_payments = "SELECT * FROM paymentreq WHERE tutorid = ?";
$stmt_payments = $conn->prepare($sql_payments);
$stmt_payments->bind_param("i", $tutorID);
$stmt_payments->execute();
$result_payments = $stmt_payments->get_result();
$payments = [];
if ($result_payments->num_rows > 0) {
    while ($row_payments = $result_payments->fetch_assoc()) {
        $payments[] = $row_payments; // Store payment records
    }
}
$stmt_payments->close();

// Don't forget to close the statement
$stmt_tutor->close();


$stmt_user->close();
$stmt_teacher->close();
$stmt_videos->close();
$stmt_tutor_info->close();
$stmt_tutor_rate->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="tutordash.css">
    <title>Tutor Dashboard</title>

</head>
<body>
    <div class="header">
        <h1>Tutor Dashboard</h1>
    </div>

    <div class="tutor-info">
        <?php
        if (isset($tutor_info['profpic']) && !empty($tutor_info['profpic'])) {
            $imageData = base64_encode($tutor_info['profpic']);
            $src = 'data:image/jpeg;base64,' . $imageData;
        } else {
            $src = 'PE.png'; // Use the path to your default profile image
        }
        ?>
        <img src="<?php echo htmlspecialchars($src); ?>" alt="Profile Picture" class="profile-pic">
        <p>
        <?php echo htmlspecialchars($tutor_info['firstname']); ?> &nbsp;  &nbsp; 
        <?php echo htmlspecialchars($tutor_info['lastname']); ?>
        </p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($tutor_info['email']); ?></p>
        <p><strong>Tutor ID:</strong> <?php echo htmlspecialchars($tutorID); ?></p> <!-- Display Tutor ID -->
        <p><strong>Rate:</strong> ₱ <?php echo htmlspecialchars($tutorRate); ?></p>
        <button onclick="openEditRateModal()" class="edit-rate-button">Edit Rate</button> <!-- Edit Rate Button -->
<button onclick="openContactInput()" class="edit-contact-button">Edit Contact</button> <!-- Edit Contact Button -->


        </div>

    <!-- Edit Rate Modal -->
    <div id="editRateModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditRateModal()">&times;</span>
            <h2>Edit Rate</h2>
            <form action="update-rate.php" method="post">
                <label for="rate">New Rate:</label>
                <input type="number" name="rate" id="rate" value="<?php echo htmlspecialchars($tutorRate); ?>" required step="0.01">
                <input type="hidden" name="tutorid" value="<?php echo htmlspecialchars($tutorID); ?>">
                <button type="submit">Update Rate</button>
            </form>
        </div>
    </div>


<!-- Contact Input Modal -->
<div id="contactInputModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeContactInput()">&times;</span>
        <h2>Edit Contact Number</h2>
        <form action="update_contact.php" method="post">
            <label for="contactNumber">Contact Number:</label>
            <input type="text" name="contact_number" id="contactNumber" required>
            <input type="hidden" name="tutorid" value="<?php echo htmlspecialchars($tutorID); ?>">
            <button type="submit">Update Contact</button>
        </form>
    </div>
</div>



    <!-- Button Container -->
    <div class="button-container">
        <button onclick="openModal()">Post a Video</button>
        <button onclick="openPaymentsModal()">Payments</button>
        <button onclick="redirectToTutorStudent()">Go to Tutor Student</button>
        <button onclick="redirectToDashboard()">Back to Dashboard</button>
    </div>

    <!-- Modal for posting a video -->
    <div id="videoModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Post a Video</h2>
            <form action="upload_video.php" method="post" enctype="multipart/form-data">
                <label for="caption">Caption:</label>
                <input type="text" name="caption" id="caption" required>
                <label for="video">Upload Video:</label>
                <input type="file" name="video" id="video" accept="video/*" required>
                <button type="submit">Upload Video</button>
                </form>
        </div>
    </div>

    <hr style="width: 45%; margin: 20px auto; border: 1px solid #ccc;">

    <!-- Videos Section -->
    <div class="videos-section">
        <h2>My Videos</h2>

        <div class="videos-grid">
            <?php
            if ($result_videos->num_rows > 0) {
                while ($video = $result_videos->fetch_assoc()) {
                    $videoPath = 'videos/' . $video['video']; // Adjust the path as necessary
                    echo '<div class="video-item">';
                    echo '<video onclick="openOverlay(this, \'' . htmlspecialchars($video['videoid']) . '\')" controls muted src="' . htmlspecialchars($videoPath) . '"></video>';
                    echo '<p><strong>Caption:</strong> ' . htmlspecialchars($video['caption']) . '</p>';
                    echo '</div>';
                }
            } else {
                echo '<p>No videos found.</p>';
            }
            ?>
        </div>
    </div>

<!-- Video Overlay -->
<div id="videoOverlay" class="video-overlay">
    <div class="video-overlay-content">
        <span class="close" onclick="closeOverlay()">&times;</span>
        <video id="overlayVideo" controls></video>
        <form id="deleteForm" action="delete_video.php" method="post" style="display: none;">
            <input type="hidden" name="videoid" id="videoid">
            <button type="submit" class="delete-button">Delete Video</button>
        </form>
    </div>
</div>

    <!-- Payments Modal -->
    <div id="paymentsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePaymentsModal()">&times;</span>
            <h2>Payment Requests</h2>
            <table>
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Receipt</th>
                        <th>Amount</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Parent ID</th>
                        <th>Student ID</th>
                        <th>Actions</th> <!-- Add Actions column for buttons -->
                    </tr>
                </thead>
                <tbody id="paymentsTableBody">
                    <!-- Payment rows will be injected here via JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
<div id="contactInput" style="display:none;">
    <input type="text" id="contactNumber" placeholder="Enter Contact Number" />
    <button onclick="saveContact()">Save</button>
</div>
<script>

    // Function to open the contact input modal
function openContactInput() {
    document.getElementById('contactInputModal').style.display = 'block';
}

// Function to close the contact input modal
function closeContactInput() {
    document.getElementById('contactInputModal').style.display = 'none';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    var contactInputModal = document.getElementById('contactInputModal');
    if (event.target == contactInputModal) {
        closeContactInput();
    }
};
function redirectToTutorStudent() {
    window.location.href = 'tutorstudent.php';
}
    // Open the video overlay
function openOverlay(videoElement, videoId) {
    const overlay = document.getElementById('videoOverlay');
    const overlayVideo = document.getElementById('overlayVideo');
    const deleteForm = document.getElementById('deleteForm');
    const videoIdInput = document.getElementById('videoid');

    // Set the source of the overlay video
    overlayVideo.src = videoElement.src;

    // Set the video ID in the delete form
    videoIdInput.value = videoId;

    // Show the overlay and the delete form
    overlay.style.display = 'flex';
    deleteForm.style.display = 'block'; // Show the delete button
}

function closeOverlay() {
    const overlay = document.getElementById('videoOverlay');
    const overlayVideo = document.getElementById('overlayVideo');

    // Stop the video and reset its source
    overlayVideo.pause();
    overlayVideo.src = '';

    // Hide the overlay
    overlay.style.display = 'none';
}


    // Function to open the edit rate modal
    function openEditRateModal() {
        document.getElementById('editRateModal').style.display = 'block';
    }

    // Function to close the edit rate modal
    function closeEditRateModal() {
        document.getElementById('editRateModal').style.display = 'none';
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        var editRateModal = document.getElementById('editRateModal');
        if (event.target == editRateModal) {
            closeEditRateModal();
        }
        var videoModal = document.getElementById('videoModal');
        if (event.target == videoModal) {
            closeModal();
        }
    };

    // Function to open the video posting modal
    function openModal() {
        document.getElementById('videoModal').style.display = 'block';
    }

    // Function to close the video posting modal
    function closeModal() {
        document.getElementById('videoModal').style.display = 'none';
    }

    // Redirect to the dashboard
    function redirectToDashboard() {
        window.location.href = 'dashboard.php';
    }

    // Open Payments Modal
    function openPaymentsModal() {
        var paymentsModal = document.getElementById('paymentsModal');
        paymentsModal.style.display = 'block';

        var paymentsTableBody = document.getElementById('paymentsTableBody');
        paymentsTableBody.innerHTML = ''; // Clear previous rows

        // Inject payment data with clickable receipt and action buttons
        var payments = <?php echo json_encode($payments); ?>;

payments.forEach(function(payment) {
    var row = '<tr>' +
        '<td>' + payment.paymentid + '</td>' +
        '<td><a href="receipt/' + payment.receipt + '" download>' + // Update to match the correct path
            '<img src="receipt/' + payment.receipt + '" alt="Receipt" width="50">' + // Update to match the correct path
            payment.receipt + // Display only the filename here
            '</a></td>' +
        '<td>₱' + payment.amount + '</td>' +
        '<td>' + payment.message + '</td>' +
        '<td>' + payment.status + '</td>' +
        '<td>' + payment.parentid + '</td>' +
        '<td>' + payment.studentID + '</td>' +
        // Add Accept and Reject buttons in Actions column
        '<td>' +
            '<button onclick="updatePaymentStatus(' + payment.paymentid + ', \'Accepted\')">Enroll</button>' +
        '</td>' +
        '</tr>';
    paymentsTableBody.innerHTML += row;
});


    }

    // Function to update payment status
    function updatePaymentStatus(paymentId, newStatus) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "update-payment-status.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onload = function() {
            if (xhr.status === 200) {
                // Update status in the modal
                document.querySelector('tr[data-payment-id="' + paymentId + '"] td:nth-child(5)').innerText = newStatus;
                alert('Payment ID ' + paymentId + ' has been ' + newStatus.toLowerCase());
            } else {
                alert('Failed to update payment status.');
            }
        };

        xhr.send("paymentid=" + paymentId + "&status=" + newStatus);
    }

    // Close Payments Modal
    function closePaymentsModal() {
        var paymentsModal = document.getElementById('paymentsModal');
        paymentsModal.style.display = 'none';
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        var editRateModal = document.getElementById('editRateModal');
        var videoModal = document.getElementById('videoModal');
        var paymentsModal = document.getElementById('paymentsModal'); // Add paymentsModal

        if (event.target == editRateModal) {
            closeEditRateModal();
        }
        if (event.target == videoModal) {
            closeModal();
        }
        if (event.target == paymentsModal) {
            closePaymentsModal(); // Close payments modal
        }
    };

function updatePaymentStatus(paymentId, newStatus) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "update-payment-status.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onload = function() {
    if (xhr.status === 200) {
        console.log(xhr.responseText); // Debugging line
        var response = JSON.parse(xhr.responseText);
        // ...
    } else {
        alert('Failed to update payment status. Status: ' + xhr.status);
    }
};


    xhr.send("paymentid=" + paymentId + "&status=" + newStatus);
}


</script>

</body>
</html>