<?php
// bookmarks.php
session_start();
require_once __DIR__ . '/config/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// Fetch bookmarked courses with full details
$stmt = $mysqli->prepare("
  SELECT c.id, c.title, c.url, c.duration, c.provider, c.thumbnail_url, c.field
  FROM courses c
  JOIN bookmarks b ON c.id = b.course_id
  WHERE b.user_id = ?
  ORDER BY b.created_at DESC
");
if (!$stmt) {
    error_log('Bookmarks query prepare failed: ' . $mysqli->error);
    $courses = [];
} else {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Bookmarks - EduScout</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
  <style>
    .bookmarks-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
    }

    .bookmarks-header {
      margin-bottom: 2rem;
    }

    .bookmarks-header h1 {
      color: #333;
      margin: 0 0 0.5rem 0;
      font-size: 2rem;
    }

    .bookmarks-count {
      color: #666;
      font-size: 0.95rem;
    }

    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .empty-state-icon {
      font-size: 4rem;
      margin-bottom: 1rem;
    }

    .empty-state h2 {
      color: #999;
      margin: 0 0 1rem 0;
    }

    .empty-state p {
      color: #999;
      margin: 0 0 1.5rem 0;
    }

    .empty-state a {
      color: #667eea;
      text-decoration: none;
      font-weight: 600;
    }

    .empty-state a:hover {
      text-decoration: underline;
    }

    @media (max-width: 768px) {
      .bookmarks-container {
        padding: 1rem;
      }

      .bookmarks-header h1 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>
  
  <div class="bookmarks-container">
    <div class="bookmarks-header">
      <h1>⭐ My Bookmarked Courses</h1>
      <p class="bookmarks-count"><?= count($courses) ?> course<?= count($courses) !== 1 ? 's' : '' ?> bookmarked</p>
    </div>

    <section class="courses">
      <?php if (count($courses) > 0): ?>
        <?php foreach ($courses as $course): ?>
          <a href="course_view.php?id=<?= $course['id'] ?>" style="text-decoration: none; color: inherit;">
            <div class="course-card">
              <div style="position: relative; width: 100%; padding-bottom: 56.25%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); overflow: hidden;">
                <img src="<?= htmlspecialchars($course['thumbnail_url'] ?: 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22280%22 height=%22160%22%3E%3Crect fill=%22%23667eea%22 width=%22280%22 height=%22160%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 font-size=%2232%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22white%22%3E🎬%3C/text%3E%3C/svg%3E') ?>"
                     alt="Course thumbnail"
                     style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;" />
              </div>
              <div class="course-info">
                <h3><?= htmlspecialchars($course['title']) ?></h3>
                <p><strong>⏱️ Duration:</strong> <?= htmlspecialchars($course['duration']) ?></p>
                <p><strong>📂 Field:</strong> <?= htmlspecialchars($course['field']) ?></p>
                <p><strong>📺 By:</strong> <?= htmlspecialchars($course['provider']) ?></p>
                <button class="bookmark-btn bookmarked"
                        data-course-id="<?= $course['id'] ?>"
                        onclick="event.preventDefault(); event.stopPropagation();">
                  ⭐ Bookmarked
                </button>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="grid-column: 1 / -1;">
          <div class="empty-state">
            <div class="empty-state-icon">📚</div>
            <h2>No Bookmarks Yet</h2>
            <p>You haven't bookmarked any courses yet. Start exploring and bookmark your favorite courses!</p>
            <a href="index.php">← Explore Courses</a>
          </div>
        </div>
      <?php endif; ?>
    </section>
  </div>

  <script src="assets/js/scripts.js"></script>
</body>
</html>



