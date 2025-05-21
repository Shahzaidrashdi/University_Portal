<?php
require_once 'config.php';

$errors = [];
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize and validate inputs
    $firstName = trim(filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING));
    $lastName = trim(filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $nic = trim($_POST['cnic']); // CNIC/NIC needs custom validation
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
    $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING));

    // Validation
    if (empty($firstName)) $errors[] = "First name is required.";
    if (empty($lastName)) $errors[] = "Last name is required.";
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    // CNIC/NIC validation (adjust based on your country's format)
    if (empty($nic)) {
        $errors[] = "CNIC/NIC is required.";
    } elseif (!preg_match('/^[0-9]{5}-[0-9]{7}-[0-9]{1}$/', $nic)) {
        $errors[] = "Invalid CNIC format. Please use XXXXX-XXXXXXX-X format.";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain uppercase, lowercase letters and numbers.";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }
    
    // Phone validation
    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $errors[] = "Invalid phone number format.";
    }

    if (empty($errors)) {
        try {
            // Check if email or NIC already exists
            $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ? OR cnic = ?");
            $stmt->execute([$email, $nic]);
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "Email or CNIC already registered.";
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert student
                $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, email, cnic, password, phone, address) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$firstName, $lastName, $email, $nic, $hashedPassword, $phone, $address]);
                
                $success = "Registration successful! You can now login.";
                $_POST = []; // Clear form
                
                // Send confirmation email (optional)
                // $this->sendConfirmationEmail($email, $firstName);
            }
        } catch(PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $errors[] = "A system error occurred. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color:rgb(147, 184, 220);
        }
        .registration-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="registration-container">
            <h2 class="text-center mb-4">Student Registration</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php elseif ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="register.php">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" 
                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" 
                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="cnic" class="form-label">Cnic or B form number</label>
                    <input type="cnic" class="form-control" id="cnic" name="cnic" 
                           value="<?php echo htmlspecialchars($_POST['cnic'] ?? ''); ?>" required>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" class="form-control" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Register</button>
                
                <div class="mt-3 text-center">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
