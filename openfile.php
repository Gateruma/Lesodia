<?php
// Include the database connection
include('config.php');

session_start(); // Start the session
$randomStudentNames = ['John Doe', 'Jane Smith', 'Michael Johnson', 'Emily Brown', 'Chris Davis'];

function base64_safe_encode($data)
{
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

if (isset($_GET['id']) && isset($_GET['type'])) {
    $id = intval($_GET['id']); // Ensure id is an integer to prevent SQL injection
    $type = $_GET['type'];

    // Initialize data array
    $data = [];

    // Switch case to handle different types
    switch ($type) {
        case 'lesson':
            $sql = "SELECT title, content, file FROM lesson WHERE lessonid = ?";
            break;
        case 'essay':
            $sql = "SELECT title, question AS content, NULL AS file, type FROM essay_type WHERE essayid = ?";
            break;
        case 'block':
            $sql = "SELECT title, instruction, deadline, score, image FROM blocks_type WHERE blockid = ?";
            break;
        case 'guesstype':
            $sql = "SELECT title, items, deadline FROM guesstype WHERE guessid = ?";
            break;
        default:
            echo "Invalid request type.";
            exit();
    }

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Check for errors
    if ($stmt->error) {
        echo "Error executing SQL: " . $stmt->error;
        exit();
    }

    // Fetch result
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        echo "Data not found.";
        exit();
    }
    $stmt->close();

    // Additional processing for 'essay' type
    if ($type === 'essay') {
        $sql_score = "SELECT score FROM response WHERE essayid = ?";
        $stmt_score = $conn->prepare($sql_score);
        $stmt_score->bind_param("i", $id);
        $stmt_score->execute();
        $result_score = $stmt_score->get_result();
        $row_score = $result_score->fetch_assoc();
        $score = $row_score['score'] ?? 0;
        $stmt_score->close();

        // Fetch the teacher ID based on the logged-in user ID
        $sql_teacher_id = "SELECT teacher_id FROM teacher WHERE user_ID = ?";
        $stmt_teacher_id = $conn->prepare($sql_teacher_id);
        $stmt_teacher_id->bind_param("i", $_SESSION['user_ID']);
        $stmt_teacher_id->execute();
        $result_teacher_id = $stmt_teacher_id->get_result();
        $teacher_id = $result_teacher_id->num_rows > 0 ? $result_teacher_id->fetch_assoc()['teacher_id'] : null;
        $stmt_teacher_id->close();

        // Fetch students assigned to the current teacher with their names
        $teacher_students = [];
        if ($teacher_id) {
            $sql_teacher_students = "SELECT s.studentID, u.user_id, u.firstname, u.lastname 
                                    FROM student s 
                                    INNER JOIN user u ON s.userID = u.user_id 
                                    WHERE s.teacherID = ?";
            $stmt_teacher_students = $conn->prepare($sql_teacher_students);
            $stmt_teacher_students->bind_param("i", $teacher_id);
            $stmt_teacher_students->execute();
            $result_teacher_students = $stmt_teacher_students->get_result();
            while ($row_teacher_students = $result_teacher_students->fetch_assoc()) {
                $teacher_students[$row_teacher_students['studentID']] = [
                    'studentID' => $row_teacher_students['studentID'],
                    'firstname' => $row_teacher_students['firstname'],
                    'lastname' => $row_teacher_students['lastname']
                ];
            }
            $stmt_teacher_students->close();
        }
    }

    // Display the fetched data
    if (!empty($data)) {
        echo "<div class='line'></div>";
        echo "<div class='title'>{$data['title']}</div>";

        if (!empty($data['image'])) {
            $base64Image = base64_encode($data['image']);
            echo "<div class='block-image' style='margin: 0 auto; width: 70%; height: 50%; overflow: hidden; border: 1px solid #ddd; border-radius: 8px; display: flex; justify-content: center; align-items: center; position: absolute; top: 78%; left: 50%; transform: translate(-50%, -50%);'><img src='data:image/png;base64,$base64Image' alt='Block Image'></div>";
        }

        if (!empty($data['instruction'])) {
            echo "<div class='instruction' style='margin: 0 auto; width: 80%; max-width: 600px; background-color: #f9f9f9; border-radius: 10px; padding: 20px; position: absolute; top: 43%; left:5%; font-family: Arial, Helvetica, sans-serif; font-size: 20px;'>{$data['instruction']}</div>";
        }

        if (!empty($data['content'])) {
            echo "<div class='content'>{$data['content']}</div>";
        }

        if ($type === 'essay' && $data['type'] === 'remedial') {
            echo "<button class='assign' onclick='assignStudents($id)'>Assign students</button>";
        }

        if (!empty($data['deadline'])) {
            echo "<div class='deadline'>Deadline: {$data['deadline']}</div>";
        } else {
            echo "<div class='deadline'>No Deadline</div>";
        }

        if (!empty($data['score'])) {
            echo "<div class='score'>Score: {$data['score']}</div>";
        }

        if ($type === 'essay') {
            echo "<button class='submissions' type='button' onclick='seeSubmissions($id, $score)'>See submissions</button>";
        }

        if ($type === 'guesstype') {
            if (!empty($data['items'])) {
                echo "<div class='items'>{$data['items']}</div>";
            }
            if (!empty($data['deadline'])) {
                echo "<div class='deadline'>Deadline: {$data['deadline']}</div>";
                echo "<button class='submissions' type='button' onclick='seeSubmissions($id)'>See submissions</button>";

            }
            
        }
    } else {
        echo "Data not found.";
    }
} else {
    echo "Invalid request.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="openfile.css"> <!-- Include your CSS file here -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <title>KlaseMo - File Details</title>
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
            justify-content: center;
            align-items: center; /* Center the modal vertically */
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto; /* Center the modal horizontally and vertically */
            padding: 20px;
            border: 1px solid #888;
            border-radius: 5px;
            width: 50%;
            text-align: center;
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

    <button class="return-button" type="button" onclick="returnToDashboard()">Return to Dashboard</button>
    <?php if ($type === 'essay') : ?>
        <button class="submissions" type="button" onclick="seeSubmissions(<?php echo $id; ?>, <?php echo $score; ?>)">See submissions</button>
    <?php elseif ($type === 'block') : ?>
        <button class="submissions" type="button" onclick="seeSubmissions(<?php echo $id; ?>)">See submissions</button>
    <?php endif; ?>
    

    <!-- Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Teacher ID: <?php echo $teacher_id; ?></h3> <!-- Display teacher ID here -->
            <h3>Students Assigned to Current Teacher:</h3>
            <form id="studentForm" method="post" action="assign_students.php?id=<?php echo $id; ?>&type=<?php echo $type; ?>&subjectID=<?php echo urlencode($_GET['subjectID']); ?>">
                <input type="hidden" name="teacherID" value="<?php echo htmlspecialchars($_GET['teacherID']); ?>">
                <table>
                    <?php foreach ($teacher_students as $student) : ?>
                        <tr>
                            <td>
                                <input type="checkbox" id="<?php echo $student['studentID']; ?>" name="studentID[]" value="<?php echo $student['studentID']; ?>">
                                <label for="<?php echo $student['studentID']; ?>"><?php echo $student['firstname'] . ' ' . $student['lastname']; ?></label>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <button type="submit">Assign Selected Students</button>
            </form>
        </div>
    </div>

    <script>
        function returnToDashboard() {
            window.location.href = 'dashboard.php';
        }

        function seeSubmissions(id, score = null) {
            var type = '<?php echo $type; ?>';
            var url = 'submissions.php?id=' + id + '&type=' + type + '&subjectID=<?php echo urlencode($_GET['subjectID']); ?>';
            if (type === 'essay' && score !== null) {
                url += '&score=' + score;
            }
            window.location.href = url;
        }

        function assignStudents(id) {
            document.getElementById('myModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('myModal').style.display = 'none';
        }
    </script>
</body>

</html>
