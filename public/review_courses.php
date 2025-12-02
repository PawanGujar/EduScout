<?php
// review_courses.php
session_start();
require_once 'config.php';

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';

// Handle approve/reject actions
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'] === 'approve' ? 'approved' : 'rejected';

    $stmt = $mysqli->prepare("UPDATE courses SET status=? WHERE id=?");
    $stmt->bind_param('si', $action, $id);
    if ($stmt->execute()) {
        $message = "✅ Course $action.";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch pending courses
$result = $mysqli->query("SELECT c.id, c.title, c.url, c.category, u.username 
                          FROM courses c 
                          JOIN users u ON c.submitted_by = u.id
                          WHERE c.status='pending'");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Review Courses - EduScout</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <?php include 'header.php'; ?>
  <div class="container">
    <h2>Review Submitted Courses</h2>
    <?php if ($message): ?>
      <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <?php if ($result->num_rows > 0): ?>
      <table border="1" cellpadding="8" cellspacing="0">
        <tr>
          <th>Title</th>
          <th>Category</th>
          <th>Submitted By</th>
          <th>Preview</th>
          <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><a href="<?= htmlspecialchars($row['url']) ?>" target="_blank">View</a></td>
            <td>
              <a href="review_courses.php?action=approve&id=<?= $row['id'] ?>">✅ Approve</a> |
              <a href="review_courses.php?action=reject&id=<?= $row['id'] ?>">❌ Reject</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </table>
    <?php else: ?>
      <p>No pending courses.</p>
    <?php endif; ?>
  </div>
</body>
</html>
