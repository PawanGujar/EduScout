<?php
// my_submissions.php
session_start();
require_once __DIR__ . '/../config/config.php';

// Only editors/admins
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['editor','admin'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $mysqli->prepare("SELECT id, title, category, status FROM courses WHERE submitted_by = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>My Submissions - EduScout</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>
  <div class="container">
    <h2>My Submitted Courses</h2>
    <?php if ($result->num_rows > 0): ?>
      <table border="1" cellpadding="8" cellspacing="0">
        <tr>
          <th>Title</th>
          <th>Category</th>
          <th>Status</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><?= ucfirst($row['status']) ?></td>
          </tr>
        <?php endwhile; ?>
      </table>
    <?php else: ?>
      <p>You haven’t submitted any courses yet.</p>
    <?php endif; ?>
  </div>
</body>
</html>
