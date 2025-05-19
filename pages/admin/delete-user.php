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

// Prevent admin self-deletion (optional but recommended)
if ($_SESSION['user_id'] == $user_id) {
    header("Location: manage-users.php"); 
    exit;
}

// Check if user exists (excluding admin)
$check_sql = "SELECT * FROM users WHERE id = $user_id AND role != 'admin'";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) === 0) {
    header("Location: manage-users.php");
    exit;
}

// Delete user
$sql = "DELETE FROM users WHERE id = $user_id AND role != 'admin'";

if (mysqli_query($conn, $sql)) {
    header("Location: manage-users.php");
    exit;
} else {
    die("Error deleting user: " . mysqli_error($conn));
}
?>