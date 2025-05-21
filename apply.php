<?php
require_once 'config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// Get courses for dropdown
try {
    $courses = $pdo->query("SELECT id, name FROM courses")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error fetching courses: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token. Please try again.";
    } else {
        $courseId = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
        
        if (!$courseId) {
            $error = "Please select a valid course.";
        } else {
            try {
                // Check if already applied
                $stmt = $pdo->prepare("SELECT id FROM applications WHERE student_id = ? AND course_id = ?");
                $stmt->execute([$_SESSION['student_id'], $courseId]);
                
                if ($stmt->rowCount() > 0) {
                    $error = "You have already applied to this course.";
                } else {
                    // Submit application
                    $stmt = $pdo->prepare("INSERT INTO applications (student_id, course_id) VALUES (?, ?)");
                    $stmt->execute([$_SESSION['student_id'], $courseId]);
                    
                    $success = "Application submitted successfully!";
                }
            } catch(PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Get student information for header
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Admission</title>
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
        .application-container {
            max-width: 600px;
            margin: 0 auto;
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
                            <a class="nav-link active" href="apply.php">
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
                    <h1 class="h2">Apply for Admission</h1>
                </div>
                
                <div class="application-container">
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php elseif ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="apply.php">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                
                                <div class="mb-3">
                                    <label for="course_id" class="form-label">Select Course</label>
                                    <select class="form-select" id="course_id" name="course_id" required>
                                        <option value="">-- Select a course --</option>
                                        <?php foreach ($courses as $course): ?>
                                            <option value="<?php echo $course['id']; ?>">
                                                <?php echo htmlspecialchars($course['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">Submit Application</button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
