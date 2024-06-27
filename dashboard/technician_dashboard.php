
<?php
session_start(); 

if (!isset($_SESSION['id'])) {
    header("Location: technician_login.html"); 
    exit;
}

$loggedInUserId = $_SESSION['id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "itsa";

$techName = "";
$organisationName = "";
$techRecordCount = 0;
$requestRecordCount = 0;
$ongoingCount = 0;
$completedCount = 0;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT name, email FROM technicians WHERE id = :id");
    $stmt->bindParam(':id', $loggedInUserId);
    $stmt->execute();
    $techRow = $stmt->fetch(PDO::FETCH_ASSOC);

    $techEmail = $techRow['email'];

    echo '<script>localStorage.setItem("tech_email", "' . $techEmail . '");</script>';

    if ($techRow) {
        $techName = $techRow['name'];
  
       
    }
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo $techName ?></title>
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
                        <span><?php echo $techName?>/tech</span>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="index.html" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                   
                    <a href="download" class="nav-item nav-link"><i class="fa fa-download me-2"></i>My Ongoing Maintenance</a>
                    <a href="chart.html" class="nav-item nav-link"><i class="fa fa-question-circle me-2"></i>Completed Mainternance</a>
                    <a href="chart.html" class="nav-item nav-link"><i class="fa fa-question-circle me-2"></i>Organisations</a>
                    <a href="logout.php" class="nav-item nav-link"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </nav>
        </div>

        <div class="content">
            <!-- Navbar Start -->
            <div class="container-fluid pt-4 px-4">
    <div class="bg-secondary text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0">techs of <?php echo htmlspecialchars($organisationName); ?></h6>
        </div>
        <div class="d-flex mb-3">
            <form method="POST" action="add_tech.php" class="w-100 d-flex">
                <input class="form-control bg-transparent" type="text" name="tech_name" placeholder="tech Name" required> 
                <input class="form-control bg-transparent ms-3" type="email" name="email" placeholder="Email" required>
                <button type="submit" class="btn btn-primary ms-3">Add tech</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
                <thead>
                    <tr class="text-white">
                        <th scope="col"><input class="form-check-input" type="checkbox"></th>
                        <th scope="col">Subject</th>
                        <th scope="col">Description</th>
                        <th scope="col">Organisation</th>
                        <th scope="col">Staff</th>
                        <th scope="col">NOR</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                  
                    <tr>
                        <td><input class="form-check-input" type="checkbox"></td>
                        <td><?php  ?></td>
                        <td><?php ?></td>
                        <td><?php ?></td>
                        <td><?php ?></td>
                        <td><a class="btn btn-sm btn-primary" href="delete_tech.php?id=<?php echo $tech['id']; ?>">Delete tech</a></td>
                    </tr>
                   
                </tbody>
            </table>
        </div>
    </div>
</div>

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

            <!-- Footer End -->
        </div>
        <!-- Content End -->


        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script>
  $(document).ready(function() {
    // Retrieve email from local storage
    var email = localStorage.getItem('tech_email');

    // Check if email exists in local storage
    if (!email) {
        console.error('Email not found in local storage');
        return;
    }

    // Ajax request to fetch system info based on email
    $.ajax({
        url: '../systems/' + encodeURIComponent(email) + '.json', // Construct the URL
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            displayUserInfo(data); // Assuming data directly contains the user info
        },
        error: function(xhr, status, error) {
            console.error('Error fetching system info:', error);
        }
    });

    function displayUserInfo(userInfo) {
        // Update HTML elements with retrieved data
        $('#os-info').text(userInfo['OS Info']['Operating System']);
        $('#cpu-health').text(userInfo['Health Status']['Process Health']);
        $('#memory-health').text(userInfo['Health Status']['Memory Health']);
        $('#battery-health').text(userInfo['Health Status']['Battery Health']);
    }
});


    </script>
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
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1 
                    },
                    title: {
                        display: true,
                        text: 'Number of Day'  
                    }
                }
            }
        }
    });
});

    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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