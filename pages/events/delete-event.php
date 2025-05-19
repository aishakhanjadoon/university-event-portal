<?php
require_once '../../config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /Project/pages/auth/login.php');
    exit();
}

// Check if event ID is provided
if (!isset($_GET['id'])) {
    header('Location: /Project/index.php');
    exit();
}

$event_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get event details to check ownership and get image path
$sql = "SELECT * FROM events WHERE id = ? AND created_by = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $event_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$event = mysqli_fetch_assoc($result);

// If event doesn't exist or user doesn't have permission
if (!$event) {
    header('Location: /Project/index.php');
    exit();
}

// Delete the event
$sql = "DELETE FROM events WHERE id = ? AND created_by = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $event_id, $user_id);

if (mysqli_stmt_execute($stmt)) {
    // Delete the event image if it exists
    if ($event['image_url'] && file_exists('../../' . $event['image_url'])) {
        unlink('../../' . $event['image_url']);
    }
    
    // Redirect to appropriate page based on event type
    if ($event['department']) {
        header('Location: /Project/pages/events/department-events.php?department=' . urlencode($event['department']));
    } else if ($event['society']) {
        header('Location: /Project/pages/events/society-events.php?society=' . urlencode($event['society']));
    } else {
        header('Location: /Project/index.php');
    }
    exit();
} else {
    // If deletion fails, redirect to event details page
    header('Location: /Project/pages/events/event-details.php?id=' . $event_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Event - University Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h1 class="card-title mb-4">Delete Event</h1>
                        
                        <?php if(!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <div class="alert alert-warning">
                            <h4 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Warning!</h4>
                            <p>You are about to delete the following event. This action cannot be undone.</p>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                <p class="card-text text-muted">
                                    <i class="bi bi-calendar-event"></i> 
                                    <?php echo date('F d, Y g:i A', strtotime($event['event_date'])); ?>
                                </p>
                                <p class="card-text">
                                    <?php echo substr(htmlspecialchars($event['description']), 0, 200) . '...'; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary">
                                        <?php echo ucfirst(htmlspecialchars($event['category'])); ?>
                                    </span>
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($event['location']); ?>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="confirm" required>
                                    <label class="form-check-label" for="confirm">
                                        I understand that this action cannot be undone and all event registrations will be deleted.
                                    </label>
                                    <div class="invalid-feedback">
                                        You must confirm before proceeding.
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="confirm_delete" class="btn btn-danger">
                                    <i class="bi bi-trash"></i> Delete Event
                                </button>
                                <a href="/pages/events/manage-events.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
    <script>
    // Form validation
    (function() {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if(!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
    </script>
</body>
</html> 