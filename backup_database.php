<?php
require_once 'config/database.php';

// Set the backup file name with current date and time
$backup_file = 'backup_' . date("Y-m-d_H-i-s") . '.sql';

// Get all table names
$tables = array();
$result = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

// Open the backup file
$handle = fopen($backup_file, 'w');

// Add DROP TABLE statements
foreach ($tables as $table) {
    fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
}

// Add CREATE TABLE and INSERT statements
foreach ($tables as $table) {
    // Get CREATE TABLE statement
    $result = mysqli_query($conn, "SHOW CREATE TABLE `$table`");
    $row = mysqli_fetch_row($result);
    fwrite($handle, "\n" . $row[1] . ";\n\n");

    // Get table data
    $result = mysqli_query($conn, "SELECT * FROM `$table`");
    while ($row = mysqli_fetch_assoc($result)) {
        $values = array_map(function($value) use ($conn) {
            if ($value === null) return 'NULL';
            return "'" . mysqli_real_escape_string($conn, $value) . "'";
        }, $row);
        
        fwrite($handle, "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n");
    }
    fwrite($handle, "\n");
}

fclose($handle);

echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
echo "<h2 style='color: #28a745;'>Database Backup Complete! 🎉</h2>";
echo "<p>Your database has been backed up to: <strong>$backup_file</strong></p>";
echo "<p>You can now safely proceed with dropping and recreating the database.</p>";
echo "<p><a href='drop_database.php' style='display: inline-block; padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px;'>Proceed to Drop Database</a></p>";
echo "</div>";

mysqli_close($conn);
?> 