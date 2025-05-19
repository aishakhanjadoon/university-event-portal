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
    $department = mysqli_real_escape_string($conn, $_POST['department']);

    // Check if society already exists
    $check_sql = "SELECT * FROM societies WHERE name = '$name'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Society with this name already exists!";
    } else {
        // Insert new society
        $sql = "INSERT INTO societies (name, description, department) VALUES ('$name', '$description', '$department')";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: manage-societies.php");
            exit;
        } else {
            $error = "Error creating society: " . mysqli_error($conn);
        }
    }
}

// Fetch all societies
$societies_sql = "SELECT * FROM societies ORDER BY name";
$societies = mysqli_query($conn, $societies_sql);

// Fetch departments for dropdown
$departments_sql = "SELECT * FROM departments ORDER BY name";
$departments = mysqli_query($conn, $departments_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Societies - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/assets/css/style.css">
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <div class="container py-4">
        <h1 class="mb-4">Manage Societies</h1>
        
        <?php include 'admin-header.php'; ?>

        <div class="row">
            <!-- Add New Society Form -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Society</h5>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Society Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" id="department" name="department" required>
                                    <option value="">Select Department</option>
                                    <?php while($dept = mysqli_fetch_assoc($departments)): ?>
                                        <option value="<?php echo htmlspecialchars($dept['name']); ?>">
                                            <?php echo htmlspecialchars($dept['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Society</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Societies List -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">All Societies</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($society = mysqli_fetch_assoc($societies)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($society['name']); ?></td>
                                        <td><?php echo htmlspecialchars($society['department']); ?></td>
                                        <td><?php echo htmlspecialchars($society['description']); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="edit-society.php?id=<?php echo $society['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <a href="delete-society.php?id=<?php echo $society['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Are you sure you want to delete this society?')">
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