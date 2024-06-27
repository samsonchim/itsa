<?php
session_start();

if (!isset($_SESSION['id'])) {
    http_response_code(403); 
    die("Unauthorized access");
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "itsa";
$systemsFolderPath = "../systems/"; 

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['id'])) {
        $email = filter_var($_GET['id'], FILTER_SANITIZE_EMAIL);

        $stmt = $conn->prepare("SELECT name FROM staffs WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($staff) {
            $staff_name = $staff['name']; 

            $fileName = $email . ".json";
            $filePath = $systemsFolderPath . $fileName;

            // Check if JSON file exists
            if (file_exists($filePath)) {
                // Read JSON file
                $jsonContent = file_get_contents($filePath);
                $jsonData = json_decode($jsonContent, true);

                // Extract necessary system information
                $osInfo = isset($jsonData['OS Info']) ? $jsonData['OS Info'] : [];
                $cpuInfo = isset($jsonData['CPU Info']) ? $jsonData['CPU Info'] : [];
                $memoryInfo = isset($jsonData['Memory Info']) ? $jsonData['Memory Info'] : [];
                $diskInfo = isset($jsonData['Disk Info']) ? $jsonData['Disk Info'] : [];
                $uptime = isset($jsonData['Uptime']) ? $jsonData['Uptime'] : "N/A";
                $batteryInfo = isset($jsonData['Battery Info']) ? $jsonData['Battery Info'] : [];
                $publicIP = isset($jsonData['Public IP']) ? $jsonData['Public IP'] : "N/A";
                $healthStatus = isset($jsonData['Health Status']) ? $jsonData['Health Status'] : [];

                // Function to format uptime
                function formatUptime($uptime)
                {
                    if ($uptime !== "N/A") {
                        $uptimeParts = explode(':', $uptime);
                        $hours = (int)$uptimeParts[0];
                        $minutes = (int)$uptimeParts[1];

                        $formattedUptime = '';

                        if ($hours > 0) {
                            $formattedUptime .= $hours . ' hour' . ($hours > 1 ? 's' : '');
                        }

                        if ($minutes > 0) {
                            $formattedUptime .= ($formattedUptime ? ', ' : '') . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
                        }

                        return $formattedUptime;
                    }

                    return "N/A";
                }

                $formattedUptime = formatUptime($uptime);
                



   




   




$loggedInUserId = $_SESSION['id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "itsa";

$organisationName = "";
$recordCount = 0;
$staffs = []; // Initialize an empty array for staffs

try {
    // Establishing a connection to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch organisation name
    $stmt = $conn->prepare("SELECT organisation_name FROM organisations WHERE id = :id");
    $stmt->bindParam(':id', $loggedInUserId);
    $stmt->execute();
    $organisationRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($organisationRow) {
        $organisationName = $organisationRow['organisation_name'];

        // Fetch record count of staffs
        $stmt = $conn->prepare("SELECT COUNT(*) AS record_count FROM staffs WHERE organisation_id = :id");
        $stmt->bindParam(':id', $loggedInUserId);
        $stmt->execute();
        $countRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($countRow) {
            $recordCount = $countRow['record_count'];
        }

        // Fetch staff details
        $stmt = $conn->prepare("SELECT id, name, email, system_id, visible_password FROM staffs WHERE organisation_id = :organisation_id");
        $stmt->bindParam(':organisation_id', $loggedInUserId);
        $stmt->execute();
        $staffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$conn = null; 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo $organisationName ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/logo.png" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Roboto:wght@500;700&display=swap" rel="stylesheet"> 
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">


        <!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-secondary navbar-dark">
                <a href="index.html" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary"><img class="logo-dashboard" src="img/logo.png" alt=""></i></h3>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                  
                    <div class="ms-3">
                        <h6 class="mb-0"><?php echo $organisationName?></h6>
                        <span>Admin</span>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="admin_dashboard.php" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                    <!--<div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-laptop me-2"></i>Elements</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="button.html" class="dropdown-item">Buttons</a>
                            <a href="typography.html" class="dropdown-item">Typography</a>
                            <a href="element.html" class="dropdown-item">Other Elements</a>
                        </div>
                    </div> -->
                    <a href="staffs.php" class="nav-item nav-link"><i class="fa fa-th me-2"></i>Staffs</a>
                    <a href="technicians.php" class="nav-item nav-link"><i class="fa fa-keyboard me-2"></i>Technicians</a>
                    <a href="plans.php" class="nav-item nav-link"><i class="fa fa-table me-2"></i>Plans</a>
                    <a href="help.html" class="nav-item nav-link"><i class="fa fa-question-circle me-2"></i>Help and Support</a>
                    <a href="logout.php" class="nav-item nav-link"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>

            </nav>
        </div>
        <!-- Sidebar End -->


        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-secondary navbar-dark sticky-top px-4 py-0">
                <a href="index.html" class="navbar-brand d-flex d-lg-none me-4">
                </a>
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="bars fa fa-bars"></i>
                   
                </a>
                <form class="d-none d-md-flex ms-4">
                    <br>
                    <a href="plans.html"><div class="alert alert-warning" role="alert">
                       You are Enjoying 7-day free trial of Business Packages 
                    </div></a>
                </form>
                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-envelope me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex">Message</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-secondary border-0 rounded-0 rounded-bottom m-0">
                            <a href="#" class="dropdown-item">
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                                    <div class="ms-2">
                                        <h6 class="fw-normal mb-0">Jhon send you a message</h6>
                                        <small>15 minutes ago</small>
                                    </div>
                                </div>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item">
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                                    <div class="ms-2">
                                        <h6 class="fw-normal mb-0">Jhon send you a message</h6>
                                        <small>15 minutes ago</small>
                                    </div>
                                </div>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item">
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                                    <div class="ms-2">
                                        <h6 class="fw-normal mb-0">Jhon send you a message</h6>
                                        <small>15 minutes ago</small>
                                    </div>
                                </div>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item text-center">See all message</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-bell me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex">Notificatin</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-secondary border-0 rounded-0 rounded-bottom m-0">
                            <a href="#" class="dropdown-item">
                                <h6 class="fw-normal mb-0">Profile updated</h6>
                                <small>15 minutes ago</small>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item">
                                <h6 class="fw-normal mb-0">New user added</h6>
                                <small>15 minutes ago</small>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item">
                                <h6 class="fw-normal mb-0">Password changed</h6>
                                <small>15 minutes ago</small>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item text-center">See all notifications</a>
                        </div>
                    </div>
                  
                </div>
            </nav>

            <div class="container-fluid pt-4 px-4">
    <div class="bg-secondary text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0"><?php echo htmlspecialchars($staff_name); ?>'s System Details</h6>
        </div>
       
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
            <tbody>
                                <tr>
                                    <td><strong>Operating System</strong></td>
                                    <td><?php echo isset($osInfo['Operating System']) ? $osInfo['Operating System'] : "N/A"; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>OS Version</strong></td>
                                    <td><?php echo isset($osInfo['OS Version']) ? $osInfo['OS Version'] : "N/A"; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Architecture</strong></td>
                                    <td><?php echo isset($osInfo['Architecture']) ? $osInfo['Architecture'] : "N/A"; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Logical CPUs</strong></td>
                                    <td><?php echo isset($cpuInfo['Total Logical CPUs']) ? $cpuInfo['Total Logical CPUs'] : "N/A"; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>CPU Frequency (MHz)</strong></td>
                                    <td><?php echo isset($cpuInfo['Current CPU Frequency (MHz)']) ? $cpuInfo['Current CPU Frequency (MHz)'] : "N/A"; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Memory Usage (%)</strong></td>
                                    <td><?php echo isset($memoryInfo['Memory Usage (%)']) ? $memoryInfo['Memory Usage (%)'] : "N/A"; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Memory (GB)</strong></td>
                                    <td><?php echo isset($memoryInfo['Total Memory (GB)']) ? $memoryInfo['Total Memory (GB)'] : "N/A"; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Free Memory (GB)</strong></td>
                                    <td><?php echo isset($memoryInfo['Free Memory (GB)']) ? $memoryInfo['Free Memory (GB)'] : "N/A"; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Disk Size (GB)</strong></td>
                                    <td><?php echo isset($diskInfo[0]['Total Size (GB)']) ? $diskInfo[0]['Total Size (GB)'] : "N/A"; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Used Disk Size (GB)</strong></td>
                                    <td><?php echo isset($diskInfo[0]['Used Size (GB)']) ? $diskInfo[0]['Used Size (GB)'] : "N/A"; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Free Disk Size (GB)</strong></td>
                                    <td><?php echo isset($diskInfo[0]['Free Size (GB)']) ? $diskInfo[0]['Free Size (GB)'] : "N/A"; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Public IP</strong></td>
                                    <td><?php echo $publicIP; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Uptime</strong></td>
                                    <td><?php echo $formattedUptime; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Battery Health</strong></td>
                                    <td><?php echo isset($batteryInfo['Battery Percent (%)']) ? "Battery level: " . $batteryInfo['Battery Percent (%)'] . "%" : "N/A"; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>CPU Health</strong></td>
                                    <td><?php echo isset($healthStatus['CPU Health'][0]) ? $healthStatus['CPU Health'][0] : "N/A"; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Memory Health</strong></td>
                                    <td><?php echo isset($healthStatus['Memory Health'][0]) ? $healthStatus['Memory Health'][0] : "N/A"; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Disk Health</strong></td>
                                    <td><?php echo isset($healthStatus['Disk Health'][0]) ? $healthStatus['Disk Health'][0] : "N/A"; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Process Health</strong></td>
                                    <td><?php echo !empty($healthStatus['Process Health']) ? implode("<br>", $healthStatus['Process Health']) : "No processes detected"; ?></td>
                                </tr>

                                <tr>
                                   
                                    <td><a href="<?php echo $filePath; ?>" download="<?php echo $filePath; ?>">Download Full Info</a></td>
                                </tr>
                            </tbody>
            </table>
        </div>
    </div>
</div>



        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var elements = document.querySelectorAll('*:contains("Health: OK")');
            elements.forEach(function(element) {
                element.innerHTML = element.innerHTML.replace(/Health: OK/g, '<span style="color: red;">Health: OK</span>');
            });
        });

    </script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>


                <?php
                exit; 
            } else {
                echo "JSON file not found for the specified email.";
            }
        } else {
            echo "Staff not found for the specified email.";
        }
    } else {
        echo "Email parameter (id) is missing.";
    }

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo "Connection failed: " . $e->getMessage();
}
?>
