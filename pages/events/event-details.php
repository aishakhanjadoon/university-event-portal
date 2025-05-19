<?php
require_once '../../config/database.php';
session_start();

// Check if event ID is provided
if(!isset($_GET['id'])) {
    header("Location: /Project/index.php");
    exit();
}

$event_id = $_GET['id'];
$error = '';
$success = '';

// Fetch event details
$sql = "SELECT e.*, u.username as creator_name, u.full_name as creator_full_name 
        FROM events e 
        LEFT JOIN users u ON e.created_by = u.id 
        WHERE e.id = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $event_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($event = mysqli_fetch_assoc($result)) {
        // Check if user is registered for this event
        $is_registered = false;
        if(isset($_SESSION['user_id'])) {
            $check_sql = "SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ?";
            if($check_stmt = mysqli_prepare($conn, $check_sql)) {
                mysqli_stmt_bind_param($check_stmt, "ii", $event_id, $_SESSION['user_id']);
                mysqli_stmt_execute($check_stmt);
                mysqli_stmt_store_result($check_stmt);
                $is_registered = mysqli_stmt_num_rows($check_stmt) > 0;
                mysqli_stmt_close($check_stmt);
            }
        }

        // Handle registration
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
            if(isset($_POST['register'])) {
                // Register for event
                $register_sql = "INSERT INTO event_registrations (event_id, user_id) VALUES (?, ?)";
                if($register_stmt = mysqli_prepare($conn, $register_sql)) {
                    mysqli_stmt_bind_param($register_stmt, "ii", $event_id, $_SESSION['user_id']);
                    if(mysqli_stmt_execute($register_stmt)) {
                        $success = "Successfully registered for the event!";
                        $is_registered = true;
                    } else {
                        $error = "Failed to register for the event.";
                    }
                    mysqli_stmt_close($register_stmt);
                }
            } elseif(isset($_POST['unregister'])) {
                // Unregister from event
                $unregister_sql = "DELETE FROM event_registrations WHERE event_id = ? AND user_id = ?";
                if($unregister_stmt = mysqli_prepare($conn, $unregister_sql)) {
                    mysqli_stmt_bind_param($unregister_stmt, "ii", $event_id, $_SESSION['user_id']);
                    if(mysqli_stmt_execute($unregister_stmt)) {
                        $success = "Successfully unregistered from the event.";
                        $is_registered = false;
                    } else {
                        $error = "Failed to unregister from the event.";
                    }
                    mysqli_stmt_close($unregister_stmt);
                }
            }
        }

        // Get registration count and list of registrations
        $registrations = [];
        $count_sql = "SELECT COUNT(*) as count FROM event_registrations WHERE event_id = ?";
        if($count_stmt = mysqli_prepare($conn, $count_sql)) {
            mysqli_stmt_bind_param($count_stmt, "i", $event_id);
            mysqli_stmt_execute($count_stmt);
            $count_result = mysqli_stmt_get_result($count_stmt);
            $registration_count = mysqli_fetch_assoc($count_result)['count'];
            mysqli_stmt_close($count_stmt);
        }

        // Get list of registered participants
        $reg_sql = "SELECT u.full_name 
                   FROM event_registrations er 
                   JOIN users u ON er.user_id = u.id 
                   WHERE er.event_id = ? 
                   ORDER BY er.registration_date DESC";
        if($reg_stmt = mysqli_prepare($conn, $reg_sql)) {
            mysqli_stmt_bind_param($reg_stmt, "i", $event_id);
            mysqli_stmt_execute($reg_stmt);
            $reg_result = mysqli_stmt_get_result($reg_stmt);
            while($row = mysqli_fetch_assoc($reg_result)) {
                $registrations[] = $row;
            }
            mysqli_stmt_close($reg_stmt);
        }
    } else {
        header("Location: /Project/index.php");
        exit();
    }
    mysqli_stmt_close($stmt);
}

// Include header
require_once '../../components/header.php';
?>

<div class="container py-5">
    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if(!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <?php if ($event['image_url']): ?>
                        <img src="/Project/<?php echo htmlspecialchars($event['image_url']); ?>" 
                             class="img-fluid rounded mb-4" alt="<?php echo htmlspecialchars($event['title']); ?>">
                    <?php endif; ?>
                    
                    <h1 class="card-title mb-4"><?php echo htmlspecialchars($event['title']); ?></h1>
                    
                    <div class="mb-4">
                        <span class="badge bg-primary me-2"><?php echo htmlspecialchars($event['category']); ?></span>
                        <?php if ($event['department']): ?>
                            <span class="badge bg-info me-2"><?php echo htmlspecialchars($event['department']); ?></span>
                        <?php endif; ?>
                        <?php if ($event['society']): ?>
                            <span class="badge bg-success"><?php echo htmlspecialchars($event['society']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><i class="fas fa-calendar me-2"></i>Date: <?php echo date('F d, Y', strtotime($event['event_date'])); ?></p>
                            <p><i class="fas fa-clock me-2"></i>Time: <?php echo date('h:i A', strtotime($event['event_date'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><i class="fas fa-map-marker-alt me-2"></i>Location: <?php echo htmlspecialchars($event['location']); ?></p>
                            <p><i class="fas fa-user me-2"></i>Organizer: <?php echo htmlspecialchars($event['creator_name']); ?></p>
                        </div>
                    </div>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['user_id'] === $event['created_by']): ?>
                            <div class="d-flex gap-2">
                                <a href="/Project/pages/events/edit-event.php?id=<?php echo $event['id']; ?>" 
                                   class="btn btn-primary">
                                    <i class="fas fa-edit me-2"></i>Edit Event
                                </a>
                                <a href="/Project/pages/events/delete-event.php?id=<?php echo $event['id']; ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this event? This action cannot be undone.')">
                                    <i class="fas fa-trash me-2"></i>Delete Event
                                </a>
                            </div>
                        <?php else: ?>
                            <form method="POST" class="d-grid">
                                <?php if ($is_registered): ?>
                                    <button type="submit" name="unregister" class="btn btn-danger">
                                        <i class="fas fa-times-circle me-2"></i>Unregister
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="register" class="btn btn-primary">
                                        <i class="fas fa-check-circle me-2"></i>Register Now
                                    </button>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Please <a href="/Project/pages/auth/login.php">login</a> to register for this event.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header text-white">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Registered Participants</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($registrations)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($registrations as $registration): ?>
                                <li class="list-group-item">
                                    <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($registration['full_name']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mb-0">No participants registered yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../../components/footer.php';
?> 