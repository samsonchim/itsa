<?php
// Database connection settings
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "itsa"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the required POST variables are set
if (isset($_POST['note']) && isset($_POST['request_id']) && isset($_POST['technician_id']) && isset($_POST['organisation_id']) && isset($_POST['staff_email'])) {
    // Get form data
    $note = $_POST['note'];
    $request_id = $_POST['request_id'];
    $technician_id = $_POST['technician_id'];
    $organisation_id = $_POST['organisation_id'];
    $staff_email = $_POST['staff_email'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO ongoing_maintenance (note, request_id, technician_id, organisation_id, staff_email) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $note, $request_id, $technician_id, $organisation_id, $staff_email);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect to ongoing_maintenance.php upon successful insertion
        header("Location: ongoing_maintenance.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
} else {
    echo "Error: Required form data is missing.";
}

// Close the connection
$conn->close();
?>
