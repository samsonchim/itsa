<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../admin_login.html");
    exit;
}

$loggedInUserId = $_SESSION['id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "itsa";

$requestId = $_GET['id'];

try {
    // Establishing a connection to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the request details
    $stmt = $conn->prepare("
        SELECT subject_issue, description, notice_date, created_at, staff_id
        FROM request_sent
        WHERE id = :id AND organisation_id = :org_id
    ");
    $stmt->bindParam(':id', $requestId);
    $stmt->bindParam(':org_id', $loggedInUserId);
    $stmt->execute();
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($request) {
        // Fetch the staff email using staff_id
        $stmt = $conn->prepare("SELECT email FROM staffs WHERE id = :staff_id");
        $stmt->bindParam(':staff_id', $request['staff_id']);
        $stmt->execute();
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($staff) {
            // Fetch a random technician
            $stmt = $conn->prepare("SELECT id FROM technicians ORDER BY RAND() LIMIT 1");
            $stmt->execute();
            $technician = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($technician) {
                // Insert into assigned_requests table
                $stmt = $conn->prepare("
                    INSERT INTO request_recieved (technician_id, organisation_id, staff_email, subject_issue, description, notice_date, recieved_on)
                    VALUES (:technician_id, :organisation_id, :staff_email, :subject_issue, :description, :notice_date, :received_on)
                ");
                $stmt->bindParam(':technician_id', $technician['id']);
                $stmt->bindParam(':organisation_id', $loggedInUserId);
                $stmt->bindParam(':staff_email', $staff['email']);
                $stmt->bindParam(':subject_issue', $request['subject_issue']);
                $stmt->bindParam(':description', $request['description']);
                $stmt->bindParam(':notice_date', $request['notice_date']);
                $stmt->bindParam(':received_on', $request['created_at']);
                $stmt->execute();

                $message = "Technician assigned successfully.";
            } else {
                $message = "No technicians available.";
            }
        } else {
            $message = "Staff email not found.";
        }
    } else {
        $message = "Request not found.";
    }

} catch (PDOException $e) {
    $message = "Connection failed: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading...</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #1c2431;
            color: white;
            font-family: "Roboto Condensed", sans-serif;
        }

        .preloader {
            text-align: center;
        }

        .preloader img {
            width: 120px;
            height: 120px;
        }

        .preloader .message {
            margin-top: 20px;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="preloader">
        <img src="loading.gif" alt="Loading...">
        <div class="message" id="message"><?php echo $message ?></div>
    </div>
    <script>
        const messages = [
            "Looking for the closest Technician to you...",
            "Booking appointment with them...",
            "Sharing the issue with them...",
            "Concluding..."
        ];
        let messageIndex = 0;
        const messageElement = document.getElementById('message');

        function showNextMessage() {
            messageIndex++;
            if (messageIndex < messages.length) {
                messageElement.textContent = messages[messageIndex];
            } else {
                window.location.href = 'all_request.php';
            }
        }

        setInterval(showNextMessage, 3000);
    </script>
</body>
</html>
