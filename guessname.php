<?php
include('config.php');

session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$firstName = $_SESSION['firstname'];
$lastName = $_SESSION['lastname'];
$email = $_SESSION['email'];

// Fetch student data based on the current logged-in user
$sql_student = "
    SELECT * 
    FROM student 
    WHERE userID = (
        SELECT user_ID 
        FROM user 
        WHERE username = ? 
        LIMIT 1
    )";

$stmt_student = $conn->prepare($sql_student);
$stmt_student->bind_param("s", $username);
$stmt_student->execute();
$result_student = $stmt_student->get_result();

if ($result_student->num_rows > 0) {
    $row_student = $result_student->fetch_assoc();
    $studentID = $row_student['studentID'];
    $userID = $row_student['userID'];
    $teacherID = $row_student['teacherID'];
} else {
    echo "Student information not found.";
}

// Close the statement
$stmt_student->close();

// Fetch subject data based on the URL parameter
$subject = $_GET['subject'];

// Fetch guesstypes
$sql_guesstypes = "SELECT g.title, g.deadline, g.items
                    FROM guesstype g
                    JOIN subject s ON g.subjectID = s.subjectID
                    WHERE s.subjectName = ? AND g.teacherid = ?";
$stmt_guesstypes = $conn->prepare($sql_guesstypes);
$stmt_guesstypes->bind_param("si", $subject, $teacherID);
$stmt_guesstypes->execute();
$result_guesstypes = $stmt_guesstypes->get_result();

$guesstypes = array();

if ($result_guesstypes->num_rows > 0) {
    while ($row_guesstype = $result_guesstypes->fetch_assoc()) {
        $guesstypes[] = $row_guesstype;
    }
}

// Close the statement
$stmt_guesstypes->close();

// Fetch the items parameter from the URL
$items = isset($_GET['items']) ? intval($_GET['items']) : 0; // Convert to integer

// Define the questions array with all questions
$questions = [
    ['image' => 'guess/lion.png', 'name' => 'lion'],
    ['image' => 'guess/pig.png', 'name' => 'pig'],
    ['image' => 'guess/cow.png', 'name' => 'cow'],
    ['image' => 'guess/cat.png', 'name' => 'cat'],
    ['image' => 'guess/snake.png', 'name' => 'snake'],
    // Add more questions as needed
];

// Shuffle the questions array
shuffle($questions);

// Take only the specified number of questions if items is greater than 0
if ($items > 0) {
    $questions = array_slice($questions, 0, $items);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="guessname.css"> <!-- Link to your external CSS file -->
    <title>Visual Perception Assessment</title>
</head>
<body>
<div class="header">
    <img src="logo.png" alt="Logo">
</div>

<div class="container">
    <h1>Visual Perception Assessment</h1>
    <div class="image-wrapper">
        <div class="image-container">
            <img id="question-image" src="<?php echo $questions[0]['image']; ?>" alt="Object">
        </div>
    </div>
    <div class="letter-container">
        <!-- Letters will be dynamically generated here -->
    </div>
    <div class="answer-container" id="answer-container">
        <!-- Droppable area for arranging letters -->
    </div>
    <div id="score">Score: 0</div>
    <div class="message" id="message"></div>
    <form id="response-form" action="submit_guess.php" method="POST">
    <input type="hidden" name="studentID" value="<?php echo $studentID; ?>">
    <input type="hidden" name="score" id="hidden-score" value="0">
    <input type="hidden" name="date" id="hidden-date" value="">
    <input type="hidden" name="guessid" value="<?php echo $_GET['guessid']; ?>"> <!-- Add guessid from URL -->
    <button type="submit">Submit</button>
</form>

</div>

<script>
function submitResponses() {
    const date = new Date().toISOString().slice(0, 19).replace('T', ' '); // Get current date and time in MySQL format

    // Check if all required fields are present
    if (!studentID || !date || !score) {
        console.error("Missing required fields: studentID, date, score");
        return;
    }

    // Prepare data to send to PHP script
    const data = {
        studentID: studentID,
        date: date,
        score: score
    };

    fetch('submit_guess.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        // Handle response from PHP script if needed
        console.log(data);
        // Redirect to submit_guess.php
        window.location.href = 'submit_guess.php';
    })
    .catch(error => {
        console.error('There was a problem with your fetch operation:', error);
    });
}


document.addEventListener('DOMContentLoaded', function () {
    const answerContainer = document.getElementById('answer-container');
    const messageContainer = document.getElementById('message');
    const questionImage = document.getElementById('question-image');
    const container = document.querySelector('.container');
    const scoreElement = document.getElementById('score');
    const hiddenScoreInput = document.getElementById('hidden-score');
    const hiddenDateInput = document.getElementById('hidden-date');
    let score = 0; // Set initial score to zero

    // Update the displayed score
    scoreElement.textContent = `Score: ${score}`;

    let questions = <?php echo json_encode($questions); ?>;

    renderQuestion();

    function renderQuestion() {
        questionImage.src = questions[0].image;
        questionImage.alt = questions[0].name;

        // Generate letters for the current question
        generateLetters(scrambleWord(questions[0].name));

        // Initialize the drag and drop functionality
        const letters = document.querySelectorAll('.letter');
        letters.forEach(letter => {
            letter.addEventListener('dragstart', dragStart);
        });
    }

    function generateLetters(word) {
        const shuffledWord = shuffleArray([...word]);
        let html = '';
        shuffledWord.forEach(letter => {
            html += `<div class="letter" draggable="true">${letter}</div>`;
        });
        document.querySelector('.letter-container').innerHTML = html;
    }

    answerContainer.addEventListener('dragover', dragOver);
    answerContainer.addEventListener('drop', drop);

    function dragStart(event) {
        event.dataTransfer.setData('text/plain', event.target.textContent);
    }

    function dragOver(event) {
        event.preventDefault();
    }

    function drop(event) {
        event.preventDefault();
        const data = event.dataTransfer.getData('text');
        const newLetter = document.createElement('div');
        newLetter.className = 'letter';
        newLetter.textContent = data;
        answerContainer.appendChild(newLetter);

        const currentAnswer = Array.from(answerContainer.children)
            .map(child => child.textContent.trim())
            .join("");
        const correctWord = questions[0].name;
        const isCorrect = currentAnswer.toLowerCase() === correctWord.toLowerCase();
        if (isCorrect) {
            score++;
            scoreElement.textContent = `Score: ${score}`;
            hiddenScoreInput.value = score; // Update hidden input with the current score
            messageContainer.textContent = 'Correct!';
            messageContainer.classList.remove('wrong');
            messageContainer.classList.add('correct');
            setTimeout(() => {
                messageContainer.textContent = '';
                messageContainer.classList.remove('correct');
                answerContainer.innerHTML = ''; // Clear the container for the next question
                moveToNextQuestion();
            }, 1000);
        } else {
            messageContainer.textContent = 'Wrong!';
            messageContainer.classList.remove('correct');
            messageContainer.classList.add('wrong');
        }
    }

    // Remove letter when clicked in the answer container
    answerContainer.addEventListener('click', function (event) {
        if (event.target.classList.contains('letter')) {
            event.target.remove();
        }
    });

    function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }

    function scrambleWord(word) {
        // Scramble Word by shuffling its letters
        const letters = word.split('');
        return shuffleArray(letters).join('');
    }

    function moveToNextQuestion() {
        questions.shift(); // Remove the first question from the array
        if (questions.length > 0) {
            renderQuestion();
        } else {
            messageContainer.textContent = 'You have completed all questions!';
        }
    }

    // Update hidden date input with the current date and time when the form is submitted
    document.getElementById('response-form').addEventListener('submit', function () {
        hiddenDateInput.value = new Date().toISOString().slice(0, 19).replace('T', ' ');
    });
});
</script>

</body>
</html>
