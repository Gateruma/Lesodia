<?php
session_start();
include('config.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$firstName = $_SESSION['firstname'];
$lastName = $_SESSION['lastname'];
$email = $_SESSION['email'];

// Fetch the studentID based on the current logged-in user
$sql = "SELECT studentID FROM student WHERE userID = (SELECT user_ID FROM user WHERE username = ? LIMIT 1)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    echo "Student information not found.";
    exit(); // Ensure script stops if student information isn't found
}

$studentID = $student['studentID'];

// Fetch the block data based on the blockid in the URL
if (isset($_GET['blockid'])) {
    $blockid = $_GET['blockid'];
    $sql = "SELECT * FROM blocks_type WHERE blockid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $blockid);
    $stmt->execute();
    $result = $stmt->get_result();
    $blockData = $result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="playground.css">

    <title>Playground</title>

    <style>
#downloadButton {
    position: fixed;
    top: 82%;
    right: 5%;
    height: 10%;
    background-color: #1F6C52; /* Change to your desired color */
    color: white; /* Text color */
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

#downloadButton:hover {
    background-color: #14513A; /* Change to your desired hover color */
}

        #blockIdDisplay {
            position: fixed;
            bottom: 20px;
            left: 20px;
            color: white;
            background-color: rgba(0, 0, 0, 0.7);
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="header">
    <img src="logo.png" alt="Logo">
</div> 

<button class="return-button" type="button" onclick="returnToDashboard()">Return to Dashboard</button>


<div class="container">
    <div class="draggable-shape" onclick="placeInBondPaper(event, 'shape1.png')">
        <img src="shape1.png" alt="Shape 1">
    </div>
    <div class="draggable-shape" onclick="placeInBondPaper(event, 'shape2.png')">
        <img src="shape2.png" alt="Shape 2">
    </div>
    <div class="draggable-shape" onclick="placeInBondPaper(event, 'shape3.png')">
        <img src="shape3.png" alt="Shape 3">
    </div>
    <div class="draggable-shape" onclick="placeInBondPaper(event, 'shape4.png')">
        <img src="shape4.png" alt="Shape 4">
    </div>
    <div class="draggable-shape" onclick="placeInBondPaper(event, 'shape5.png')">
        <img src="shape5.png" alt="Shape 5">
    </div>
    <div class="draggable-shape" onclick="placeInBondPaper(event, 'shape6.png')">
        <img src="shape6.png" alt="Shape 6">
    </div>
</div>

<div class="bond-paper" id="bondPaper" onmousemove="moveShape(event)" onmouseup="stopDragging(event)">
    <!-- Content inside the bond paper -->
</div>

<button id="downloadButton" onclick="captureAndDownload()">Download and Submit</button>



<div id="blockInfo">
    <?php if (isset($blockData)) : ?>
        <h2><?php echo $blockData['title']; ?></h2>
        <p>Instruction: <?php echo $blockData['instruction']; ?></p>
        <?php
            // Fetch student ID
            $sql = "SELECT studentID FROM student WHERE userID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $_SESSION['user_ID']);
            $stmt->execute();
            $result = $stmt->get_result();
            $studentData = $result->fetch_assoc();
            $stmt->close();
            $studentID = $studentData['studentID'];
        ?>
    <?php else : ?>
        <p>No block information available</p>
    <?php endif; ?>
</div>

<div id="blockImage">
    <?php if (!empty($blockData['image'])) : ?>
        <img class="block-image" src="data:image/jpg;base64, <?php echo base64_encode($blockData['image']); ?>" alt="Block Image">
    <?php endif; ?>

    
</div>


<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script>
var shiftX, shiftY;
var selectedShape = null;

function placeInBondPaper(event, shapeSrc) {
    var bondPaper = document.getElementById('bondPaper');
    var bondPaperBounds = bondPaper.getBoundingClientRect();

    var newShape = document.createElement('div');
    newShape.classList.add('draggable-shape');
    newShape.style.position = 'absolute';
    newShape.style.left = (event.clientX - bondPaperBounds.left - shiftX) + 'px';
    newShape.style.top = (event.clientY - bondPaperBounds.top - shiftY) + 'px';

    var shapeImage = document.createElement('img');
    shapeImage.src = shapeSrc;
    shapeImage.alt = 'Shape';
    newShape.appendChild(shapeImage);

    bondPaper.appendChild(newShape);

    newShape.addEventListener('mousedown', function (e) {
        var bounds = newShape.getBoundingClientRect();
        shiftX = e.clientX - bounds.left;
        shiftY = e.clientY - bounds.top;

        newShape.style.zIndex = 1000;
        newShape.classList.add('glowing');  // Add glow effect on mousedown
        selectedShape = newShape;  // Set as the selected shape
    });

    newShape.addEventListener('click', function (e) {
        e.stopPropagation();
    });
}

function returnToDashboard() {
    window.history.back();
}


function captureAndDownload() {
    // Get the bond paper element
    var bondPaper = document.getElementById('bondPaper');

    // Create a canvas element
    html2canvas(bondPaper).then(function (canvas) {
        // Convert the canvas to an image
        var imgData = canvas.toDataURL('image/png');

        // Create a temporary anchor element
        var downloadLink = document.createElement('a');
        downloadLink.href = imgData;
        downloadLink.download = 'bond_paper.png';

        // Simulate a click on the download link after a short delay
        setTimeout(function () {
            downloadLink.click();
            // Send the image data to block_response.php
            sendResponseToServer(imgData);
        }, 100);
    });
}

function sendResponseToServer(imageFile) {
    var blockId = <?php echo isset($_GET['blockid']) ? $_GET['blockid'] : "null"; ?>;
    if (blockId) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "block_response.php", true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                console.log("Response submitted successfully");
            }
        };
        
        var formData = new FormData();
        formData.append('blockid', blockId);
        formData.append('studentid', <?php echo $studentID; ?>);
        formData.append('image', imageFile);
        
        xhr.send(formData);
    } else {
        console.error("Block ID is missing");
    }
}


function moveShape(event) {
    var draggedElement = document.querySelector('.draggable-shape.dragging');
    if (draggedElement !== null) {
        var containerBounds = document.getElementById('bondPaper').getBoundingClientRect();
        var x = event.pageX - shiftX - containerBounds.left;
        var y = event.pageY - shiftY - containerBounds.top;

        x = Math.min(Math.max(x, 0), containerBounds.width - draggedElement.offsetWidth);
        y = Math.min(Math.max(y, 0), containerBounds.height - draggedElement.offsetHeight);

        draggedElement.style.left = x + 'px';
        draggedElement.style.top = y + 'px';
    }
}

function stopDragging(event) {
    var draggedElement = document.querySelector('.draggable-shape.dragging');
    if (draggedElement !== null) {
        draggedElement.classList.remove('dragging');
        draggedElement.classList.remove('glowing');  // Remove glow effect on mouseup
    }
}

document.addEventListener('mousedown', function (event) {
    var shape = event.target.closest('.draggable-shape');
    if (shape) {
        shape.classList.add('dragging');
        shape.classList.add('glowing');  // Add glow effect on mousedown
        selectedShape = shape;  // Set the clicked shape as the selected shape
    }
});

document.addEventListener('keydown', function (event) {
    if (selectedShape) {
        var shapeImage = selectedShape.querySelector('img');
        if (event.key === 'ArrowUp') {
            // Increase the size
            var currentWidth = shapeImage.clientWidth;
            var currentHeight = shapeImage.clientHeight;
            shapeImage.style.width = (currentWidth * 1.1) + 'px';
            shapeImage.style.height = (currentHeight * 1.1) + 'px';
        } else if (event.key === 'ArrowDown') {
            // Decrease the size
            var currentWidth = shapeImage.clientWidth;
            var currentHeight = shapeImage.clientHeight;
            shapeImage.style.width = (currentWidth * 0.9) + 'px';
            shapeImage.style.height = (currentHeight * 0.9) + 'px';
        } else if (event.key === 'Backspace') {
            // Remove the shape
            selectedShape.parentNode.removeChild(selectedShape);
            selectedShape = null;
        }
    }
});

</script>
</body>
</html>
