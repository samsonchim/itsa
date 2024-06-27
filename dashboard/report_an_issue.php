<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['id'])) {
    header("Location: staff_login.html"); 
    exit;
}

$loggedInUserId = $_SESSION['id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "itsa";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectIssue = htmlspecialchars($_POST['subjectIssue']);
    $description = htmlspecialchars($_POST['description']);
    $dayOfNotice = $_POST['dayOfNotice']; 

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Retrieve organisation_id and ip_address for the logged-in staff member
        $stmtOrg = $conn->prepare("SELECT organisation_id, ip_address FROM staffs WHERE id = :staff_id");
        $stmtOrg->bindParam(':staff_id', $loggedInUserId);
        $stmtOrg->execute();
        $row = $stmtOrg->fetch(PDO::FETCH_ASSOC);
        $organisationId = $row['organisation_id'];
        $ipAddress = $row['ip_address'];

        // Insert into request_sent table
        $stmt = $conn->prepare("INSERT INTO request_sent (staff_id, organisation_id, subject_issue, description, notice_date, staff_ip) VALUES (:staff_id, :organisation_id, :subject_issue, :description, :notice_date, :staff_ip)");
        
        $stmt->bindParam(':staff_id', $loggedInUserId);
        $stmt->bindParam(':organisation_id', $organisationId);
        $stmt->bindParam(':subject_issue', $subjectIssue);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':notice_date', $dayOfNotice);
        $stmt->bindParam(':staff_ip', $ipAddress);

        $stmt->execute();

        header("Location: staff_dashboard.php"); 
        exit;
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }

    $conn = null;
}
?>
