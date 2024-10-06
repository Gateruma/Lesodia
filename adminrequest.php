<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admindashboard.css">
    <title>Document</title>
    <style>
        .table-container {
            position: absolute; 
            top:350px;
            margin-bottom: 200px;        }

        .table-container1 {
            position: absolute; 
            top:200px;
            margin-bottom: 200px;
        }


        .table-container table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        .table-container th,
        .table-container td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table-container th {
            background-color: #f2f2f2;
        }

        .table-container tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .table-container tr:hover {
            background-color: #ddd;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
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

        .container h2 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }

        .center-table {
            margin: auto;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="logo.png" alt="Logo">
    </div>

    <button class="dashboard" onclick="redirectToAdminDashboard()">Dashboard</button>
    <button class="classrecord" onclick="redirectToAdminRequest()">Parent Request</button>

        <table class="center-table">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Parent ID</th>
                    <th>Student</th>
                    <th>File</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include('config.php');


                // Fetch pending requests from the request table
                $sql = "SELECT * FROM request WHERE status = 'pending'";
                $result = $conn->query($sql);

                // Check if there are rows in the result
                if ($result->num_rows > 0) {
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row["requestid"] . "</td>
                                <td>" . $row["parentid"] . "</td>
                                <td>" . $row["student"] . "</td>
                                <td><a href='" . $row["file"] . "' target='_blank'>" . $row["file"] . "</a></td>
                                <td>" . $row["status"] . "</td>
                                <td>
                                    <button onclick='showApproveModal(" . $row["requestid"] . ", \"" . $row["parentid"] . "\", \"" . $row["student"] . "\")'>Approve</button>
                                    <button onclick='rejectRequest(" . $row["requestid"] . ")'>Reject</button>
                                </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No pending requests found</td></tr>";
                }

                // Close the connection
                $conn->close();
                ?>
            </tbody>
        </table>

    <!-- Approve Modal -->
    <div id="approveModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeApproveModal()">&times;</span>
            <div>
                <label for="studentID">Student ID:</label>
                <input type="text" id="studentID" name="studentID" onkeyup="getSearchSuggestions(this.value)">
                <div id="searchSuggestions"></div>
                <input type="hidden" id="requestID" name="requestID">
                <input type="hidden" id="parentID" name="parentID">
            </div>
            <button onclick="submitApprove()">Submit</button>
        </div>
    </div>

        <table class="center-table1">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Parent ID</th>
                    <th>Student</th>
                    <th>File</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>

                <?php
                include('config.php');
                // Fetch approved requests from the request table
                $sql = "SELECT * FROM request WHERE status = 'approved'";
                $result = $conn->query($sql);

                // Check if there are rows in the result
                if ($result->num_rows > 0) {
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row["requestid"] . "</td>
                                <td>" . $row["parentid"] . "</td>
                                <td>" . $row["student"] . "</td>
                                <td><a href='" . $row["file"] . "' target='_blank'>" . $row["file"] . "</a></td>
                                <td>" . $row["status"] . "</td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No approved requests found</td></tr>";
                }

                // Close the connection
                $conn->close();
                ?>
            </tbody>
        </table>

    <script>
        function redirectToAdminDashboard() {
            window.location.href = 'admindashboard.php';
        }

        function redirectToAdminRequest() {
            window.location.href = 'adminrequest.php';
        }

        function openAddSubjectModal() {
            // Implementation for opening the add subject modal goes here
            console.log("Add subject modal opened.");
        }

        function openRemoveSubjectModal() {
            // Implementation for opening the remove subject modal goes here
            console.log("Remove subject modal opened.");
        }

        function showApproveModal(requestId, parentId, studentName) {
            document.getElementById('requestID').value = requestId;
            document.getElementById('parentID').value = parentId;
            document.getElementById('studentID').value = ""; // Clear student ID input
            document.getElementById('searchSuggestions').innerHTML = ""; // Clear search suggestions
            document.getElementById('approveModal').style.display = 'block';
        }

        function closeApproveModal() {
            document.getElementById('approveModal').style.display = 'none';
        }

        function submitApprove() {
            var requestId = document.getElementById('requestID').value;
            var parentId = document.getElementById('parentID').value;
            var studentId = document.getElementById('studentID').value;

            // Send AJAX request to update parent table
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'updateParentTable.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log(xhr.responseText);
                    closeApproveModal();
                    // Reload the page or update the table as needed
                    // window.location.reload(); 
                } else {
                    console.log('Request failed. Returned status of ' + xhr.status);
                }
            };
            xhr.send('requestId=' + requestId + '&parentId=' + parentId + '&studentId=' + studentId);
        }

        function rejectRequest(requestId) {
            // Add code here to handle rejecting the request with the given request ID
            console.log("Request rejected with ID: " + requestId);
        }

        function getSearchSuggestions(input) {
            if (input.length == 0) {
                document.getElementById("searchSuggestions").innerHTML = "";
                return;
            } else {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById("searchSuggestions").innerHTML = this.responseText;
                    }
                };
                xhttp.open("GET", "get_search_suggestions.php?input=" + input, true);
                xhttp.send();
            }
        }

        function selectSuggestion(suggestion) {
            // Set the value of the search input field to the selected studentID
            document.getElementById('studentID').value = suggestion;
            // Hide the search suggestions container
            document.getElementById('searchSuggestions').style.display = 'none';
        }z
    </script>
</body>

</html>
