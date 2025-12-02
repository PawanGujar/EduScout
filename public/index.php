<?php
// index.php
session_start();
require_once 'config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role     = $_SESSION['role'];  // 'learner', 'editor', or 'admin'

// Determine field filter (default IT)
$field = isset($_GET['field']) ? $_GET['field'] : 'IT';

// 1) Fetch all courses for the chosen field
$stmt = $mysqli->prepare("
    SELECT id, title, duration, provider, thumbnail_url
    FROM courses
    WHERE field = ?
    ORDER BY created_at DESC
");
$stmt->bind_param('s', $field);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 2) If learner, fetch their bookmarked course IDs
$bookmarked = [];
if ($role === 'learner') {
    $stmt = $mysqli->prepare("
        SELECT course_id 
        FROM bookmarks 
        WHERE user_id = ?
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $bookmarked[] = $row['course_id'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>EduScout Dashboard</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <header class="site-header">
    <h1>EduScout</h1>
    <nav>
      <ul>
        <li><a href="index.php"<?= !isset($_GET['field']) ? ' class="active"' : '' ?>>Home</a></li>
        <?php if ($role === 'learner'): ?>
          <li><a href="bookmarks.php">My Bookmarks</a></li>
        <?php endif; ?>
        <li><a href="profile.php">Profile (<?= htmlspecialchars($username) ?>)</a></li>
        <?php if ($role === 'editor' || $role === 'admin'): ?>
          <li><a href="submit_course.php">Submit Course</a></li>
        <?php endif; ?>
        <?php if ($role === 'admin'): ?>
          <li><a href="review_courses.php">Review Courses</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Logout</a></li>
      </ul>
    </nav>
  </header>

  <div class="container">
    <aside class="sidebar">
      <h2>Fields</h2>
      <ul>
        <?php 
          $fields = ['IT','Medical','Engineering','Arts'];
          foreach ($fields as $f): 
        ?>
          <li>
            <a href="index.php?field=<?= $f ?>"
               <?= $field === $f ? 'class="active"' : '' ?>>
              <?= $f ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </aside>

    <main class="main-content">
      <section class="courses">
        <?php if (count($courses) > 0): ?>
          <?php foreach ($courses as $course): 
            $isBook = in_array($course['id'], $bookmarked);
          ?>
            <div class="course-card">
              <img src="<?= htmlspecialchars($course['thumbnail_url'] ?: 'placeholder.png') ?>"
                   alt="Course thumbnail" />
              <div class="course-info">
                <h3><?= htmlspecialchars($course['title']) ?></h3>
                <p>Duration: <?= htmlspecialchars($course['duration']) ?></p>
                <p>By: <?= htmlspecialchars($course['provider']) ?></p>
                <?php if ($role === 'learner'): ?>
                  <button
                    class="bookmark-btn<?= $isBook ? ' bookmarked' : '' ?>"
                    data-course-id="<?= $course['id'] ?>"
                  >
                    <?= $isBook ? 'Bookmarked' : 'Bookmark' ?>
                  </button>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No courses found in this field.</p>
        <?php endif; ?>
      </section>
    </main>
  </div>

  <script src="scripts.js"></script>
</body>
</html>
