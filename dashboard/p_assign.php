<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: staff_login.html"); 
    exit;
}

// Validate technician id from URL parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid technician ID.");
}

$organisation_id = $_SESSION['id'];
$technician_id = $_GET['id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "itsa";

try {
    // Establishing a connection to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insert into p_assignment table
    $stmt = $conn->prepare("INSERT INTO p_assignment (organisation_id, technician_id) VALUES (:organisation_id, :technician_id)");
    $stmt->bindParam(':organisation_id', $organisation_id);
    $stmt->bindParam(':technician_id', $technician_id);
    $stmt->execute();

    $message = "Technician assigned successfully.";

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$conn = null; 
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
            "Informing the technician...",
            "Informing the technician...",
            "Handing your organisation inventory to them...",
            "Concluding..."
        ];
        let messageIndex = 0;
        const messageElement = document.getElementById('message');

        function showNextMessage() {
            messageIndex++;
            if (messageIndex < messages.length) {
                messageElement.textContent = messages[messageIndex];
            } else {
                window.location.href = 'technicians.php';
            }
        }

        setInterval(showNextMessage, 3000);
    </script>
</body>
</html>
