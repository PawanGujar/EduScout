<?php
// add_url_column.php — safely add url column to courses table if it doesn't exist
// Restrict to localhost only
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/config/config.php';

echo "<h3>Add URL Column to Courses Table</h3>";

// Check if column exists
$result = $mysqli->query("SHOW COLUMNS FROM courses LIKE 'url'");
if ($result && $result->num_rows > 0) {
    echo "<p style='color:green'>✅ URL column already exists</p>";
} else {
    // Column doesn't exist, add it
    $sql = "ALTER TABLE courses ADD COLUMN url VARCHAR(500) AFTER title";
    if ($mysqli->query($sql)) {
        echo "<p style='color:green'>✅ URL column added successfully</p>";
    } else {
        echo "<p style='color:red'>❌ Error adding URL column: " . $mysqli->error . "</p>";
    }
}

// Show the updated schema
echo "<h3>Updated Courses Table Structure</h3>";
$result = $mysqli->query("DESCRIBE courses");
if ($result) {
    echo "<table border='1' cellpadding='5'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<p><a href='check_tables.php'>View all tables and data</a></p>";
$mysqli->close();
?>


