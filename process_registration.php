<?php
session_start(); 

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "itsa";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

$organisation_name = $_POST['organisation_name'];
$organisation_email = $_POST['organisation_email'];
$organisation_phone = $_POST['organisation_phone'];
$password = $_POST['password'];

// Check if email already exists
$sql = "SELECT * FROM organisations WHERE organisation_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $organisation_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
    exit;
}

// Assign ID starting from 4000
$sql = "SELECT MAX(id) AS max_id FROM organisations";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$new_id = $row['max_id'] >= 4000 ? $row['max_id'] + 1 : 4000;


$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Insert new organisation
$sql = "INSERT INTO organisations (id, organisation_name, organisation_email, organisation_phone, password) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $new_id, $organisation_name, $organisation_email, $organisation_phone, $hashed_password);

if ($stmt->execute()) {
    $_SESSION['id'] = $new_id;
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to create account']);
}

$conn->close();
?>
