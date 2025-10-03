<?php
// profile.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// Fetch user details
$stmt = $mysqli->prepare("SELECT username, email, role, created_at FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Profile</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <?php include 'header.php'; ?>
  <div class="container">
    <main class="main-content">
      <h2>My Profile</h2>
      <div class="profile-card">
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Role:</strong> <?= htmlspecialchars(ucfirst($user['role'])) ?></p>
        <p><strong>Member Since:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
      </div>
    </main>
  </div>
</body>
</html>
