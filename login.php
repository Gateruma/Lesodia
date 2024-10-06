<?php
include("config.php");

session_start();

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM user WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if ($password === $row["password"]) {
            $_SESSION['user_ID'] = $row['user_ID'];
            $_SESSION['username'] = $username;
            $_SESSION['firstname'] = $row['firstname'];
            $_SESSION['lastname'] = $row['lastname'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] === 'student') {
                header("Location: studentdashboard.php");
                exit();
            } elseif ($row['role'] === 'admin') {
                header("Location: admindashboard.php");
                exit();
            } elseif ($row['role'] === 'parent') {
                header("Location: parentdashboard.php"); // Redirect to parentdashboard.php for parents
                exit();
            } else {
                $user_id = $row['user_id'];
                $teacher_sql = "SELECT * FROM teacher WHERE user_id=?";
                $teacher_stmt = $conn->prepare($teacher_sql);
                $teacher_stmt->bind_param("i", $user_id);
                $teacher_stmt->execute();
                $teacher_result = $teacher_stmt->get_result();

                if ($teacher_result->num_rows > 0) {
                    $teacher_data = $teacher_result->fetch_assoc();

                    $_SESSION['subject'] = $teacher_data['subject'];
                }

                $teacher_stmt->close();

                header("Location: dashboard.php");
                exit();
            }
        } else {
            echo '<script>';
            echo 'alert("Error: Invalid Username or Password");';
            echo 'setTimeout(function() { window.location.href = "login.html"; }, 1000);';
            echo '</script>';
            exit();
        }
    } else {
        echo '<script>';
        echo 'alert("Error: Invalid Username or Password");';
        echo 'setTimeout(function() { window.location.href = "login.html"; }, 1000);';
        echo '</script>';
        exit();
    }

    $stmt->close();
}

$conn->close();
?>
