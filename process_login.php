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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $organisation_email = $_POST['organisation_email'];
    $password = $_POST['password'];

    if (empty($organisation_email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Email and Password are required.']);
        exit;
    }

    $sql = "SELECT id, password FROM organisations WHERE organisation_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $organisation_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['id'] = $user['id'];
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>