<?php
// rehash_passwords.php — securely re-hash all plain-text passwords to bcrypt
// Only accessible from localhost. Use this once to upgrade legacy plain-text passwords.

if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/config/config.php';

echo "<h3>Password Re-hash Utility</h3>";
echo "<p>This tool converts plain-text passwords to bcrypt hashes (one-way).</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    echo "<h4>Re-hashing in progress...</h4>";
    
    // Fetch all users
    $result = $mysqli->query("SELECT id, password_hash FROM users");
    if (!$result) {
        echo "<p style='color:red'>Query failed: " . $mysqli->error . "</p>";
        exit;
    }
    
    $rehashed = 0;
    $skipped = 0;
    
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $hash = $row['password_hash'];
        
        // If already a hash (starts with $), skip
        if (strpos($hash, '$') === 0) {
            $skipped++;
            continue;
        }
        
        // Plain text — re-hash it
        $new_hash = password_hash($hash, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->bind_param('si', $new_hash, $id);
        if ($stmt->execute()) {
            $rehashed++;
        } else {
            echo "<p style='color:red'>Failed to update user $id: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
    
    echo "<p style='color:green'>✅ Re-hashing complete!</p>";
    echo "<p><strong>Re-hashed:</strong> $rehashed users</p>";
    echo "<p><strong>Already hashed:</strong> $skipped users</p>";
    echo "<p><a href='check_db.php'>Check users table</a></p>";
} else {
    // Show confirmation form
    $result = $mysqli->query("SELECT COUNT(*) as total FROM users WHERE password_hash NOT LIKE '$%'");
    $row = $result->fetch_assoc();
    $plain_count = $row['total'];
    
    echo "<p>Found <strong>$plain_count</strong> user(s) with plain-text passwords.</p>";
    
    if ($plain_count > 0) {
        echo "<form method='POST'>";
        echo "<p><strong>⚠️ Warning:</strong> This will permanently convert plain-text passwords to bcrypt hashes.</p>";
        echo "<p>Users will still be able to log in with their original passwords.</p>";
        echo "<label><input type='checkbox' name='confirm' value='1' required> I confirm, proceed with re-hashing</label><br><br>";
        echo "<button type='submit'>Re-hash Now</button>";
        echo "</form>";
    } else {
        echo "<p style='color:green'>✅ All passwords are already hashed!</p>";
    }
}

$mysqli->close();
?>


