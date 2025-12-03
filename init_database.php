<?php
// init_database.php — creates tables if they don't exist
// Restrict to localhost only
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/config/config.php';

echo "<h3>Database Initialization</h3>";

$tables_created = 0;
$tables_skipped = 0;

// 1. Users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('learner','editor','admin') DEFAULT 'learner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    echo "<p style='color:green'>✅ Users table ready</p>";
    $tables_created++;
} else {
    echo "<p style='color:orange'>⚠ Users table: " . $mysqli->error . "</p>";
}

// 2. Courses table
$sql = "CREATE TABLE IF NOT EXISTS courses (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    url VARCHAR(500),
    duration VARCHAR(50),
    provider VARCHAR(100),
    thumbnail_url VARCHAR(500),
    field VARCHAR(50),
    category VARCHAR(100),
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    submitted_by INT(11),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX (status),
    INDEX (field),
    INDEX (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    echo "<p style='color:green'>✅ Courses table ready</p>";
    $tables_created++;
} else {
    echo "<p style='color:orange'>⚠ Courses table: " . $mysqli->error . "</p>";
}

// 3. Bookmarks table
$sql = "CREATE TABLE IF NOT EXISTS bookmarks (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    course_id INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_bookmark (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX (user_id),
    INDEX (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    echo "<p style='color:green'>✅ Bookmarks table ready</p>";
    $tables_created++;
} else {
    echo "<p style='color:orange'>⚠ Bookmarks table: " . $mysqli->error . "</p>";
}

echo "<hr>";
echo "<p><strong>Summary:</strong> $tables_created table(s) created or already exist.</p>";
echo "<p><a href='check_tables.php'>View all tables and schema</a></p>";

$mysqli->close();
?>


