<?php
require_once '../../config/database.php';
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Project/pages/auth/login.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $society = mysqli_real_escape_string($conn, $_POST['society']);

    // Check if username or email already exists
    $check_sql = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Username or email already exists!";
    } else {
        // Insert new user
        $sql = "INSERT INTO users (username, email, password, full_name, role, department, society) 
                VALUES ('$username', '$email', '$password', '$full_name', '$role', '$department', '$society')";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: manage-users.php");
            exit;
        } else {
            $error = "Error creating user: " . mysqli_error($conn);
        }
    }
}

// Fetch departments and societies for dropdowns
$departments_sql = "SELECT * FROM departments ORDER BY name";
$departments = mysqli_query($conn, $departments_sql);

$societies_sql = "SELECT * FROM societies ORDER BY name";
$societies = mysqli_query($conn, $societies_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/assets/css/style.css">
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <div class="container py-4">
        <h1 class="mb-4">Add New User</h1>
        
        <?php include 'admin-header.php'; ?>

        <div class="card">
            <div class="card-body">
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="student">Student</option>
                                <option value="representative">Representative</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department">
                                <option value="">Select Department</option>
                                <?php while($dept = mysqli_fetch_assoc($departments)): ?>
                                    <option value="<?php echo htmlspecialchars($dept['name']); ?>">
                                        <?php echo htmlspecialchars($dept['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="society" class="form-label">Society</label>
                            <select class="form-select" id="society" name="society">
                                <option value="">Select Society</option>
                                <?php while($soc = mysqli_fetch_assoc($societies)): ?>
                                    <option value="<?php echo htmlspecialchars($soc['name']); ?>">
                                        <?php echo htmlspecialchars($soc['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="manage-users.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/Project/assets/js/main.js"></script>
</body>
</html> 