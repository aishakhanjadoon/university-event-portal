<?php
require_once 'config/database.php';

// Function to execute SQL queries safely
function executeQuery($conn, $sql) {
    if (!mysqli_query($conn, $sql)) {
        echo "Error executing query: " . mysqli_error($conn) . "<br>";
        echo "Query: " . $sql . "<br><br>";
        return false;
    }
    return true;
}

// Drop existing database if it exists
$sql = "DROP DATABASE IF EXISTS my_database";
executeQuery($conn, $sql);

// Create database
$sql = "CREATE DATABASE my_database";
if (!executeQuery($conn, $sql)) {
    die("Failed to create database");
}

// Select database
if (!mysqli_select_db($conn, "my_database")) {
    die("Failed to select database");
}

// Create users table
$sql = "CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('student', 'representative', 'admin') NOT NULL DEFAULT 'student',
    department VARCHAR(100),
    society VARCHAR(100),
    profile_picture VARCHAR(255),
    notify_new_events BOOLEAN DEFAULT TRUE,
    notify_registration BOOLEAN DEFAULT TRUE,
    notify_updates BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if (!executeQuery($conn, $sql)) {
    die("Failed to create users table");
}

// Create departments table
$sql = "CREATE TABLE departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if (!executeQuery($conn, $sql)) {
    die("Failed to create departments table");
}

// Create societies table
$sql = "CREATE TABLE societies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department) REFERENCES departments(name) ON DELETE SET NULL
)";
if (!executeQuery($conn, $sql)) {
    die("Failed to create societies table");
}

// Create events table
$sql = "CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    event_date DATETIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    category ENUM('academic', 'cultural', 'sports', 'technical', 'other') NOT NULL,
    image_url VARCHAR(255),
    created_by INT NOT NULL,
    department VARCHAR(100),
    society VARCHAR(100),
    max_participants INT,
    is_featured BOOLEAN DEFAULT FALSE,
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department) REFERENCES departments(name) ON DELETE SET NULL,
    FOREIGN KEY (society) REFERENCES societies(name) ON DELETE SET NULL
)";
if (!executeQuery($conn, $sql)) {
    die("Failed to create events table");
}

// Create event registrations table
$sql = "CREATE TABLE event_registrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
    attendance_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (event_id, user_id)
)";
if (!executeQuery($conn, $sql)) {
    die("Failed to create event_registrations table");
}

// Create event comments table
$sql = "CREATE TABLE event_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if (!executeQuery($conn, $sql)) {
    die("Failed to create event_comments table");
}

// Create notifications table
$sql = "CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('event', 'registration', 'system') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if (!executeQuery($conn, $sql)) {
    die("Failed to create notifications table");
}

// Insert default admin user
$sql = "INSERT INTO users (username, password, email, full_name, role) 
        VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'System Administrator', 'admin')";
if (!executeQuery($conn, $sql)) {
    die("Failed to insert admin user");
}

// Insert sample departments
$departments = [
    ['Computer Science', 'Department of Computer Science and Engineering'],
    ['Electrical Engineering', 'Department of Electrical and Electronics Engineering'],
    ['Mechanical Engineering', 'Department of Mechanical Engineering'],
    ['Business Administration', 'Department of Business Administration'],
    ['Arts and Humanities', 'Department of Arts and Humanities'],
    ['Physical Education', 'Department of Physical Education and Sports']
];

foreach ($departments as $dept) {
    $sql = "INSERT INTO departments (name, description) VALUES ('" . 
           mysqli_real_escape_string($conn, $dept[0]) . "', '" . 
           mysqli_real_escape_string($conn, $dept[1]) . "')";
    if (!executeQuery($conn, $sql)) {
        echo "Warning: Failed to insert department: " . $dept[0] . "<br>";
    }
}

// Insert sample societies
$societies = [
    ['Coding Club', 'A society for programming enthusiasts', 'Computer Science'],
    ['Drama Club', 'A society for theater and performing arts', 'Arts and Humanities'],
    ['Sports Club', 'A society for sports and physical activities', 'Physical Education'],
    ['Music Society', 'A society for music lovers and performers', 'Arts and Humanities'],
    ['Debate Club', 'A society for public speaking and debates', 'Arts and Humanities'],
    ['Robotics Club', 'A society for robotics and automation enthusiasts', 'Electrical Engineering'],
    ['Business Club', 'A society for business and entrepreneurship', 'Business Administration']
];

foreach ($societies as $society) {
    $sql = "INSERT INTO societies (name, description, department) VALUES ('" . 
           mysqli_real_escape_string($conn, $society[0]) . "', '" . 
           mysqli_real_escape_string($conn, $society[1]) . "', '" . 
           mysqli_real_escape_string($conn, $society[2]) . "')";
    if (!executeQuery($conn, $sql)) {
        echo "Warning: Failed to insert society: " . $society[0] . "<br>";
    }
}

// Insert sample events
$events = [
    [
        'title' => 'Annual Tech Symposium',
        'description' => 'Join us for a day of cutting-edge technology presentations and workshops.',
        'event_date' => date('Y-m-d H:i:s', strtotime('+7 days')),
        'location' => 'Main Auditorium',
        'category' => 'academic',
        'created_by' => 1,
        'department' => 'Computer Science',
        'society' => 'Coding Club',
        'is_featured' => true
    ],
    [
        'title' => 'Cultural Night',
        'description' => 'Experience the rich cultural diversity of our university through music, dance, and food.',
        'event_date' => date('Y-m-d H:i:s', strtotime('+14 days')),
        'location' => 'University Grounds',
        'category' => 'cultural',
        'created_by' => 1,
        'department' => 'Arts and Humanities',
        'society' => 'Music Society',
        'is_featured' => true
    ],
    [
        'title' => 'Sports Tournament',
        'description' => 'Annual inter-department sports tournament featuring cricket, football, and basketball.',
        'event_date' => date('Y-m-d H:i:s', strtotime('+21 days')),
        'location' => 'Sports Complex',
        'category' => 'sports',
        'created_by' => 1,
        'department' => 'Physical Education',
        'society' => 'Sports Club',
        'is_featured' => true
    ],
    [
        'title' => 'Robotics Workshop',
        'description' => 'Learn the basics of robotics and automation in this hands-on workshop.',
        'event_date' => date('Y-m-d H:i:s', strtotime('+10 days')),
        'location' => 'Engineering Lab',
        'category' => 'technical',
        'created_by' => 1,
        'department' => 'Electrical Engineering',
        'society' => 'Robotics Club',
        'is_featured' => false
    ]
];

foreach ($events as $event) {
    $sql = "INSERT INTO events (title, description, event_date, location, category, created_by, department, society, is_featured) 
            VALUES ('" . 
            mysqli_real_escape_string($conn, $event['title']) . "', '" . 
            mysqli_real_escape_string($conn, $event['description']) . "', '" . 
            $event['event_date'] . "', '" . 
            mysqli_real_escape_string($conn, $event['location']) . "', '" . 
            $event['category'] . "', " . 
            $event['created_by'] . ", '" . 
            mysqli_real_escape_string($conn, $event['department']) . "', '" . 
            mysqli_real_escape_string($conn, $event['society']) . "', " . 
            ($event['is_featured'] ? "TRUE" : "FALSE") . ")";
    if (!executeQuery($conn, $sql)) {
        echo "Warning: Failed to insert event: " . $event['title'] . "<br>";
    }
}

echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
echo "<h2 style='color: #28a745;'>Database Setup Complete! 🎉</h2>";
echo "<p>Your database has been successfully set up with the following default admin account:</p>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p><strong>Username:</strong> admin</p>";
echo "<p><strong>Password:</strong> password</p>";
echo "<p><strong>Email:</strong> admin@example.com</p>";
echo "</div>";
echo "<p style='color: #dc3545;'><strong>Important:</strong> Please change the default password after your first login!</p>";
echo "<p><a href='index.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go to Homepage</a></p>";
echo "</div>";

mysqli_close($conn);
?> 