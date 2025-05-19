<?php
require_once '../../config/database.php';
session_start();

// Check if user is logged in and is a representative
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'representative') {
    header("Location: /pages/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user = $_SESSION;

// Get user's society and department
$society = isset($user['society']) ? $user['society'] : null;
$department = isset($user['department']) ? $user['department'] : null;

// Build the WHERE clause based on user's role
$where_clause = "WHERE e.created_by = ?";
$params = [$user_id];
$types = "i";

if($user['role'] === 'representative') {
    if($society) {
        $where_clause .= " AND e.society = ?";
        $params[] = $society;
        $types .= "s";
    }
    if($department) {
        $where_clause .= " AND e.department = ?";
        $params[] = $department;
        $types .= "s";
    }
}

// Handle event deletion
if(isset($_POST['delete_event'])) {
    $event_id = $_POST['event_id'];
    $delete_sql = "DELETE FROM events WHERE id = ? AND created_by = ?";
    if($stmt = mysqli_prepare($conn, $delete_sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $event_id, $user_id);
        mysqli_stmt_execute($stmt);
    }
}

// Fetch events created by this representative
$events_sql = "SELECT * FROM events e JOIN users u ON e.created_by = u.id $where_clause ORDER BY e.event_date DESC";
$events = [];
if($stmt = mysqli_prepare($conn, $events_sql)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while($row = mysqli_fetch_assoc($result)) {
        $events[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - University Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Events</h1>
            <a href="add-event.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Event
            </a>
        </div>

        <!-- Event Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="searchEvent" placeholder="Search events...">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="filterCategory">
                            <option value="">All Categories</option>
                            <option value="society">Society Events</option>
                            <option value="department">Department Events</option>
                            <option value="open">Open Events</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="past">Past</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Events List -->
        <div class="row" id="eventsList">
            <?php foreach($events as $event): ?>
            <div class="col-md-6 col-lg-4 mb-4 event-item" 
                 data-category="<?php echo htmlspecialchars($event['category']); ?>"
                 data-date="<?php echo htmlspecialchars($event['event_date']); ?>">
                <div class="card h-100">
                    <?php if($event['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($event['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($event['title']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars(substr($event['description'], 0, 100)) . '...'; ?></p>
                        <div class="mb-3">
                            <span class="badge bg-primary"><?php echo ucfirst(htmlspecialchars($event['category'])); ?></span>
                            <span class="badge bg-info"><?php echo htmlspecialchars($event['society'] ?: $event['department']); ?></span>
                        </div>
                        <p class="card-text">
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i> <?php echo date('M d, Y', strtotime($event['event_date'])); ?><br>
                                <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($event['location']); ?>
                            </small>
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <div class="d-flex justify-content-between">
                            <a href="edit-event.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                <button type="submit" name="delete_event" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if(empty($events)): ?>
        <div class="text-center py-5">
            <i class="bi bi-calendar-x display-1 text-muted"></i>
            <h3 class="mt-3">No Events Found</h3>
            <p class="text-muted">You haven't created any events yet.</p>
            <a href="add-event.php" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle"></i> Create Your First Event
            </a>
        </div>
        <?php endif; ?>
    </div>

    <?php include '../../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
    <script>
        // Event filtering and search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchEvent');
            const categoryFilter = document.getElementById('filterCategory');
            const statusFilter = document.getElementById('filterStatus');
            const eventItems = document.querySelectorAll('.event-item');

            function filterEvents() {
                const searchTerm = searchInput.value.toLowerCase();
                const category = categoryFilter.value;
                const status = statusFilter.value;
                const currentDate = new Date();

                eventItems.forEach(item => {
                    const title = item.querySelector('.card-title').textContent.toLowerCase();
                    const description = item.querySelector('.card-text').textContent.toLowerCase();
                    const itemCategory = item.dataset.category;
                    const eventDate = new Date(item.dataset.date);
                    
                    const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
                    const matchesCategory = !category || itemCategory === category;
                    const matchesStatus = !status || 
                        (status === 'upcoming' && eventDate >= currentDate) ||
                        (status === 'past' && eventDate < currentDate);

                    item.style.display = matchesSearch && matchesCategory && matchesStatus ? 'block' : 'none';
                });
            }

            searchInput.addEventListener('input', filterEvents);
            categoryFilter.addEventListener('change', filterEvents);
            statusFilter.addEventListener('change', filterEvents);
        });
    </script>
</body>
</html> 