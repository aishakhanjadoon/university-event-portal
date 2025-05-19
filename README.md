# university-event-portal
# SQL Project Setup

This project uses HTML, CSS, JavaScript, PHP, and MySQL for database operations.

## Prerequisites
- PHP 7.4 or higher
- MySQL Server
- Web Server (Apache/Nginx)
- Web Browser

## Setup Instructions

1. Install XAMPP or similar local development environment
2. Place the project files in your web server's root directory (e.g., htdocs for XAMPP)
3. Create a MySQL database using the schema in `database/schema.sql`
4. Update database credentials in `config/database.php`
5. Access the project through your web browser

## Project Structure
```
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── script.js
├── config/
│   └── database.php
├── database/
│   └── schema.sql
├── index.php
└── README.md
```

## Database Configuration
Update the database credentials in `config/database.php` with your MySQL server details:
- Host
- Username
- Password
- Database name 
