<?php
// submit_course.php
session_start();
require_once __DIR__ . '/../config/config.php';

// Only allow editors or admins
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['editor','admin'])) {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $url   = trim($_POST['url']);
    $category = trim($_POST['category']);
    $submitted_by = $_SESSION['user_id'];

    if ($title && $url && $category) {
        $stmt = $mysqli->prepare("INSERT INTO courses (title, url, category, status, submitted_by) VALUES (?, ?, ?, 'pending', ?)");
        $stmt->bind_param('sssi', $title, $url, $category, $submitted_by);
        if ($stmt->execute()) {
            $message = "✅ Course submitted for review.";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "⚠ Please fill all fields.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Submit Course - EduScout</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>
  <div class="container">
    <h2>Submit a Course</h2>
    <?php if ($message): ?>
      <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form method="POST" action="submit_course.php">
      <label>Course Title:</label>
      <input type="text" name="title" required>
      <label>Course URL (YouTube link):</label>
      <input type="url" name="url" required>
      <label>Category:</label>
      <input type="text" name="category" required>
      <button type="submit">Submit</button>
    </form>
  </div>
</body>
</html>
