<?php
session_start();
require_once '../../config/database.php';

// Get all departments
$departments = [];
$sql = "SELECT * FROM departments ORDER BY name ASC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $departments[] = $row;
    }
}

// Get selected department
$selected_department = isset($_GET['department']) ? $_GET['department'] : null;

// Get events for selected department
$events = [];
if ($selected_department) {
    $sql = "SELECT e.*, u.full_name as creator_name, 
            (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as registration_count 
            FROM events e 
            JOIN users u ON e.created_by = u.id 
            WHERE e.department = ? 
            ORDER BY e.event_date ASC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $selected_department);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = $row;
        }
    }
}

// Include header
require_once '../../components/header.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Department List -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header text-white">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Departments</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($departments as $dept): ?>
                        <a href="?department=<?php echo urlencode($dept['name']); ?>" 
                           class="list-group-item list-group-item-action <?php echo ($selected_department === $dept['name']) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Events List -->
        <div class="col-md-9">
            <?php if ($selected_department): ?>
                <h2 class="mb-4" style="color: var(--navy-blue);">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <?php echo htmlspecialchars($selected_department); ?> Events
                </h2>
                
                <?php if (!empty($events)): ?>
                    <div class="row">
                        <?php foreach ($events as $event): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card event-card">
                                    <?php if ($event['image_url']): ?>
                                        <img src="/Project/<?php echo htmlspecialchars($event['image_url']); ?>" 
                                             class="card-img-top event-image" 
                                             alt="<?php echo htmlspecialchars($event['title']); ?>">
                                    <?php else: ?>
                                        <div class="card-img-top event-image bg-secondary d-flex align-items-center justify-content-center">
                                            <i class="fas fa-calendar-alt fa-3x text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="card-body">
                                        <span class="badge bg-primary category-badge">
                                            <?php echo htmlspecialchars($event['category']); ?>
                                        </span>
                                        <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                        <p class="card-text">
                                            <?php echo htmlspecialchars(substr($event['description'], 0, 100)) . '...'; ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> 
                                                <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                            </small>
                                            <small class="text-muted">
                                                <i class="fas fa-users"></i> 
                                                <?php echo $event['registration_count']; ?> registered
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="event-details.php?id=<?php echo $event['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm">View Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        No events found for this department.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    Please select a department to view its events.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../../components/footer.php';
?> 