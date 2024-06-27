<?php
session_start();

if (!isset($_SESSION['id'])) {
    http_response_code(403); 
    die("Unauthorized access");
}

$loggedInUserId = $_SESSION['id'];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "itsa";
$systemsFolderPath = "../systems/";

try {
    // Establishing a connection to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['request_id'])) {
        // Collect the request_id and sanitize it
        $request_id = filter_var($_GET['request_id'], FILTER_SANITIZE_NUMBER_INT);

        // Prepare and execute the query to fetch the email from request_recieved table
        $stmt = $conn->prepare("SELECT staff_email, organisation_id FROM request_recieved WHERE request_id = :request_id");
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $stmt->execute();
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($request) {
            $email = $request['staff_email']; 
            $organisation_id = $request['organisation_id'];

            // Prepare and execute the query to fetch the staff details using the email
            $stmt = $conn->prepare("SELECT name FROM staffs WHERE email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
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

                    // Use $osInfo, $cpuInfo, $memoryInfo, $diskInfo, $formattedUptime, $batteryInfo, $publicIP, and $healthStatus as needed
                } else {
                    // Handle file not found error
                    echo "File not found.";
                }
            } else {
                // Handle case where no staff found with the provided email
                echo "No staff found with the provided email.";
            }
        } else {
            // Handle case where no request found with the provided request_id
            echo "No request found with the provided request_id.";
        }
    } else {
        // Handle case where request_id is not set in the URL
        echo "No request_id provided.";
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
    <title>Technician Dashboard</title>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
                        <span>Technician</span>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="technician_dashboard.php" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                   
                    <a href="download" class="nav-item nav-link"><i class="fa fa-download me-2"></i>My Ongoing Maintenance</a>
                    <a href="chart.html" class="nav-item nav-link"><i class="fa fa-question-circle me-2"></i>Completed Mainternance</a>
                    <a href="chart.html" class="nav-item nav-link"><i class="fa fa-question-circle me-2"></i>Organisations</a>
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

        <div class="d-flex mb-3">
        <form action="start_maintenance.php" method="POST" class="w-100 d-flex custom-form">
        <input class="form-control bg-transparent" type="text" name="note" id="note" placeholder="Add a note of what you will be working on!" required>
        <input type="hidden" id="request_id" name="request_id" value="<?php echo $request_id; ?>">
        <input type="hidden" id="technician_id" name="technician_id" value="<?php echo $loggedInUserId; ?>">
        <input type="hidden" id="organisation_id" name="organisation_id" value="<?php echo $organisation_id; ?>">
        <input type="hidden" id="staff_email" name="staff_email" value="<?php echo $email; ?>">
        <button type="submit" class="btn btn-primary ms-3">Start Maintenance</button>
    </form>


        </div>

        <style>
             .custom-form .form-control {
            color: white;
        }

        .custom-form .form-control::placeholder {
            color: grey; 
        }

        .custom-form .form-control {
            background-color: transparent; 
        }

       
        </style>
       
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
            <tbody>
                 <tr>
                                    <td><strong>Operating System</strong></td>
                                    <td><?php echo isset($osInfo['Operating System']) ? $osInfo['Operating System'] : "N/A"; ?></td>
                                </tr>
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
                                    <td><?php echo $formattedUptime; ?> ago</td>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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


