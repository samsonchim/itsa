<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "itsa";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['id'])) {
    die("You need to be logged in to view this page.");
}

if (!isset($_GET['request_id'])) {
    die("Request ID is required.");
}

$request_id = $_GET['request_id'];

// Fetch the record from ongoing_maintenance
$sql = "SELECT * FROM ongoing_maintenance WHERE request_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("No such request found.");
}

$record = $result->fetch_assoc();
$stmt->close();

// Insert the record into completed_maintenance
$sql = "INSERT INTO completed_maintenance (request_id, technician_id, organisation_id, staff_email, note, completion_date) VALUES (?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiss", $record['request_id'], $record['technician_id'], $record['organisation_id'], $record['staff_email'], $record['note']);
$stmt->execute();
$stmt->close();

// Delete the record from ongoing_maintenance
$sql = "DELETE FROM ongoing_maintenance WHERE request_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$stmt->close();

$conn->close();

// Redirect back to the ongoing maintenance page
header("Location: ongoing_maintenance.php");
exit;
?>
