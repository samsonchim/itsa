<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: staff_login.html"); 
    exit;
}

$loggedInUserId = $_SESSION['id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "itsa";

if (isset($_GET['id'])) {
    $requestId = $_GET['id'];

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verify if the request belongs to the logged-in user
        $stmt = $conn->prepare("SELECT id FROM request_sent WHERE id = :id AND staff_id = :staff_id");
        $stmt->bindParam(':id', $requestId);
        $stmt->bindParam(':staff_id', $loggedInUserId);
        $stmt->execute();
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($request) {
            // Delete the request
            $deleteStmt = $conn->prepare("DELETE FROM request_sent WHERE id = :id");
            $deleteStmt->bindParam(':id', $requestId);
            $deleteStmt->execute();
        } else {
            echo "No record found for deletion or you do not have permission to delete this record.";
        }

        header("Location: staff_dashboard.php");
        exit;
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }

    $conn = null;
} else {
    echo "Invalid request. No ID parameter provided.";
    header("Location: staff_dashboard.php");
    exit;
}
?>
