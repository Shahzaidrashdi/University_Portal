<?php
require_once 'config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

// Get all courses
try {
    $courses = $pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error fetching courses: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color:rgb(147, 184, 220);
        }
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(236, 178, 178, 0.75);
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .main-content {
            padding: 20px;
        }
        .course-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .course-card:hover {
            transform: translateY(-5px);
        }
        .contact-info i {
            width: 30px;
            text-align: center;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-dark">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4>Student Portal</h4>
                        <p>Welcome</p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="apply.php">
                            <i class="bi bi-pencil-square me-2"></i>
                                Apply for Admission
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="courses.php">
                            <i class="bi bi-book me-2"></i>
                                View Courses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="university.php">
                            <i class="bi bi-building me-2"></i>
                                University Info
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Available Courses</h1>
                </div>
                
                <div class="row">
                    <?php foreach ($courses as $course): ?>
                        <div class="col-md-4">
                            <div class="card course-card">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($course['name']); ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($course['department']); ?></h6>
                                    <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                                    <ul class="list-group list-group-flush mb-3">
                                        <li class="list-group-item"><strong>Duration:</strong> <?php echo htmlspecialchars($course['duration']); ?></li>
                                        <li class="list-group-item"><strong>Fees:</strong> $<?php echo number_format($course['fees'], 2); ?></li>
                                    </ul>
                                    <a href="apply.php" class="btn btn-primary">Apply Now</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
