<?php
// create_categories_table.php
// This script creates the categories table if it doesn't exist
// Run this once to set up dynamic category management

require_once __DIR__ . '/config/config.php';

echo "<h1>Category Table Setup</h1>";

// Create categories table
$sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50) DEFAULT '📚',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    echo "<p style='color: green;'>✅ Categories table created successfully or already exists.</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating categories table: " . htmlspecialchars($mysqli->error) . "</p>";
}

// Insert default categories
$defaultCategories = [
    ['IT', 'it', 'Information Technology & Programming', '💻'],
    ['Medical', 'medical', 'Medical & Healthcare Sciences', '🏥'],
    ['Engineering', 'engineering', 'Engineering & Technology', '⚙️'],
    ['Arts', 'arts', 'Arts, History & Humanities', '🎨']
];

foreach ($defaultCategories as $cat) {
    $stmt = $mysqli->prepare("INSERT IGNORE INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $cat[0], $cat[1], $cat[2], $cat[3]);
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Category '{$cat[0]}' inserted or already exists.</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Could not insert category '{$cat[0]}': " . htmlspecialchars($stmt->error) . "</p>";
    }
    $stmt->close();
}

echo "<p><a href='index.php' style='color: blue; text-decoration: none;'>← Back to Home</a></p>";
?>


