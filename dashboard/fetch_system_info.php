<?php
session_start();

if (!isset($_SESSION['id'])) {
    http_response_code(403); // Forbidden
    die("Unauthorized access");
}

$loggedInUserId = $_SESSION['id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "itsa";
$systemsFolderPath = "../systems/"; // Path to JSON files

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch staff emails for the logged-in organization
    $stmt = $conn->prepare("SELECT email, name FROM staffs WHERE organisation_id = :org_id");
    $stmt->bindParam(':org_id', $loggedInUserId);
    $stmt->execute();
    $staffs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = array();

    foreach ($staffs as $staff) {
        $email = $staff['email'];
        $fileName = $email . ".json";
        $filePath = $systemsFolderPath . $fileName;

        // Check if JSON file exists
        if (file_exists($filePath)) {
            // Read JSON file
            $jsonContent = file_get_contents($filePath);
            $jsonData = json_decode($jsonContent, true);

            // Extract system health information
            $osInfo = isset($jsonData['OS Info']) ? $jsonData['OS Info']['Operating System'] : "N/A";
            $cpuHealth = isset($jsonData['Health Status']['CPU Health'][0]) ? $jsonData['Health Status']['CPU Health'][0] : "N/A";
            $batteryHealth = isset($jsonData['Health Status']['Battery Health'][0]) ? $jsonData['Health Status']['Battery Health'][0] : "N/A";
            $uptime = isset($jsonData['Uptime']) ? $jsonData['Uptime'] : "N/A";

            // Reformat uptime if it is not "N/A"
            if ($uptime !== "N/A") {
                $uptimeParts = explode(':', $uptime);
                $hours = (int)$uptimeParts[0];
                $minutes = (int)$uptimeParts[1];
                $seconds = (int)$uptimeParts[2];

                $formattedUptime = '';

                if ($hours > 0) {
                    $formattedUptime .= $hours . ' hour' . ($hours > 1 ? 's' : '');
                }

                if ($minutes > 0) {
                    $formattedUptime .= ($formattedUptime ? ', ' : '') . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
                }

                $uptime = $formattedUptime;
            }

            // Prepare data to send back
            $rowData = array(
                'staff_name' => $staff['name'],
                'pc_name' => basename($fileName, ".json"),
                'cpu_health' => $cpuHealth,
                'battery_health' => $batteryHealth,
                'uptime' => $uptime,
                'email' => $email,
            );

            $result[] = $rowData;
        }
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($result);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo "Connection failed: " . $e->getMessage();
}
?>
