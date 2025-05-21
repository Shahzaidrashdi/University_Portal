<?php
require_once 'config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

// Get student information
try {
    $stmt = $pdo->prepare("SELECT first_name, last_name, email FROM students WHERE id = ?");
    $stmt->execute([$_SESSION['student_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get student applications
    $appStmt = $pdo->prepare("SELECT a.id, c.name, a.status, a.application_date 
                             FROM applications a 
                             JOIN courses c ON a.course_id = c.id 
                             WHERE a.student_id = ?");
    $appStmt->execute([$_SESSION['student_id']]);
    $applications = $appStmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

// Get all courses
$courses = $pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);

// Get university info
$university = $pdo->query("SELECT * FROM university_info LIMIT 1")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color:rgb(147, 184, 220);
        }
        .sidebar {
            min-height: 100vh;
            background-color:rgb(55, 62, 69);
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
        .card {
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .status-pending {
            color: #ffc107;
        }
        .status-approved {
            color: #28a745;
        }
        .status-rejected {
            color: #dc3545;
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
                        <p>Welcome, <?php echo htmlspecialchars($student['first_name']); ?></p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
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
                            <a class="nav-link" href="courses.php">
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
                    <h1 class="h2">Dashboard</h1>
                </div>
                
                <!-- Student Info -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Your Information</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>University Contact</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($university['contact_email']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($university['contact_phone']); ?></p>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($university['address']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Applications -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Your Applications</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($applications)): ?>
                            <p>You haven't applied to any courses yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th>Application Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($applications as $app): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($app['name']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($app['application_date'])); ?></td>
                                                <td class="status-<?php echo htmlspecialchars($app['status']); ?>">
                                                    <?php echo ucfirst(htmlspecialchars($app['status'])); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Available Courses -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Available Courses</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($courses as $course): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($course['name']); ?></h5>
                                            <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($course['department']); ?></h6>
                                            <p class="card-text"><?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?>...</p>
                                            <p class="card-text"><small class="text-muted">Duration: <?php echo htmlspecialchars($course['duration']); ?></small></p>
                                            <p class="card-text"><small class="text-muted">Fees: $<?php echo number_format($course['fees'], 2); ?></small></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
