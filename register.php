<?php /*
// Include the database configuration
include("config.php");
// Create a connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assuming you have user registration form data submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and validate form data
    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];
    $dateofbirth = $_POST["dateofbirth"];
    $address = $_POST["address"];
    $gender = $_POST["gender"];
    $ebcollege = $_POST["ebcollege"];
    $ebhighschool = $_POST["ebhighschool"];
    $ebelementary = $_POST["ebelementary"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $role = $_POST["role"]; // Retrieve the role field

    // Hash the password
    //$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // SQL query with prepared statement to insert data into the "register" table
    $sql = "INSERT INTO register (firstname, lastname, dateofbirth, address, gender, username, password, role)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $firstname, $lastname, $dateofbirth, $address, $gender, $username, $hashedPassword, $role);

    if ($stmt->execute()) {
        echo "Registration successful";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close(); */
?>
