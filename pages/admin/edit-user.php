<?php
require_once '../../config/database.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Project/pages/auth/login.php");
    exit;
}

// Check if user ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage-users.php");
    exit;
}

$user_id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch user details
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: manage-users.php");
    exit;
}

$user = mysqli_fetch_assoc($result);

// Fetch departments and societies for dropdowns
$departments_sql = "SELECT * FROM departments";
$departments_result = mysqli_query($conn, $departments_sql);
$departments = mysqli_fetch_all($departments_result, MYSQLI_ASSOC);

$societies_sql = "SELECT * FROM societies";
$societies_result = mysqli_query($conn, $societies_sql);
$societies = mysqli_fetch_all($societies_result, MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $society = mysqli_real_escape_string($conn, $_POST['society']);

    // Update user
    $update_sql = "UPDATE users SET 
                   username = '$username', 
                   email = '$email', 
                   full_name = '$full_name', 
                   role = '$role', 
                   department = '$department', 
                   society = '$society'
                   WHERE id = $user_id";

    if (mysqli_query($conn, $update_sql)) {
        header("Location: manage-users.php");
        exit;
    } else {
        die("Error updating user: " . mysqli_error($conn));
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/assets/css/style.css">
</head>

<body>
    <?php include '../../components/header.php'; ?>

    <div class="container py-4">
        <h1 class="mb-4">Edit User</h1>

        <?php include 'admin-header.php'; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username"
                            value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name"
                            value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="user" <?php if ($user['role'] === 'user') echo 'selected'; ?>>User</option>
                            <option value="representative" <?php if ($user['role'] === 'representative') echo 'selected'; ?>>
                                Representative</option>
                            <option value="admin" <?php if ($user['role'] === 'admin') echo 'selected'; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="department" class="form-label">Department</label>
                        <select class="form-select" id="department" name="department">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept['name']); ?>"
                                    <?php if ($user['department'] === $dept['name']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="society" class="form-label">Society</label>
                        <select class="form-select" id="society" name="society">
                            <option value="">Select Society</option>
                            <?php foreach ($societies as $soc): ?>
                                <option value="<?php echo htmlspecialchars($soc['name']); ?>"
                                    <?php if ($user['society'] === $soc['name']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($soc['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="manage-users.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update User</button>
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