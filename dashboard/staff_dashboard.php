
<?php
session_start(); 

if (!isset($_SESSION['id'])) {
    header("Location: staff_login.html"); 
    exit;
}

$loggedInUserId = $_SESSION['id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "itsa";

$staffName = "";
$organisationName = "";
$staffRecordCount = 0;
$requestRecordCount = 0;
$ongoingCount = 0;
$completedCount = 0;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch staff details
    $stmt = $conn->prepare("SELECT name, organisation_id FROM staffs WHERE id = :id");
    $stmt->bindParam(':id', $loggedInUserId);
    $stmt->execute();
    $staffRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($staffRow) {
        $staffName = $staffRow['name'];
        $organisationId = $staffRow['organisation_id'];

        // Fetch organisation name
        $stmt = $conn->prepare("SELECT organisation_name FROM organisations WHERE id = :organisation_id");
        $stmt->bindParam(':organisation_id', $organisationId);
        $stmt->execute();
        $organisationRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($organisationRow) {
            $organisationName = $organisationRow['organisation_name'];
        }

        // Fetch staff record count
        $stmt = $conn->prepare("SELECT COUNT(*) AS record_count FROM staffs WHERE id = :id");
        $stmt->bindParam(':id', $loggedInUserId);
        $stmt->execute();
        $countRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($countRow) {
            $staffRecordCount = $countRow['record_count'];
        }

        // Fetch request record count
        $stmt = $conn->prepare("SELECT COUNT(*) AS request_count FROM request_sent WHERE staff_id = :id");
        $stmt->bindParam(':id', $loggedInUserId);
        $stmt->execute();
        $countRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($countRow) {
            $requestRecordCount = $countRow['request_count'];
        }

        // Fetch completed maintenance count
        $stmt = $conn->prepare("SELECT COUNT(*) AS completed_maintenance FROM completed_maintenance WHERE id = :id");
        $stmt->bindParam(':id', $loggedInUserId);
        $stmt->execute();
        $countRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($countRow) {
            $completedCount = $countRow['completed_maintenance'];
        }

        // Fetch ongoing maintenance count
        $stmt = $conn->prepare("SELECT COUNT(*) AS ongoing_maintenance_count FROM ongoing_maintenance WHERE id = :id");
        $stmt->bindParam(':id', $loggedInUserId);
        $stmt->execute();
        $countRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($countRow) {
            $ongoingCount = $countRow['ongoing_maintenance_count'];
        }
    }
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

//The CLustered Chart Data was fetched from here.
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the count of requests per month for the logged-in user
    $stmt = $conn->prepare("
        SELECT MONTH(created_at) as month, COUNT(*) as request_count
        FROM request_sent
        WHERE staff_id = :staff_id
        GROUP BY MONTH(created_at)
    ");
    $stmt->bindParam(':staff_id', $loggedInUserId);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data for the chart
    $monthlyData = array_fill(1, 12, 0); 

    foreach ($results as $row) {
        $monthlyData[intval($row['month'])] = $row['request_count'];
    }

    // Convert data to JSON for use in JS
    $jsonData = json_encode($monthlyData);

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}


try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT id, subject_issue FROM request_sent WHERE staff_id = :staff_id ORDER BY created_at DESC LIMIT 8");
    $stmt->bindParam(':staff_id', $loggedInUserId);
    $stmt->execute();

    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
                        <span><?php echo $staffName?>/Staff</span>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="index.html" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                   
                    <a href="download" class="nav-item nav-link"><i class="fa fa-download me-2"></i>Download Driver</a>
                    <a href="chart.html" class="nav-item nav-link"><i class="fa fa-question-circle me-2"></i>Help and Support</a>
                    <a href="logout.php" class="nav-item nav-link"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </nav>
        </div>

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

                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-bell me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex">Notifications</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-secondary border-0 rounded-0 rounded-bottom m-0">
                            <a href="#" class="dropdown-item">
                                <h6 class="fw-normal mb-0">Profile updated</h6>
                                <small>15 minutes ago</small>
                            </a>
                            <hr class="dropdown-divider">
                      </div>
                     </nav>
        
                <div class="container-fluid pt-4 px-4">
                    <div class="row g-4">
                        <div class="col-sm-6 col-xl-3">
                        
                        </div>
                    
                        <div class="container">
        <div class="row justify-content-center">
            <div class="col-sm-6 col-md-4">
                <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-tasks fa-2x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Request Made</p>
                        <h6 class="mb-0"><?php echo $requestRecordCount ?></h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4">
                <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-wrench fa-2x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Ongoing Maintenance(s)</p>
                        <h6 class="mb-0"><?php echo $ongoingCount ?></h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4">
                <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-cogs fa-2x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Completed Maintenance(s)</p>
                        <h6 class="mb-0"><?php echo $completedCount ?></h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-6">
                    <div class="bg-secondary text-center rounded p-4">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                        </div>
                        <canvas id="request_sent"></canvas>
                    </div>
                    </div>
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-secondary text-center rounded p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h6 class="mb-0">Sytem Information</h6>
                            </div>
                           
                                <p>jsj</p>
                           
                        </div>
                    </div>
                </div>
            </div>

          

            <div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-sm-12 col-md-6 col-xl-4">
            <div class="h-100 bg-secondary rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h6 class="mb-0">Report a technical issue</h6>
                                   </div>
                <form action="report_an_issue.php" method="POST">
                    <div class="mb-3">
                        <label for="subjectIssue" class="form-label">Subject Issue</label>
                        <input type="text" class="form-control" id="subjectIssue" name="subjectIssue" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="dayOfNotice" class="form-label">Noticing Date</label>
                        <input type="date" class="form-control" id="dayOfNotice" name="dayOfNotice" required>
                    </div>
                    <button type="submit" class="make-request">
                        <i class="fa fa-envelope me-lg-2"></i>Make a request
                    </button>
                    <style>
                            .make-request{
                                width: 90%;
                                padding: 16px 0px;
                                margin: 17px;
                                border: none;
                                border-radius: 8px;
                                outline: none;
                                letter-spacing: 3px;
                                color: #ffffff;
                                background: linear-gradient(to right, #003f7d, #6aa9d8, #3366cc);
                                cursor: pointer;
                                box-shadow: 0px 10px 40px -12px #0a272952;
                            }
                            
                           </style>
                </form>
                
              
               
            </div>
        </div>
 


                    <div class="col-sm-12 col-md-6 col-xl-4">
                        <div class="h-100 bg-secondary rounded p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h6 class="mb-0">Calender</h6>
                                   </div>
                            <div id="calender"></div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-xl-4">
                    <div class="h-100 bg-secondary rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h6 class="mb-0">Issues Created</h6>
                    </div>

                    <?php foreach ($requests as $request): ?>
                        <div class="d-flex align-items-center border-bottom py-2">
                            <div class="w-100 ms-3">
                                <div class="d-flex w-100 align-items-center justify-content-between">
                                    <span><?php echo htmlspecialchars($request['subject_issue']); ?></span>
                                    <a href="delete_request.php?id=<?php echo $request['id']; ?>">
                                        <button class="btn btn-sm"><i class="fa fa-times"></i></button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                    </div>

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

    <!-- JavaScript Libraries -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('request_sent').getContext('2d');
            const monthlyData = <?php echo $jsonData; ?>;
            const labels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Requests',
                        data: Object.values(monthlyData),
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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