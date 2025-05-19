<?php
require_once '../../config/database.php';

// Add department column to societies table
$sql = "ALTER TABLE societies ADD COLUMN department VARCHAR(100) AFTER description";

if (mysqli_query($conn, $sql)) {
    echo "Successfully added department column to societies table.";
} else {
    echo "Error adding department column: " . mysqli_error($conn);
}
?> 