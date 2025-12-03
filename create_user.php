<?php
require __DIR__ . '/config/config.php';

$email = 'admin@example.com';
$username = 'adminuser';
$role = 'admin';
$plain = '54321'; // login with this

$hash = password_hash($plain, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param('ssss', $username, $email, $hash, $role);
if ($stmt->execute()) {
    echo "User created: $email / $plain";
} else {
    echo "Insert error: " . $stmt->error;
}


