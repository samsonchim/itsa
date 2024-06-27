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

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

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
                <form class="d-none d-md-flex ms-4">
                    <br>
                    <!--
                    <a href="plans.php"><div class="alert alert-warning" role="alert">
                       You are Enjoying 7-day free trial of Business Packages 
                    </div></a> -->
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
                <div class="row g-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-user-friends fa-2x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Staffs</p>
                                <h6 class="mb-0"><?php echo $staffRecordCount?></h6>
                            </div>
                        </div>
                    </div>
                  
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                        <i class="fa fa-tasks fa-2x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Request Recieved</p>
                                <h6 class="mb-0"><?php echo $requestRecordCount ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                        <i class="fa fa-wrench fa-2x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Ongoing Maintenance(s)</p>
                                <h6 class="mb-0"><?php echo $ongoingCount?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                        <i class="fa fa-cogs fa-2x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Completed Maintenance(s)</p>
                                <h6 class="mb-0"><?php echo $completedCount?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    

            <div class="container-fluid pt-4 px-4">
                <div class="bg-secondary text-center rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h6 class="mb-0">Online Systems</h6>
                        <a href="">Show All</a>
                    </div>
                    <div class="table-responsive">
                     <table id="system-info-table" class="table text-start align-middle table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-white">
                                <th scope="col"><input class="form-check-input" type="checkbox"></th>
                                <th scope="col">Staff Name</th>
                                <th scope="col">PC Name</th>
                                <th scope="col">CPU Health</th>
                                <th scope="col">Battery Health</th>
                                <th scope="col">Uptime</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <!-- Table rows will be dynamically populated by JavaScript -->
                    </tbody>
                    </table>

                    </div>
                </div>
            </div>
        <!--
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-secondary text-center rounded p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h6 class="mb-0">Worldwide Sales</h6>
                                <a href="">Show All</a>
                            </div>
                            <canvas id="worldwide-sales"></canvas>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-secondary text-center rounded p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h6 class="mb-0">Salse & Revenue</h6>
                                <a href="">Show All</a>
                            </div>
                            <canvas id="salse-revenue"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            --->

            <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="h-100 bg-secondary rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0">Maintenance Requests</h6>
                        <a href="all_request.php">Show All</a>
                    </div>
                    <?php if (!empty($requests)): ?>
                    <?php foreach ($requests as $request): ?>
                        <div class="d-flex align-items-center border-bottom py-3">
                            <a href="assign_technician.php?id=<?php echo $request['id']; ?>">
                                <div class="w-100 ms-3">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($request['subject_issue']); ?></h6>
                                    </div>
                                    <small>Received Time: <?php echo date('F j, Y, g:i a', strtotime($request['created_at'])); ?></small>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>All Systems are good and running.</p>
                <?php endif; ?>

                </div>
            </div>
                    <div class="col-sm-12 col-md-6 col-xl-4">
                        <div class="h-100 bg-secondary rounded p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h6 class="mb-0">Calender</h6>
                                <a href="">Show All</a>
                            </div>
                            <div id="calender"></div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-xl-4">
                        <div class="h-100 bg-secondary rounded p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h6 class="mb-0">To Do List</h6>
                                <a href="">Show All</a>
                            </div>
                            <div class="d-flex mb-2">
                                <input class="form-control bg-dark border-0" type="text" placeholder="Enter task">
                                <button type="button" class="btn btn-primary ms-2">Add</button>
                            </div>
                            <div class="d-flex align-items-center border-bottom py-2">
                                <input class="form-check-input m-0" type="checkbox">
                                <div class="w-100 ms-3">
                                    <div class="d-flex w-100 align-items-center justify-content-between">
                                        <span>Short task goes here...</span>
                                        <button class="btn btn-sm"><i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                          
                        
                            <div class="d-flex align-items-center pt-2">
                                <input class="form-check-input m-0" type="checkbox">
                                <div class="w-100 ms-3">
                                    <div class="d-flex w-100 align-items-center justify-content-between">
                                        <span>Short task goes here...</span>
                                        <button class="btn btn-sm"><i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Widgets End -->


            <!-- Footer Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-secondary rounded-top p-4">
                    <div class="row">
                        <div class="col-12 col-sm-6 text-center text-sm-start">
                            &copy; <a href="#">ITSA</a>, All Right Reserved. 
                        </div>
                      
                    </div>
                </div>
            </div>
            <!-- Footer End -->
        </div>
        <!-- Content End -->


        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>
<script>
    $(document).ready(function() {
    // AJAX request to fetch system info
    $.ajax({
        url: 'fetch_system_info.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            // Clear existing table rows
            $('#system-info-table tbody').empty();

            // Populate table with fetched data
            response.forEach(function(data) {
                var row = `<tr>
                               <td><input class="form-check-input" type="checkbox"></td>
                               <td>${data.staff_name}</td>
                               <td>${data.pc_name}</td>
                               <td>${data.cpu_health}</td>
                               <td>${data.battery_health}</td>
                               <td>${data.uptime}</td>
                               <td><a class="btn btn-sm btn-primary" href="detailed_info.php?id=${data.email}">Detailed Info</a></td>
                           </tr>`;
                $('#system-info-table tbody').append(row);
            });
        },
        error: function(xhr, status, error) {
            console.error('Error fetching system info:', error);
        }
    });
});
</script>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>