<?php
include('config.php');


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newSubjectName = $_POST['newSubjectName'];

    $sql = "INSERT INTO storedsubj (subjectname) VALUES ('$newSubjectName')";
    $result = $conn->query($sql);

    $conn->close();
}
?>
