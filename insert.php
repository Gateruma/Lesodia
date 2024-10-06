<?php
include("config.php");


session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST["firstname"] ?? '';
    $lastname = $_POST["lastname"] ?? '';
    $email = $_POST["email"] ?? '';
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';
    $role = $_POST["role"] ?? '';
    $dateofbirth = $_POST["dateofbirth"] ?? '';
    $address = $_POST["address"] ?? '';
    $gender = $_POST["gender"] ?? '';
    $grade = $_POST["grade"] ?? '';
    $section = $_POST["section"] ?? '';
    $category = $_POST["category"] ?? '';
    $gfirstname = $_POST["gfirstname"] ?? '';
    $glastname = $_POST["glastname"] ?? '';
    $gcontact = $_POST["gcontact"] ?? '';
    $gemail = $_POST["gemail"] ?? '';

    $sql_user = "INSERT INTO user (firstname, lastname, email, username, password, role, dateofbirth, address, gender, grade, section, category, gfirstname, glastname, gcontact, gemail)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("ssssssssssssssis", $firstname, $lastname, $email, $username, $password, $role, $dateofbirth, $address, $gender, $grade, $section, $category, $gfirstname, $glastname, $gcontact, $gemail);

    if ($stmt_user->execute()) {
        $user_id = $conn->insert_id;  
        if ($role == "teacher") {
            $sql_teacher = "INSERT INTO teacher (user_ID)
                            VALUES (?)";

            $stmt_teacher = $conn->prepare($sql_teacher);
            $stmt_teacher->bind_param("i", $user_id);

            if (!$stmt_teacher->execute()) {
                echo '<script>';
                echo 'alert("Error: ' . $stmt_teacher->error . '");';
                echo 'window.location.href = "index.html";';
                echo '</script>';
            }

            $stmt_teacher->close();
        } elseif ($role == "student") {
            $sql_student = "INSERT INTO student (userID) VALUES (?)";

            $stmt_student = $conn->prepare($sql_student);
            $stmt_student->bind_param("i", $user_id);

            if (!$stmt_student->execute()) {
                echo '<script>';
                echo 'alert("Error: ' . $stmt_student->error . '");';
                echo 'window.location.href = "index.html";';
                echo '</script>';
            }

            $stmt_student->close();
        } elseif ($role == "parent") {
            // Insert into parent table and associate with the user
            $sql_parent = "INSERT INTO parent (userID) VALUES (?)";

            $stmt_parent = $conn->prepare($sql_parent);
            $stmt_parent->bind_param("i", $user_id);

            if (!$stmt_parent->execute()) {
                echo '<script>';
                echo 'alert("Error: ' . $stmt_parent->error . '");';
                echo 'window.location.href = "index.html";';
                echo '</script>';
            }

            $stmt_parent->close();
        }

        $_SESSION['firstname'] = $firstname;
        $_SESSION['lastname'] = $lastname;
        $_SESSION['email'] = $email;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;

        echo '<script>';
        echo 'alert("Registration successful");';
        echo 'window.location.href = "index.html";';
        echo '</script>';
    } else {
        echo '<script>';
        echo 'alert("Error: ' . $stmt_user->error . '");';
        echo 'window.location.href = "index.html";';
        echo '</script>';
    }

    $stmt_user->close();
}
$conn->close();
?>
