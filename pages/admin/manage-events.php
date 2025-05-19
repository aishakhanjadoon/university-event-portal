<?php
require_once '../../config/database.php';
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Project/pages/auth/login.php");
    exit;
}

// Fetch all events
$events_sql = "SELECT e.*, u.username as created_by_username 
               FROM events e 
               LEFT JOIN users u ON e.created_by = u.id 
               ORDER BY e.event_date DESC";
$events = mysqli_query($conn, $events_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/assets/css/style.css">
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <div class="container py-4">
        <h1 class="mb-4">Manage Events</h1>
        
        <?php include 'admin-header.php'; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Events</h5>
                <a href="add-event.php" class="btn btn-primary">
                    <i class="bi bi-calendar-plus"></i> Add New Event
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Department</th>
                                <th>Society</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($event = mysqli_fetch_assoc($events)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($event['category'])); ?></td>
                                <td><?php echo htmlspecialchars($event['department']); ?></td>
                                <td><?php echo htmlspecialchars($event['society']); ?></td>
                                <td><?php echo htmlspecialchars($event['created_by_username']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit-event.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a href="delete-event.php?id=<?php echo $event['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this event?')">
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

    <?php include '../../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/Project/assets/js/main.js"></script>
</body>
</html> 