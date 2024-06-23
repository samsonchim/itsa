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
    $staffId = $_GET['id'];

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT id FROM staffs WHERE id = :id AND organisation_id = :organisation_id");
        $stmt->bindParam(':id', $staffId);
        $stmt->bindParam(':organisation_id', $loggedInUserId);
        $stmt->execute();
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($staff) {
            $stmt = $conn->prepare("DELETE FROM staffs WHERE id = :id");
            $stmt->bindParam(':id', $staffId);
            $stmt->execute();
        }

        header("Location: staffs.php");
        exit;
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }

    $conn = null;
} else {
    header("Location: staffs.php");
    exit;
}
?>