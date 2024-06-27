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

try {
    // Establishing a connection to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the latest 5 maintenance requests with staff names
    $stmt = $conn->prepare("
        SELECT request_sent.id, request_sent.subject_issue, request_sent.description, request_sent.created_at, 
               request_sent.notice_date, request_sent.staff_id, staffs.name as staff_name
        FROM request_sent
        INNER JOIN staffs ON request_sent.staff_id = staffs.id
        WHERE request_sent.organisation_id = :id
        ORDER BY request_sent.created_at DESC
        LIMIT 5
    ");
    $stmt->bindParam(':id', $loggedInUserId);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                    <a href="staffs.php" class="nav-item nav-link"><i class="fa fa-th me-2"></i>Staffs</a>
                    <a href="technicians.php" class="nav-item nav-link"><i class="fa fa-keyboard me-2"></i>Technicians</a>
                    <a href="plans.php" class="nav-item nav-link"><i class="fa fa-table me-2"></i>Plans</a>
                    <a href="chart.html" class="nav-item nav-link"><i class="fa fa-question-circle me-2"></i>Help and Support</a>
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
                    <div class="alert alert-warning" role="alert">
                       You are Enjoying 7-day free trial of Business Packages
                    </div>
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
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h6 class="mb-0">All Maintenance Request</h6>
            </div>
            <div class="table-responsive">
                <table class="table text-start align-middle table-bordered table-hover mb-0">
                    <thead>
                        <tr class="text-white">
                            <th scope="col"><input class="form-check-input" type="checkbox"></th>
                            <th scope="col">Staff Name</th>
                            <th scope="col">Issue</th>
                            <th scope="col">Description</th>
                            <th scope="col">Noticed on</th>
                            <th scope="col">Reported on</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($requests)): ?>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><input class="form-check-input" type="checkbox"></td>
                                    <td><?php echo htmlspecialchars($request['staff_name']); ?></td>
                                    <td><?php echo htmlspecialchars($request['subject_issue']); ?></td>
                                    <td><?php echo htmlspecialchars($request['description']); ?></td>
                                    <td><?php echo date('F j, Y', strtotime($request['notice_date'])); ?></td>
                                    <td><?php echo date('F j, Y, g:i a', strtotime($request['created_at'])); ?></td>
                                    <td><a class="btn btn-sm btn-primary assign-btn" href="assign_technician.php?id=<?php echo $request['id']; ?>" id="assign-btn-<?php echo $request['id']; ?>">Assign Technician</a></td>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No maintenance requests found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
          
    <style>
        .disabled-btn {
            pointer-events: none;
            background-color: grey;
            border-color: grey;
        }
    </style>

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
        document.querySelectorAll('.assign-btn').forEach(function(button) {
            if (localStorage.getItem('assigned-' + button.id)) {
                button.classList.add('disabled-btn');
                button.innerText = 'Technician Assigned';
            }

            button.addEventListener('click', function(event) {
                event.preventDefault();

                if (!localStorage.getItem('assigned-' + button.id)) {

                    button.classList.add('disabled-btn');
                    button.innerText = 'Technician Assigned';
                    localStorage.setItem('assigned-' + button.id, true);
                    setTimeout(function() {
                        window.location.href = button.href;
                    }, 100);
                } else {
                    window.location.href = button.href;
                }
            });
        });
    });
</script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>