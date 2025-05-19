<?php
require_once '../../config/database.php';
session_start();

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

$error = '';
$success = '';

// Get departments and societies for the form
$departments = [];
$societies = [];

$sql = "SELECT name FROM departments ORDER BY name ASC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $departments[] = $row['name'];
    }
}

$sql = "SELECT name FROM societies ORDER BY name ASC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $societies[] = $row['name'];
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $department = !empty($_POST['department']) ? $_POST['department'] : null;
    $society = !empty($_POST['society']) ? $_POST['society'] : null;
    
    // Validate input
    if(empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $error = "Please fill in all required fields.";
    } elseif($password != $confirm_password) {
        $error = "Passwords do not match.";
    } elseif(strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if username or email already exists
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $username, $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) > 0) {
                $error = "Username or email already exists.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user
                $sql = "INSERT INTO users (username, email, password, full_name, role, department, society) VALUES (?, ?, ?, ?, ?, ?, ?)";
                if($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "sssssss", $username, $email, $hashed_password, $full_name, $role, $department, $society);
                    
                    if(mysqli_stmt_execute($stmt)) {
                        $success = "Registration successful! You can now login.";
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - University Events Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }
        .register-container {
            max-width: 600px;
            width: 90%;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .register-header i {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 1rem;
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .btn-register {
            width: 100%;
            padding: 0.8rem;
            font-size: 1.1rem;
        }
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="register-header">
                <i class="fas fa-user-plus"></i>
                <h2>Create Account</h2>
                <p class="text-muted">Join our university events community</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                            <label for="username">Username</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                            <label for="email">Email</label>
                        </div>
                    </div>
                </div>

                <div class="form-floating">
                    <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Full Name" required>
                    <label for="full_name">Full Name</label>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password">Password</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                            <label for="confirm_password">Confirm Password</label>
                        </div>
                    </div>
                </div>

                <div class="form-floating mb-3">
                    <select class="form-select" id="role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="student">Student</option>
                        <option value="representative">Representative</option>
                    </select>
                    <label for="role">Role</label>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="department" name="department">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="department">Department</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="society" name="society">
                                <option value="">Select Society</option>
                                <?php foreach ($societies as $soc): ?>
                                    <option value="<?php echo htmlspecialchars($soc); ?>"><?php echo htmlspecialchars($soc); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="society">Society</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-register">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>

            <div class="login-link">
                <p class="mb-0">Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html> 