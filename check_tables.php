<?php
// check_tables.php — verify courses table exists and show its schema
// Restrict to localhost only
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/config/config.php';

echo "<h3>Database Tables</h3>";
$result = $mysqli->query("SHOW TABLES");
if ($result) {
    echo "<p><strong>Tables in database:</strong></p>";
    echo "<ul>";
    while ($row = $result->fetch_row()) {
        echo "<li>" . htmlspecialchars($row[0]) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:red'>Failed to list tables: " . $mysqli->error . "</p>";
}

echo "<h3>Courses Table Structure</h3>";
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
} else {
    echo "<p style='color:red'>❌ Courses table does not exist or error: " . $mysqli->error . "</p>";
}

echo "<h3>Courses in Database</h3>";
$result = $mysqli->query("SELECT * FROM courses LIMIT 10");
if ($result) {
    if ($result->num_rows > 0) {
        $fields = $result->fetch_fields();
        echo "<table border='1' cellpadding='5'><tr>";
        foreach ($fields as $field) {
            echo "<th>" . htmlspecialchars($field->name) . "</th>";
        }
        echo "</tr>";
        
        $result->data_seek(0); // Reset pointer
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $val) {
                echo "<td>" . htmlspecialchars(substr($val, 0, 50)) . (strlen($val) > 50 ? '...' : '') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No courses found.</p>";
    }
} else {
    echo "<p style='color:red'>Failed to query courses: " . $mysqli->error . "</p>";
}

$mysqli->close();
?>


