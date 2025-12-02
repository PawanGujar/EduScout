<?php
// bookmarks.php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// Fetch bookmarked courses with full details
$stmt = $mysqli->prepare("
  SELECT c.id, c.title, c.duration, c.provider, c.thumbnail_url, c.field
  FROM courses c
  JOIN bookmarks b ON c.id = b.course_id
  WHERE b.user_id = ?
  ORDER BY b.created_at DESC
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Bookmarks</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <?php include 'header.php'; /* you can extract the header nav into a separate file */ ?>
  <div class="container">
    <main class="main-content">
      <h2>My Bookmarked Courses</h2>
      <section class="courses">
        <?php if (count($courses) > 0): ?>
          <?php foreach ($courses as $course): ?>
            <div class="course-card">
              <img src="<?= htmlspecialchars($course['thumbnail_url'] ?: 'placeholder.png') ?>" alt="" />
              <div class="course-info">
                <h3><?= htmlspecialchars($course['title']) ?></h3>
                <p>Duration: <?= htmlspecialchars($course['duration']) ?></p>
                <p>Field: <?= htmlspecialchars($course['field']) ?></p>
                <button class="bookmark-btn bookmarked"
                        data-course-id="<?= $course['id'] ?>">Bookmarked</button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>You have no bookmarks yet.</p>
        <?php endif; ?>
      </section>
    </main>
  </div>
  <script src="scripts.js"></script>
</body>
</html>
