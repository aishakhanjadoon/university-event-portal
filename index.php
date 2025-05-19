<?php
session_start();
require_once 'config/database.php';

// featured events ka sql
$featured_events = [];
$sql = "SELECT e.*, u.full_name as creator_name, 
        (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as registration_count 
        FROM events e 
        JOIN users u ON e.created_by = u.id 
        ORDER BY e.event_date DESC 
        LIMIT 6";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $featured_events[] = $row;
    }
}

// departments ka sql
$departments = [];
$sql = "SELECT * FROM departments ORDER BY name ASC LIMIT 6";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $departments[] = $row;
    }
}

//  societies sql
$societies = [];
$sql = "SELECT * FROM societies ORDER BY name ASC LIMIT 6";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $societies[] = $row;
    }
}

//  header wali file ka link
require_once 'components/header.php';
?>

<!-- hero wala section -->
<div class="hero-section text-white text-center py-5 mt-4">
    <div class="container">
        <h1 class="display-4 mb-4">Welcome to University Events Portal</h1>
        <p class="lead mb-4">Discover and participate in exciting events across departments and societies</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="/Project/pages/events/department-events.php" class="btn btn-hero" role="button" aria-label="View Department Events">
                <i class="fas fa-building me-2"></i>Department Events
            </a>
            <a href="/Project/pages/events/society-events.php" class="btn btn-hero" role="button" aria-label="View Society Events">
                <i class="fas fa-users me-2"></i>Society Events
            </a>
        </div>
    </div>
</div>

<!-- Featured Events Section -->
<section class="py-5">
    <div class="container">
        <h2 class="section-title mb-4">Featured Events</h2>
        <div class="row">
            <?php foreach ($featured_events as $event): ?>
                <div class="col-md-4 mb-4">
                    <div class="card event-card">
                        <?php if ($event['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($event['image_url']); ?>" 
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
                            <a href="/Project/pages/events/event-details.php?id=<?php echo $event['id']; ?>" 
                               class="btn btn-outline-primary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Departments Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="section-title mb-4">Departments</h2>
        <div class="row">
            <?php foreach ($departments as $dept): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-building fa-3x mb-3" style="color: var(--cerulean);"></i>
                            <h5 class="card-title"><?php echo htmlspecialchars($dept['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($dept['description']); ?></p>
                            <a href="/Project/pages/events/department-events.php?department=<?php echo urlencode($dept['name']); ?>" 
                               class="btn btn-outline-primary">View Events</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
                </div>
            </section>

<!-- Societies Section -->
<section class="py-5">
    <div class="container">
        <h2 class="section-title mb-4">Societies</h2>
        <div class="row">
            <?php foreach ($societies as $soc): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-3x mb-3" style="color: var(--cerulean);"></i>
                            <h5 class="card-title"><?php echo htmlspecialchars($soc['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($soc['description']); ?></p>
                            <a href="/Project/pages/events/society-events.php?society=<?php echo urlencode($soc['name']); ?>" 
                               class="btn btn-outline-primary">View Events</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
// Include footer
require_once 'components/footer.php';
?> 