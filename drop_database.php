<?php
require_once 'config/database.php';

// Drop the database
$sql = "DROP DATABASE IF EXISTS my_database";
if (mysqli_query($conn, $sql)) {
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<h2 style='color: #28a745;'>Database Dropped Successfully! 🎉</h2>";
    echo "<p>The existing database has been dropped.</p>";
    echo "<p>You can now proceed to create a new database with the updated schema.</p>";
    echo "<p><a href='setup_database.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Create New Database</a></p>";
    echo "</div>";
} else {
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #dc3545; border-radius: 5px;'>";
    echo "<h2 style='color: #dc3545;'>Error Dropping Database</h2>";
    echo "<p>Error: " . mysqli_error($conn) . "</p>";
    echo "<p><a href='index.php' style='display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;'>Return to Homepage</a></p>";
    echo "</div>";
}

mysqli_close($conn);
?> 