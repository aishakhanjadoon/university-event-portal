<?php
require_once '../../config/database.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Project/pages/auth/login.php");
    exit;
}

// Check if event ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage-events.php");
    exit;
}

$event_id = mysqli_real_escape_string($conn, $_GET['id']);

// Check if event exists
$check_sql = "SELECT * FROM events WHERE id = $event_id";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) === 0) {
    header("Location: manage-events.php");
    exit;
}

// Delete event
$sql = "DELETE FROM events WHERE id = $event_id";

if (mysqli_query($conn, $sql)) {
    header("Location: manage-events.php");
    exit;
} else {
    die("Error deleting event: " . mysqli_error($conn));
}
?>