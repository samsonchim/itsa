<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "itsa";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $ip_address = $_POST['ip_address'];

    if (empty($email) || empty($password)) {
        $response['message'] = 'Email and Password are required';
    } else {
        $stmt = $conn->prepare('SELECT id, password FROM technicians WHERE email = ?');
        if ($stmt === false) {
            $response['message'] = 'Prepare failed: ' . $conn->error;
            echo json_encode($response);
            exit;
        }
        
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $update_stmt = $conn->prepare('UPDATE technicians SET ip_address = ? WHERE id = ?');
                if ($update_stmt === false) {
                    $response['message'] = 'Prepare failed: ' . $conn->error;
                    echo json_encode($response);
                    exit;
                }
                
                $update_stmt->bind_param('si', $ip_address, $id);
                if ($update_stmt->execute()) {
                    $_SESSION['id'] = $id;
                    $response['success'] = true;
                    $response['message'] = 'Login successful';
                } else {
                    $response['message'] = 'Failed to update IP address: ' . $update_stmt->error;
                }
                $update_stmt->close();
            } else {
                $response['message'] = 'Invalid email or password';
            }
        } else {
            $response['message'] = 'No technician found with this email';
        }
        $stmt->close();
    }
}

echo json_encode($response);
$conn->close();
?>
