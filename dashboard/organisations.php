<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "itsa";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['id'])) {
    die("You need to be logged in to view this page.");
}

$loggedInUserId = $_SESSION['id'];
$technical_id = $_SESSION['id'];

// Fetch records from request_recieved table
$sqlReceived = "SELECT request_id FROM request_recieved WHERE technician_id = ?";
$stmtReceived = $conn->prepare($sqlReceived);
$stmtReceived->bind_param("i", $technical_id);
$stmtReceived->execute();
$resultReceived = $stmtReceived->get_result();
$requestIds = [];

while ($row = $resultReceived->fetch_assoc()) {
    $requestIds[] = $row['request_id'];
}

if (empty($requestIds)) {
    echo "No requests found for this technician.";
    exit;
}

$requestDetails = [];

/* Function to get organisation name by ID */
function getOrganisationName($conn, $organisation_id) {
    $sql = "SELECT organisation_name FROM organisations WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $organisation_id);
    $stmt->execute();
    $stmt->bind_result($organisation_name);
    $stmt->fetch();
    $stmt->close();
    return $organisation_name;
}

// Function to get location by IP using the API
function getLocationByIP($ip) {
    $apiKey = "010ab7f877734057a356f48222bcee3c";
    $url = "http://api.ipgeolocation.io/ipgeo?apiKey={$apiKey}&ip={$ip}";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    return $data ? "{$data['city']}, {$data['country_name']}" : "Unknown location";
}

foreach ($requestIds as $requestId) {
    // Fetch records from request_sent table
    $sqlSent = "SELECT staff_id, staff_ip, organisation_id, subject_issue, description, notice_date FROM request_sent WHERE id = ?";
    $stmtSent = $conn->prepare($sqlSent);
    $stmtSent->bind_param("i", $requestId);
    $stmtSent->execute();
    $resultSent = $stmtSent->get_result();

    while ($row = $resultSent->fetch_assoc()) {
        $row['request_id'] = $requestId;
        $row['organisation_name'] = getOrganisationName($conn, $row['organisation_id']);
        $row['location'] = getLocationByIP($row['staff_ip']);
        $requestDetails[] = $row;
    }
}

$stmtReceived->close();
$conn->close();

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
                       
                        <span><?php echo $techName?> ~ Technician</span>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                <a href="technician_dashboard.php" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>

                <a href="ongoing_maintenance.php" class="nav-item nav-link"><i class="fa fa-tools me-2"></i>My Ongoing Maintenance</a>

                <a href="completed_maintenance.php" class="nav-item nav-link"><i class="fa fa-check-circle me-2"></i>Completed Maintenance</a>

                <a href="organisations.php" class="nav-item nav-link"><i class="fa fa-building me-2"></i>Organisations</a>

                <a href="logout.php" class="nav-item nav-link"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </nav>
        </div>

        <div class="content">
            <!-- Navbar Start -->
            <div class="container-fluid pt-4 px-4">
    <div class="bg-secondary text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0">Issues: <span>Here some Issues sent to you, accept anyone and starting working on it.</span></h6>
        </div>
        <div class="d-flex mb-3">
           
        </div>
        <div class="table-responsive">
        <table class="table text-start align-middle table-bordered table-hover mb-0">
        <thead>
            <tr class="text-white">
                <th scope="col"><input class="form-check-input" type="checkbox"></th>
                <th scope="col">Subject</th>
                <th scope="col">Description</th>
                <th scope="col">Organisation</th>
                <th scope="col">Staff name & Address</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
        <tbody>
            <?php foreach ($requestDetails as $detail): ?>
                <tr>
                    <td><input class="form-check-input" type="checkbox"></td>
                    <td><?php echo htmlspecialchars($detail['subject_issue']); ?></td>
                    <td><?php echo htmlspecialchars($detail['description']); ?></td>
                    <td><?php echo htmlspecialchars($detail['organisation_name']); ?></td>
                    <td><?php echo htmlspecialchars($detail['location']); ?></td>
                    <td>
                    <a href="pc_details.php?request_id=<?php echo $detail['request_id']; ?>">
                        <button class="btn btn-sm btn-primary">Check PC</button>
                    </a>
                </td>

                    </tr>
                </tr>
            <?php endforeach; ?>
        </tbody>
           
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
        $('#maintenanceModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var requestId = button.data('request-id'); // Extract info from data-* attributes
            var organisationId = button.data('organisation-id');
            var staffId = button.data('staff-id');

            // Update the modal's content.
            var modal = $(this);
            modal.find('#requestId').val(requestId);
            modal.find('#organisationId').val(organisationId);
            modal.find('#staffId').val(staffId);
        });

        // Submit the maintenance note form
        $('#maintenanceForm').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'save_maintenance.php',
                data: $(this).serialize(),
                success: function (response) {
                    alert(response);
                    $('#maintenanceModal').modal('hide');
                }
            });
        });
    </script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>