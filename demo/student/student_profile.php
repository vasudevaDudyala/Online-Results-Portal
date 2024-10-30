<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    // Redirect or handle the case when the user is not logged in
    // For example, you can redirect them to a login page
    header("Location: login.php");
    exit();
}

// Assuming you have already started the session and have $student_id in the session variable
$student_id = $_SESSION["user_id"];

include '../configuration.php';

// Handle form submission for editing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["edit_mode"])) {
        // Form is in edit mode, perform the update
        $newStudentName = $_POST["new_student_name"];
        $newGender = $_POST["new_gender"];
        $newDOB = $_POST["new_dob"];
        $newPhoneNumber = $_POST["new_phone_number"];

        $newDOB = substr($newDOB,8,2).substr($newDOB,5,2).substr($newDOB,0,4);
        

        $updateSql = "UPDATE USER_DETAILS SET USER_NAME = ?, USER_GENDER = ?, USER_DOB = ?, USER_PH = ? WHERE USER_ID = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("sssss", $newStudentName, $newGender, $newDOB, $newPhoneNumber, $student_id);
        $updateStmt->execute();

        // Close the statement
        $updateStmt->close();
    }
}

// Prepare and execute SQL query to fetch student details
$sql = "SELECT * FROM USER_DETAILS WHERE USER_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();

// Get result set
$result = $stmt->get_result();

// Check if any result is returned
if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        // Display the student details
        echo "Student ID: " . $row["USER_ID"] . "<br>";

        $dob = $row["USER_DOB"];

        if (isset($_GET["edit"])) {
            // Form is in edit mode, show editable fields
            echo "<form method='post' action='?edit=1'>"; // Include a query parameter to indicate edit mode
            echo "New Student Name: <input type='text' name='new_student_name' value='" . $row["USER_NAME"] . "'> <br>";
            echo "New Gender: <input type='text' name='new_gender' value='" . $row["USER_GENDER"] . "'> <br>";
            
            echo "New Date of Birth: <input type='date' name='new_dob' value='" . $row["USER_DOB"] . "'> <br>";
            echo "New Phone Number: <input type='text' name='new_phone_number' value='" . $row["USER_PH"] . "'> <br>";
            echo "<input type='hidden' name='edit_mode' value='1'>"; // Hidden field to indicate edit mode
            echo "<input type='submit' value='Save Changes'>";
            echo "</form>";
        } else {
            // Display the view mode with an Edit button
            echo "Student Name: " . $row["USER_NAME"] . "<br>";
            echo "Gender: " . $row["USER_GENDER"] . "<br>";
            echo "Date of Birth: " . substr($dob,0,2) . '/' . substr($dob,2,2) . '/' . substr($dob,4,4) . "<br>";
            echo "Phone Number: " . $row["USER_PH"] . "<br><br><br>";
            echo "<a href='?edit=1' style='text-decoration:none;color:black;padding: 10px;border:10px;background-color:orange;border-radius:10%;'>Edit</a>"; // Link to enter edit mode
        }
    }
} else {
    echo "0 results";
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
