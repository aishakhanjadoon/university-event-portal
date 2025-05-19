<?php
// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Project/pages/auth/login.php");
    exit;
}
?>
<!-- Admin Navigation -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/Project/pages/admin/dashboard.php" class="btn btn-outline-primary me-2">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    <div>
        <a href="/Project/pages/admin/manage-users.php" class="btn btn-primary me-2">Manage Users</a>
        <a href="/Project/pages/admin/manage-events.php" class="btn btn-primary">Manage Events</a>
    </div>
</div> 