<?php
require_once '../../config/database.php';
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Project/pages/auth/login.php");
    exit;
}

// Check if ID is provided
if(!isset($_GET['id'])) {
    header("Location: manage-departments.php");
    exit;
}

$department_id = mysqli_real_escape_string($conn, $_GET['id']);

// Check if department exists
$check_sql = "SELECT * FROM departments WHERE id = $department_id";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) === 0) {
    header("Location: manage-departments.php");
    exit;
}

// Delete department
$sql = "DELETE FROM departments WHERE id = $department_id";

if (mysqli_query($conn, $sql)) {
    header("Location: manage-departments.php");
    exit;
} else {
    die("Error deleting department: " . mysqli_error($conn));
} 