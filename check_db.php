<?php
// check_db.php — quick diagnostic for DB and users table
// Restrict to localhost only
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/config/config.php';

echo "<h3>Database Connection Check</h3>";
echo "Host: " . DB_HOST . "<br>";
echo "Database: " . DB_NAME . "<br>";
echo "User: " . DB_USER . "<br>";
if ($mysqli->connect_errno) {
    echo "<p style='color:red'>❌ Connection failed: " . $mysqli->connect_error . "</p>";
    exit;
} else {
    echo "<p style='color:green'>✅ Connected</p>";
}

echo "<h3>Users Table Structure</h3>";
$result = $mysqli->query("DESCRIBE users");
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
    echo "<p style='color:red'>❌ Query failed: " . $mysqli->error . "</p>";
}

echo "<h3>Users in Database</h3>";
$result = $mysqli->query("SELECT id, email, username, role, password_hash FROM users LIMIT 10");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Email</th><th>Username</th><th>Role</th><th>Password Hash (first 30 chars)</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $hash_preview = substr($row['password_hash'], 0, 30) . (strlen($row['password_hash']) > 30 ? '...' : '');
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
        echo "<td><code>" . htmlspecialchars($hash_preview) . "</code></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found or query failed: " . $mysqli->error . "</p>";
}

$mysqli->close();
?>


