<?php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'faculty_system');

function getDB()
{
    static $conn = null;
    if ($conn !== null) return $conn;

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Create database if not exists
    $conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $conn->select_db(DB_NAME);

    // Auto-init if tables missing (without calling getDB recursively)
    $check = $conn->query("SHOW TABLES LIKE 'users'");
    if ($check->num_rows === 0) {
        // Run initialization directly using this connection
        runInitQueries($conn);
    }
    // Auto-migrate new columns
    $colCheck1 = $conn->query("SHOW COLUMNS FROM users LIKE 'display_name'");
    if ($colCheck1->num_rows === 0) {
        $conn->query("ALTER TABLE users ADD COLUMN display_name VARCHAR(100) DEFAULT NULL");
    }
    $colCheck2 = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
    if ($colCheck2->num_rows === 0) {
        $conn->query("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL");
    }

    // --- Academic Term and Curriculum Migration ---
    $conn->query("CREATE TABLE IF NOT EXISTS academic_terms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        is_active TINYINT(1) DEFAULT 0,
        user_id INT DEFAULT NULL
    )");
    
    $conn->query("CREATE TABLE IF NOT EXISTS curricula (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        user_id INT DEFAULT NULL
    )");

    // Ensure a default term exists
    $termCheck = $conn->query("SELECT id FROM academic_terms LIMIT 1");
    if ($termCheck->num_rows === 0) {
        $conn->query("INSERT INTO academic_terms (name, is_active, user_id) VALUES ('1st Semester 2024-2025', 1, 1)");
    }

    // Ensure a default curriculum exists
    $currCheck = $conn->query("SELECT id FROM curricula LIMIT 1");
    if ($currCheck->num_rows === 0) {
        $conn->query("INSERT INTO curricula (name, user_id) VALUES ('Standard Curriculum', 1)");
    }

    $tid = $conn->query("SHOW COLUMNS FROM schedules LIKE 'term_id'");
    if ($tid->num_rows === 0) {
        $conn->query("ALTER TABLE schedules ADD COLUMN term_id INT DEFAULT 1");
        $conn->query("UPDATE schedules SET term_id = 1 WHERE term_id IS NULL");
    }

    $cid = $conn->query("SHOW COLUMNS FROM subjects LIKE 'curriculum_id'");
    if ($cid->num_rows === 0) {
        $conn->query("ALTER TABLE subjects ADD COLUMN curriculum_id INT DEFAULT 1");
        $conn->query("UPDATE subjects SET curriculum_id = 1 WHERE curriculum_id IS NULL");
    }

    $stid = $conn->query("SHOW COLUMNS FROM subjects LIKE 'term_id'");
    if ($stid->num_rows === 0) {
        $conn->query("ALTER TABLE subjects ADD COLUMN term_id INT DEFAULT 1");
        $conn->query("UPDATE subjects SET term_id = 1 WHERE term_id IS NULL");
    }

    return $conn;
}

function runInitQueries($db)
{
    $queries = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS faculty (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            department VARCHAR(100),
            email VARCHAR(100),
            status ENUM('Active', 'Inactive') DEFAULT 'Active',
            employment_status VARCHAR(50) DEFAULT 'Full-time',
            designated_campus VARCHAR(100) DEFAULT 'Main Campus'
        )",
        "CREATE TABLE IF NOT EXISTS subjects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(20) UNIQUE NOT NULL,
            name VARCHAR(100) NOT NULL,
            units INT DEFAULT 3
        )",
        "CREATE TABLE IF NOT EXISTS rooms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) UNIQUE NOT NULL,
            capacity INT DEFAULT 30,
            type VARCHAR(50)
        )",
        "CREATE TABLE IF NOT EXISTS schedules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            faculty_id INT,
            subject_id INT,
            room_id INT,
            day VARCHAR(20),
            section VARCHAR(50),
            start_time TIME,
            end_time TIME,
            FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE CASCADE,
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
            FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
        )"
    ];

    foreach ($queries as $query) {
        $db->query($query);
    }

    $admin_check = $db->query("SELECT id FROM users WHERE username = 'admin'");
    if ($admin_check->num_rows === 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $db->query("INSERT INTO users (username, password) VALUES ('admin', '$password')");
    }
}

function initDB()
{
    $db = getDB();
    runInitQueries($db);
}
