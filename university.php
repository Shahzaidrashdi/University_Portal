<?php
require_once 'config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

// Get student name for welcome message
try {
    $stmt = $pdo->prepare("SELECT first_name FROM students WHERE id = ?");
    $stmt->execute([$_SESSION['student_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error fetching student data: " . $e->getMessage());
}

// Get university info
try {
    $university = $pdo->query("SELECT * FROM university_info LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    // Get university images/gallery if available
    $gallery = [];
    if ($pdo->query("SHOW TABLES LIKE 'university_gallery'")->rowCount() > 0) {
        $gallery = $pdo->query("SELECT image_path, caption FROM university_gallery LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get key statistics if available
    $stats = [];
    if ($pdo->query("SHOW TABLES LIKE 'university_stats'")->rowCount() > 0) {
        $stats = $pdo->query("SELECT * FROM university_stats")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch(PDOException $e) {
    die("Error fetching university info: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Information</title>
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
        .info-section {
            margin-bottom: 30px;
        }
        .gallery-img {
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s;
        }
        .gallery-img:hover {
            transform: scale(1.03);
        }
        .stat-card {
            border-left: 4px solid #0d6efd;
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
                        <p>Welcome, <?php echo htmlspecialchars($student['first_name'] ?? 'Student'); ?></p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="apply.php">
                                <i class="bi bi-pencil-square me-2"></i>Apply for Admission
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="courses.php">
                                <i class="bi bi-book me-2"></i>View Courses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="university.php">
                                <i class="bi bi-building me-2"></i>University Info
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">University Information</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="contact.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-envelope me-1"></i> Contact Us
                        </a>
                    </div>
                </div>
                
                <!-- University Overview -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="info-section">
                            <h3 class="mb-4"><i class="bi bi-building me-2"></i>About Our University</h3>
                            <div class="row">
                                <div class="col-md-8">
                                    <?php if (!empty($university['about'])): ?>
                                        <p><?php echo nl2br(htmlspecialchars($university['about'])); ?></p>
                                    <?php else: ?>
                                        <p class="text-muted">No information available.</p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <?php if (!empty($university['logo_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($university['logo_path']); ?>" 
                                             alt="University Logo" class="img-fluid rounded">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Mission & Vision -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h4><i class="bi bi-bullseye me-2"></i>Our Mission</h4>
                                <?php if (!empty($university['mission'])): ?>
                                    <p><?php echo nl2br(htmlspecialchars($university['mission'])); ?></p>
                                <?php else: ?>
                                    <p class="text-muted">No mission statement available.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h4><i class="bi bi-eye me-2"></i>Our Vision</h4>
                                <?php if (!empty($university['vision'])): ?>
                                    <p><?php echo nl2br(htmlspecialchars($university['vision'])); ?></p>
                                <?php else: ?>
                                    <p class="text-muted">No vision statement available.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Key Statistics -->
                <?php if (!empty($stats)): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="mb-4"><i class="bi bi-bar-chart me-2"></i>Key Statistics</h3>
                        <div class="row">
                            <?php foreach ($stats as $stat): ?>
                            <div class="col-md-3 mb-3">
                                <div class="card stat-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($stat['value']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($stat['label']); ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- University Gallery -->
                <?php if (!empty($gallery)): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="mb-4"><i class="bi bi-images me-2"></i>Campus Gallery</h3>
                        <div class="row g-3">
                            <?php foreach ($gallery as $image): ?>
                            <div class="col-md-4 col-lg-2">
                                <div class="card">
                                    <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                         class="card-img-top gallery-img" 
                                         alt="<?php echo htmlspecialchars($image['caption'] ?? 'University image'); ?>">
                                    <?php if (!empty($image['caption'])): ?>
                                    <div class="card-footer small text-muted">
                                        <?php echo htmlspecialchars($image['caption']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Contact Information -->
                <div class="card">
                    <div class="card-body">
                        <h3 class="mb-4"><i class="bi bi-telephone me-2"></i>Contact Information</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled contact-info">
                                    <?php if (!empty($university['contact_email'])): ?>
                                    <li class="mb-3">
                                        <i class="bi bi-envelope"></i> 
                                        <strong>Email:</strong> 
                                        <a href="mailto:<?php echo htmlspecialchars($university['contact_email']); ?>">
                                            <?php echo htmlspecialchars($university['contact_email']); ?>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($university['contact_phone'])): ?>
                                    <li class="mb-3">
                                        <i class="bi bi-telephone"></i> 
                                        <strong>Phone:</strong> 
                                        <a href="tel:<?php echo htmlspecialchars($university['contact_phone']); ?>">
                                            <?php echo htmlspecialchars($university['contact_phone']); ?>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($university['address'])): ?>
                                    <li class="mb-3">
                                        <i class="bi bi-geo-alt"></i> 
                                        <strong>Address:</strong> 
                                        <?php echo nl2br(htmlspecialchars($university['address'])); ?>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <?php if (!empty($university['map_embed'])): ?>
                                    <div class="ratio ratio-16x9">
                                        <?php echo $university['map_embed']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
