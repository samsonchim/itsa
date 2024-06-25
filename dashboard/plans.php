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

$organisationName = "";
$staffRecordCount = 0;
$requestRecordCount = 0;
$ongoingCount = 0;
$completedCount = 0;
$requests = [];

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

        // Fetch staff record count
        $stmt = $conn->prepare("SELECT COUNT(*) AS record_count FROM staffs WHERE organisation_id = :id");
        $stmt->bindParam(':id', $loggedInUserId);
        $stmt->execute();
        $countRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($countRow) {
            $staffRecordCount = $countRow['record_count'];
        }

        // Fetch request record count
        $stmt = $conn->prepare("SELECT COUNT(*) AS request_count FROM request_sent WHERE organisation_id = :id");
        $stmt->bindParam(':id', $loggedInUserId);
        $stmt->execute();
        $countRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($countRow) {
            $requestRecordCount = $countRow['request_count'];
        }

        // Fetch completed maintenance count
        $stmt = $conn->prepare("SELECT COUNT(*) AS completed_maintenance FROM completed_maintenance WHERE organisation_id = :id");
        $stmt->bindParam(':id', $loggedInUserId);
        $stmt->execute();
        $countRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($countRow) {
            $completedCount = $countRow['completed_maintenance'];
        }

        // Fetch ongoing maintenance count
        $stmt = $conn->prepare("SELECT COUNT(*) AS ongoing_maintenance_count FROM ongoing_maintenance WHERE organisation_id = :id");
        $stmt->bindParam(':id', $loggedInUserId);
        $stmt->execute();
        $countRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($countRow) {
            $ongoingCount = $countRow['ongoing_maintenance_count'];
        }

            // Fetch the latest 5 maintenance requests including their IDs
        $stmt = $conn->prepare("SELECT id, subject_issue, description, created_at FROM request_sent WHERE organisation_id = :id ORDER BY created_at DESC LIMIT 5");
        $stmt->bindParam(':id', $loggedInUserId);
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
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
    <script src="https://cdn.tailwindcss.com"></script>

    <link href="css/bootstrap.min.css" rel="stylesheet">

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
                    <a href="index.html" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
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
                
                <div class="navbar-nav align-items-center ms-auto">
                  
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
        <div class="row g-4">
            <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="h-100 bg-secondary rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0">Student</h6>
                             </div>
                    <div class="bg-[#2a3b50] p-6 rounded-lg space-y-4">
                  <p class="font-bold text-sm">&#8358; 3,500 per Device</p>
                  <ul class="font-light text-sm text-left space-y-2">
                      <li>Deployment Setup is Free</li>
                      <li>Payment Plan: Annual</li>
                      <li>Email support</li>
                      <li>Limited to 3 Devices</li>
                  </ul>
                  <button class="bg-gradient-to-r from-[#65e2d9] to-[#339ecc] w-full rounded-full py-2.5 hover:bg-gradient-to-r hover:from-[#339ecc] hover:to-[#65e2d9]">Choose Plan</button>
              </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="h-100 bg-secondary rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0">Family</h6>
                             </div>
                    <div class="bg-[#2a3b50] p-6 rounded-lg space-y-4">
                  <p class="font-bold text-sm">&#8358; 3,500 per Device</p>
                  <ul class="font-bold text-sm text-left space-y-2">
                      <li>Deployment Setup is Free</li>
                      <li>Payment Plan: Annual</li>
                      <li>Email Support</li>
                      <li>24/7 support</li>
                      <li>Limited to 10 Devices</li>
                  </ul>
                  <button class="bg-gradient-to-r from-[#65e2d9] to-[#339ecc] w-full rounded-full py-2.5 hover:bg-gradient-to-r hover:from-[#339ecc] hover:to-[#65e2d9]">Choose Plan</button>
              </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="h-100 bg-secondary rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0">Coporate</h6>
                             </div>
                    <div class="bg-[#2a3b50] p-6 rounded-lg space-y-4">
                  <p class="font-bold text-sm">&#8358; 3,500 per Device</p>
                  <ul class="font-bold text-sm text-left space-y-2">
                      <li>Deployment Setup starts at &#8358;1,500,000</li>
                      <li>Priority support</li>
                      <li>Customizable solutions</li>
                      <li>24/7 Support</li>
                      <li>Unlimited Devices</li>
                  </ul>
                  <button disabled class="bg-gradient-to-r from-[#65e2d9] to-[#339ecc] w-full rounded-full py-2.5 hover:bg-gradient-to-r hover:from-[#339ecc] hover:to-[#65e2d9]">Current <br>(For Just a Week)</button>
                    </div>
                </div>
            </div>
        </div>
  
            <div class="container-fluid pt-4 px-4">
                <div class="bg-secondary rounded-top p-4">
                    <div class="row">
                        <div class="col-12 col-sm-6 text-center text-sm-start">
                            &copy; <a href="#">ITSA</a>, All Right Reserved. 
                        </div>
                      
                    </div>
                </div>
            </div>
        </div>
      
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

    <style>
        .text-sm{
            color: white !important;
        }
    </style>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>