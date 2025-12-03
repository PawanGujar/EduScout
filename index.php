<?php
// index.php
session_start();
require_once __DIR__ . '/config/config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role     = $_SESSION['role'];  // 'learner', 'editor', or 'admin'

// Fetch categories from database
$categories = [];
$stmt = $mysqli->prepare("SELECT id, name, slug, icon FROM categories ORDER BY created_at ASC");
if ($stmt) {
    $stmt->execute();
    $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// If no categories in DB, use defaults
if (empty($categories)) {
    $categories = [
        ['id' => 1, 'name' => 'IT', 'slug' => 'it', 'icon' => '💻'],
        ['id' => 2, 'name' => 'Medical', 'slug' => 'medical', 'icon' => '🏥'],
        ['id' => 3, 'name' => 'Engineering', 'slug' => 'engineering', 'icon' => '⚙️'],
        ['id' => 4, 'name' => 'Arts', 'slug' => 'arts', 'icon' => '🎨']
    ];
}

// Determine field filter (default to first category or IT)
$field = isset($_GET['field']) ? $_GET['field'] : ($categories[0]['name'] ?? 'IT');

// 1) Fetch all courses for the chosen field
// Helper: extract YouTube video ID and build thumbnail URL
function youtube_thumbnail_from_url($url) {
  if (!$url) return '';
  // Try to match common YouTube patterns
  $patterns = [
    '/v=([A-Za-z0-9_-]{11})/',
    '/youtu\.be\/([A-Za-z0-9_-]{11})/',
    '/embed\/([A-Za-z0-9_-]{11})/'
  ];
  foreach ($patterns as $pat) {
    if (preg_match($pat, $url, $m)) {
      return 'https://i.ytimg.com/vi/' . $m[1] . '/hqdefault.jpg';
    }
  }
  return '';
}

// Prepare search if provided
$search = trim($_GET['q'] ?? '');

if ($search !== '') {
  $like = '%' . $search . '%';
  $stmt = $mysqli->prepare("
    SELECT id, title, url, duration, provider, thumbnail_url, status, submitted_by
    FROM courses
    WHERE field = ? AND status = 'approved' AND (title LIKE ? OR provider LIKE ?)
    ORDER BY created_at DESC
  ");
  if (!$stmt) {
    error_log('Index search query prepare failed: ' . $mysqli->error);
    $courses = [];
  } else {
    $stmt->bind_param('sss', $field, $like, $like);
    $stmt->execute();
    $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
  }
} else {
  $stmt = $mysqli->prepare("
    SELECT id, title, url, duration, provider, thumbnail_url, status, submitted_by
    FROM courses
    WHERE field = ? AND status = 'approved'
    ORDER BY created_at DESC
  ");
  if (!$stmt) {
    error_log('Index query prepare failed: ' . $mysqli->error);
    $courses = [];
  } else {
    $stmt->bind_param('s', $field);
    $stmt->execute();
    $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
  }
}

// Ensure each course has a thumbnail_url (try to derive from YouTube url when empty)
foreach ($courses as &$c) {
  if (empty($c['thumbnail_url'])) {
    $thumb = youtube_thumbnail_from_url($c['url']);
    $c['thumbnail_url'] = $thumb ?: '';
  }
}
unset($c);

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
  <link rel="stylesheet" href="assets/css/styles.css" />
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="container">
    <aside class="sidebar">
      <h2>📚 Fields</h2>
      <ul>
        <?php foreach ($categories as $cat): ?>
          <li>
            <a href="index.php?field=<?= urlencode($cat['name']) ?>"
               <?= $field === $cat['name'] ? 'class="active"' : '' ?>>
              <span><?= htmlspecialchars($cat['icon']) ?></span> <?= htmlspecialchars($cat['name']) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </aside>

    <main class="main-content">
      <h2 style="margin-bottom: 1rem;">
        <?php
          $categoryIcon = '📚';
          foreach ($categories as $cat) {
            if ($cat['name'] === $field) {
              $categoryIcon = htmlspecialchars($cat['icon']);
              break;
            }
          }
          echo $categoryIcon . ' ' . htmlspecialchars($field) . ' Courses';
        ?>
      </h2>

      <form method="GET" action="index.php" class="search-form" style="margin-bottom:1rem; display:flex; gap:0.5rem; align-items:center;">
        <input type="hidden" name="field" value="<?= htmlspecialchars($field) ?>">
        <input type="search" name="q" placeholder="Search courses or creator..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="flex:1; padding:0.6rem 0.8rem; border-radius:6px; border:2px solid #e0e0e0;">
        <button type="submit" class="btn">Search</button>
      </form>

      <section class="courses">
        <?php if (count($courses) > 0): ?>
          <?php foreach ($courses as $course): 
            $isBook = in_array($course['id'], $bookmarked);
            $can_edit_delete = ($role === 'admin') || ($course['submitted_by'] == $userId && in_array($role, ['editor', 'admin']));
          ?>
            <div style="position: relative;">
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
                    <p><strong>📺 By:</strong> <?= htmlspecialchars($course['provider']) ?></p>
                    <?php if ($role === 'learner'): ?>
                      <button
                        class="bookmark-btn<?= $isBook ? ' bookmarked' : '' ?>"
                        data-course-id="<?= $course['id'] ?>"
                        onclick="event.preventDefault(); event.stopPropagation();"
                      >
                        <?= $isBook ? '⭐ Bookmarked' : '☆ Bookmark' ?>
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </a>
              
              <?php if ($can_edit_delete): ?>
                <div style="position: absolute; top: 10px; right: 10px; display: flex; gap: 5px; opacity: 0; transition: opacity 0.3s ease;" class="course-actions-menu" onmouseenter="this.style.opacity='1'" onmouseleave="this.style.opacity='0'">
                  <a href="edit_course.php?id=<?= $course['id'] ?>" title="Edit" style="background: #28a745; color: white; padding: 8px 12px; border-radius: 4px; text-decoration: none; font-size: 0.9em;">✏️ Edit</a>
                  <a href="delete_course.php?id=<?= $course['id'] ?>" title="Delete" style="background: #dc3545; color: white; padding: 8px 12px; border-radius: 4px; text-decoration: none; font-size: 0.9em;" onclick="return confirm('Delete this course?');">🗑️ Delete</a>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; background: white; border-radius: 8px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
            <p style="font-size: 1.2rem; color: #999;">📭 No courses found in <?= htmlspecialchars($field) ?> yet.</p>
            <p style="color: #999;">Be the first to submit a course in this field!</p>
          </div>
        <?php endif; ?>
      </section>
    </main>
  </div>

  <script src="assets/js/scripts.js"></script>
</body>
</html>



