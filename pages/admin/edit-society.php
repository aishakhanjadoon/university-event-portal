<?php
require_once '../../config/database.php';
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Project/pages/auth/login.php");
    exit;
}

// Check if ID is provided
if(!isset($_GET['id'])) {
    header("Location: manage-societies.php");
    exit;
}

$society_id = mysqli_real_escape_string($conn, $_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);

    // Check if society name already exists (excluding current society)
    $check_sql = "SELECT * FROM societies WHERE name = '$name' AND id != $society_id";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Society with this name already exists!";
    } else {
        // Update society
        $sql = "UPDATE societies SET name = '$name', description = '$description', department = '$department' WHERE id = $society_id";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: manage-societies.php");
            exit;
        } else {
            $error = "Error updating society: " . mysqli_error($conn);
        }
    }
}

// Fetch society details
$society_sql = "SELECT * FROM societies WHERE id = $society_id";
$society_result = mysqli_query($conn, $society_sql);

if (mysqli_num_rows($society_result) === 0) {
    header("Location: manage-societies.php");
    exit;
}

$society = mysqli_fetch_assoc($society_result);

// Fetch departments for dropdown
$departments_sql = "SELECT * FROM departments ORDER BY name";
$departments = mysqli_query($conn, $departments_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Society - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/assets/css/style.css">
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <div class="container py-4">
        <h1 class="mb-4">Edit Society</h1>
        
        <?php include 'admin-header.php'; ?>

        <div class="card">
            <div class="card-body">
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="name" class="form-label">Society Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($society['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($society['description']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="department" class="form-label">Department</label>
                        <select class="form-select" id="department" name="department" required>
                            <option value="">Select Department</option>
                            <?php while($dept = mysqli_fetch_assoc($departments)): ?>
                                <option value="<?php echo htmlspecialchars($dept['name']); ?>" 
                                    <?php echo ($dept['name'] === $society['department']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="manage-societies.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Society</button>
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