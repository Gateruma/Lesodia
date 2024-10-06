<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Attendance</title>
</head>
<body>
    <h1>Insert Attendance</h1>
    
    <form id="attendanceForm" method="POST" action="insertattendance.php">
        <label for="userID">User ID:</label>
        <input type="text" id="userID" name="userID" required>
        
        <label for="subjectID">Subject ID:</label>
        <input type="text" id="subjectID" name="subjectID" required>
        
        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required>
        
        <label for="status">Status:</label>
        <input type="text" id="status" name="status" required>
        
        <button type="submit">Submit Attendance</button>
    </form>
</body>
</html>
