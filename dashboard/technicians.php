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

$organisationName = "";
$recordCount = 0;
$staffs = []; 
$technicians = [];

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

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT id, name, email FROM technicians");
    $stmt->execute();
    $technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

                   <!-- <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="far fa-file-alt me-2"></i>Pages</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="signin.html" class="dropdown-item">Sign In</a>
                            <a href="signup.html" class="dropdown-item">Sign Up</a>
                            <a href="404.html" class="dropdown-item">404 Error</a>
                            <a href="blank.html" class="dropdown-item">Blank Page</a>
                        </div>
                    </div>
                </div>-->
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
                    <a href="plans.php"><div class="alert alert-warning" role="alert">
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
            <h6 class="mb-0">Technicians Near You</h6> <br>
        </div>
        <p class="text">Assigning any technician here grants them the privilege to monitor all your staff's CPUs and take necessary actions as needed.</p>
       
        <style>
            .text{
                color: white;
            }
        </style>
     
<div class="table-responsive">
    <table class="table text-start align-middle table-bordered table-hover mb-0">
        <thead>
            <tr class="text-white">
                <th scope="col">Technician's Name</th>
                <th scope="col">Email</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($technicians as $technician): ?>
            <tr>
                <td><?php echo htmlspecialchars($technician['name']); ?></td>
                <td><?php echo htmlspecialchars($technician['email']); ?></td>
                <td>
                <a class="btn btn-sm btn-primary" href="p_assign.php?id=<?php echo $technician['id']; ?>" id="assign-btn-<?php echo $technician['id']; ?>">
                    Assign Technician
                </a>
            </td>
            </tr>
            <?php endforeach; ?>
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
            if (localStorage.getItem('assign-' + button.id)) {
                button.classList.add('disabled-btn');
                button.innerText = 'Technician Assigned';
            }

            button.addEventListener('click', function(event) {
                event.preventDefault();

                if (!localStorage.getItem('assign-' + button.id)) {

                    button.classList.add('disabled-btn');
                    button.innerText = 'Technician Assigned';
                    localStorage.setItem('assign-' + button.id, true);
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