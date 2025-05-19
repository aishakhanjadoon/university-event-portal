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
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    // Check if department already exists
    $check_sql = "SELECT * FROM departments WHERE name = '$name'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Department with this name already exists!";
    } else {
        // Insert new department
        $sql = "INSERT INTO departments (name, description) VALUES ('$name', '$description')";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: manage-departments.php");
            exit;
        } else {
            $error = "Error creating department: " . mysqli_error($conn);
        }
    }
}

// Fetch all departments
$departments_sql = "SELECT * FROM departments ORDER BY name";
$departments = mysqli_query($conn, $departments_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/assets/css/style.css">
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <div class="container py-4">
        <h1 class="mb-4">Manage Departments</h1>
        
        <?php include 'admin-header.php'; ?>

        <div class="row">
            <!-- Add New Department Form -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Department</h5>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Department Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Department</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Departments List -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">All Departments</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($department = mysqli_fetch_assoc($departments)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($department['name']); ?></td>
                                        <td><?php echo htmlspecialchars($department['description']); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="edit-department.php?id=<?php echo $department['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <a href="delete-department.php?id=<?php echo $department['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Are you sure you want to delete this department?')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/Project/assets/js/main.js"></script>
</body>
</html> 