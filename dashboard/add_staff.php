<?php

session_start();

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['id'])) {
    header("Location: ../admin_login.html");
    exit;
}

$loggedInUserId = $_SESSION['id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "itsa";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs (optional but recommended)
    $staffName = htmlspecialchars($_POST['staff_name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    try {
        // Connect to the database
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Generate password and store it in a variable
        $visiblePassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 7);
        $hashedPassword = password_hash($visiblePassword, PASSWORD_DEFAULT);

        // Generate system ID (if needed)
        $generatedSystemId = substr(str_shuffle('0123456789'), 0, 4);

        // Check if the email exists in the database
        $stmt = $conn->prepare("SELECT id FROM staffs WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $existingStaff = $stmt->fetch(PDO::FETCH_ASSOC);

        // Prepare SQL statement based on whether the email exists
        if ($existingStaff) {
            // Update existing staff record
            $stmt = $conn->prepare("UPDATE staffs SET name = :name, system_id = :system_id, password = :password, visible_password = :visible_password, organisation_id = :organisation_id WHERE email = :email");
        } else {
            // Insert new staff record
            $stmt = $conn->prepare("INSERT INTO staffs (name, email, system_id, password, visible_password, organisation_id) VALUES (:name, :email, :system_id, :password, :visible_password, :organisation_id)");
        }

        // Bind parameters
        $stmt->bindParam(':name', $staffName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':system_id', $generatedSystemId);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':visible_password', $visiblePassword);
        $stmt->bindParam(':organisation_id', $loggedInUserId);
        $stmt->execute();

        // Redirect to the same page or another after processing
        header("Location: staffs.php");
        exit;

    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }

    // Close database connection
    $conn = null;
}
?>
