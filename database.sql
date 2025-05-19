-- Create database if not exists
CREATE DATABASE IF NOT EXISTS my_database;
USE my_database;

-- Users table
CREATE TABLE IF NOT EXISTS users (
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
);

-- Departments table
CREATE TABLE IF NOT EXISTS departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Societies table
CREATE TABLE IF NOT EXISTS societies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE IF NOT EXISTS events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    event_date DATETIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    category ENUM('academic', 'cultural', 'sports', 'workshop', 'other') NOT NULL,
    image_url VARCHAR(255),
    created_by INT NOT NULL,
    department VARCHAR(100),
    society VARCHAR(100),
    max_participants INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Event registrations table
CREATE TABLE IF NOT EXISTS event_registrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (event_id, user_id)
);

-- Event comments table
CREATE TABLE IF NOT EXISTS event_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT INTO users (username, password, email, full_name, role)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'System Administrator', 'admin');

-- Insert some sample departments
INSERT INTO departments (name, description) VALUES
('Computer Science', 'Department of Computer Science and Engineering'),
('Electrical Engineering', 'Department of Electrical and Electronics Engineering'),
('Mechanical Engineering', 'Department of Mechanical Engineering'),
('Business Administration', 'Department of Business Administration'),
('Arts and Humanities', 'Department of Arts and Humanities');

-- Insert some sample societies
INSERT INTO societies (name, description) VALUES
('Coding Club', 'A society for programming enthusiasts'),
('Drama Club', 'A society for theater and performing arts'),
('Sports Club', 'A society for sports and physical activities'),
('Music Society', 'A society for music lovers and performers'),
('Debate Club', 'A society for public speaking and debates'); 