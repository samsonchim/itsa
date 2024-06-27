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

// Function to calculate distance between two points using Haversine formula
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $theta = $lon1 - $lon2;
    $distance = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $distance = acos($distance);
    $distance = rad2deg($distance);
    $distance = $distance * 60 * 1.1515; // Miles

    return $distance;
}

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
        // Fetch the staff email and IP using staff_id
        $stmt = $conn->prepare("SELECT email, ip_address FROM staffs WHERE id = :staff_id");
        $stmt->bindParam(':staff_id', $request['staff_id']);
        $stmt->execute();
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($staff) {
            // Use ipgeolocation.io API to fetch staff's geolocation
            $ipgeolocation_api_key = '010ab7f877734057a356f48222bcee3c'; 
            $staff_ip = $staff['ip_address'];
            $ipgeolocation_url = "https://api.ipgeolocation.io/ipgeo?apiKey=$ipgeolocation_api_key&ip=$staff_ip";

            // Fetch geolocation data for staff
            $geo_data = file_get_contents($ipgeolocation_url);
            $geo_data = json_decode($geo_data, true);

            if ($geo_data && isset($geo_data['latitude']) && isset($geo_data['longitude'])) {
                $staff_lat = $geo_data['latitude'];
                $staff_lon = $geo_data['longitude'];

                // Fetch all technicians and their ip addresses from the database
                $stmt = $conn->prepare("SELECT id, ip_address FROM technicians");
                $stmt->execute();
                $technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($technicians) {
                    $closestTechnician = null;
                    $minDistance = PHP_INT_MAX;

                    // Calculate distance and find closest technician
                    foreach ($technicians as $technician) {
                        // Use ipgeolocation.io API to fetch technician's geolocation
                        $technician_ip = $technician['ip_address'];
                        $ipgeolocation_url = "https://api.ipgeolocation.io/ipgeo?apiKey=$ipgeolocation_api_key&ip=$technician_ip";

                        // Fetch geolocation data for technician
                        $geo_data = file_get_contents($ipgeolocation_url);
                        $geo_data = json_decode($geo_data, true);

                        if ($geo_data && isset($geo_data['latitude']) && isset($geo_data['longitude'])) {
                            $technician_lat = $geo_data['latitude'];
                            $technician_lon = $geo_data['longitude'];

                            // Calculate distance
                            $distance = calculateDistance($staff_lat, $staff_lon, $technician_lat, $technician_lon);

                            // Determine closest technician
                            if ($distance < $minDistance) {
                                $minDistance = $distance;
                                $closestTechnician = $technician;
                            }
                        }
                    }

                    if ($closestTechnician) {
                        // Insert into request_recieved table
                        $stmt = $conn->prepare("
                            INSERT INTO request_recieved (technician_id, organisation_id, staff_email, request_id, recieved_on)
                            VALUES (:technician_id, :organisation_id, :staff_email, :request_id, :recieved_on)
                        ");
                        $stmt->bindParam(':technician_id', $closestTechnician['id']);
                        $stmt->bindParam(':organisation_id', $loggedInUserId);
                        $stmt->bindParam(':staff_email', $staff['email']);
                        $stmt->bindParam(':request_id', $requestId);
                        $stmt->bindParam(':recieved_on', $request['created_at']);
                        $stmt->execute();

                        $message = "Technician assigned successfully.";
                    } else {
                        $message = "No technicians available.";
                    }
                } else {
                    $message = "No technicians found.";
                }
            } else {
                $message = "Failed to fetch staff's geolocation data.";
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
