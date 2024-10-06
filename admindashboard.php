<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admindashboard.css">

    <title>Document</title>
</head>

<body>
<div class="header">
        <img src="logo.png" alt="Logo"> </div>

    <button class="dashboard" onclick="redirectToDashboard()">Dashboard</button>
    <button class="classrecord" onclick="redirectToAdminRequest()">Parent Request</button>

    <button class="add-subject-button" onclick="openAddSubjectModal()">Add Subject</button>
    <button class="remove-subject-button" onclick="openRemoveSubjectModal()">Remove Subject</button>

    <!-- Logout button -->
    <button class="myprofile" onclick="logout()">Logout</button>

    <div class="second-table">
        <?php
        // Connect to the database
        include('config.php');

        // Fetch data from the storedsubj table
        $sql_storedsubj = "SELECT * FROM storedsubj ORDER BY storedsubjid DESC";
        $result_storedsubj = $conn->query($sql_storedsubj);

        // Check if there are rows in the result
        if ($result_storedsubj->num_rows > 0) {
            echo "<div>
            <table class='styled-table'>
                <tr>
                    <th>Stored Subject ID</th>
                    <th>Subject Name</th>
                </tr>";
            // Output data of each row
            while ($row_storedsubj = $result_storedsubj->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row_storedsubj["storedsubjid"] . "</td>
                        <td>" . $row_storedsubj["subjectname"] . "</td>
                    </tr>";
            }
            echo "</table>";
        } else {
            echo "No records found";
        }

        // Close the connection
        $conn->close();
        ?>
    </div>

    <!-- Modal for adding subject -->
    <div id="addSubjectModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddSubjectModal()">&times;</span>
            <div class="edit" > <label for="newSubjectName">Enter Subject Name</label> </div>
            <input type="text" id="newSubjectName" name="newSubjectName">
            <button onclick="addNewSubject()">Add Subject</button>
        </div>
    </div>

    <!-- Modal for removing subject -->
    <div id="removeSubjectModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRemoveSubjectModal()">&times;</span>
            <div class="edit" ><label for="removeSubjectName">Enter Subject Name to Remove</label> </div>
            <input type="text" id="removeSubjectName" name="removeSubjectName">
            <button onclick="removeSubject()">Remove Subject</button>
        </div>
    </div>

    <script>
        function openAddSubjectModal() {
            document.getElementById('addSubjectModal').style.display = 'block';
        }

        function closeAddSubjectModal() {
            document.getElementById('addSubjectModal').style.display = 'none';
        }

        function openRemoveSubjectModal() {
            document.getElementById('removeSubjectModal').style.display = 'block';
        }

        function closeRemoveSubjectModal() {
            document.getElementById('removeSubjectModal').style.display = 'none';
        }

        function redirectToAdminRequest() {
            window.location.href = 'adminrequest.php';
        }

        function redirectToDashboard() {
            window.location.href = '';
        }

        function addNewSubject() {
            var newSubjectName = document.getElementById('newSubjectName').value;

            // Use AJAX to send data to addsubject.php
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'insert_storedsubj.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Handle the response if needed
                    console.log(xhr.responseText);

                    // Reload the second table to reflect the changes
                    location.reload();
                }
            };

            // Send the newSubjectName to addsubject.php
            xhr.send('newSubjectName=' + newSubjectName);

            closeAddSubjectModal(); // Close the modal after adding
        }

        function removeSubject() {
            var removeSubjectName = document.getElementById('removeSubjectName').value;

            // Use AJAX to send data to removesubject.php
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'removesubject.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Handle the response if needed
                    console.log(xhr.responseText);

                    // Reload the second table to reflect the changes
                    location.reload();
                }
            };

            // Send the removeSubjectName to removesubject.php
            xhr.send('removeSubjectName=' + removeSubjectName);

            closeRemoveSubjectModal(); // Close the modal after removing
        }

        function logout() {
            window.location.href = 'index.html';
        }
    </script>
</body>

</html>
