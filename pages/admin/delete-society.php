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
    header("Location: manage-societies.php");
    exit;
}

$society_id = mysqli_real_escape_string($conn, $_GET['id']);

// Check if society exists
$check_sql = "SELECT * FROM societies WHERE id = $society_id";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) === 0) {
    header("Location: manage-societies.php");
    exit;
}

// Delete society
$sql = "DELETE FROM societies WHERE id = $society_id";

if (mysqli_query($conn, $sql)) {
    header("Location: manage-societies.php");
    exit;
} else {
    die("Error deleting society: " . mysqli_error($conn));
} 