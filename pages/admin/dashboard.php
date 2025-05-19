<?php
require_once '../../config/database.php';
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Project/pages/auth/login.php");
    exit;
}

// Fetch statistics
$stats = [
    'total_users' => 0,
    'total_events' => 0,
    'total_societies' => 0,
    'total_departments' => 0
];

// Get total users
$sql = "SELECT COUNT(*) as count FROM users WHERE role != 'admin'";
$result = mysqli_query($conn, $sql);
if($row = mysqli_fetch_assoc($result)) {
    $stats['total_users'] = $row['count'];
}

// Get total events
$sql = "SELECT COUNT(*) as count FROM events";
$result = mysqli_query($conn, $sql);
if($row = mysqli_fetch_assoc($result)) {
    $stats['total_events'] = $row['count'];
}

// Get total societies
$sql = "SELECT COUNT(*) as count FROM societies";
$result = mysqli_query($conn, $sql);
if($row = mysqli_fetch_assoc($result)) {
    $stats['total_societies'] = $row['count'];
}

// Get total departments
$sql = "SELECT COUNT(*) as count FROM departments";
$result = mysqli_query($conn, $sql);
if($row = mysqli_fetch_assoc($result)) {
    $stats['total_departments'] = $row['count'];
}

// Fetch recent events
$recent_events_sql = "SELECT e.*, u.username as created_by_username 
                     FROM events e 
                     LEFT JOIN users u ON e.created_by = u.id 
                     ORDER BY e.created_at DESC LIMIT 5";
$recent_events = mysqli_query($conn, $recent_events_sql);

// Fetch recent users
$recent_users_sql = "SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC LIMIT 5";
$recent_users = mysqli_query($conn, $recent_users_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - University Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/assets/css/style.css">
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <div class="container py-4">
        <h1 class="mb-4">Admin Dashboard</h1>
        
        <?php include 'admin-header.php'; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <h2 class="mb-0"><?php echo $stats['total_users']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Events</h5>
                        <h2 class="mb-0"><?php echo $stats['total_events']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Societies</h5>
                        <h2 class="mb-0"><?php echo $stats['total_societies']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Departments</h5>
                        <h2 class="mb-0"><?php echo $stats['total_departments']; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Events -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Events</h5>
                        <a href="manage-events.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Created By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($event = mysqli_fetch_assoc($recent_events)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($event['title']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($event['created_by_username']); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Users</h5>
                        <a href="manage-users.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Department</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($user = mysqli_fetch_assoc($recent_users)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                                        <td><?php echo htmlspecialchars($user['department']); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="add-user.php" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-person-plus"></i> Add New User
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="add-event.php" class="btn btn-outline-success w-100">
                                    <i class="bi bi-calendar-plus"></i> Add New Event
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="manage-societies.php" class="btn btn-outline-info w-100">
                                    <i class="bi bi-people"></i> Manage Societies
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="manage-departments.php" class="btn btn-outline-warning w-100">
                                    <i class="bi bi-building"></i> Manage Departments
                                </a>
                            </div>
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