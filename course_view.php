<?php
// course_view.php — view a single course with embedded YouTube video/playlist
session_start();
require_once __DIR__ . '/config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($course_id <= 0) {
    die('Invalid course ID.');
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch course details
$stmt = $mysqli->prepare("SELECT id, title, url, duration, provider, field, status, submitted_by FROM courses WHERE id = ?");
if (!$stmt) {
    die('Database error: ' . $mysqli->error);
}
$stmt->bind_param('i', $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();

if (!$course) {
    die('Course not found.');
}

// Check if user can edit/delete this course
$can_edit_delete = ($role === 'admin') || ($course['submitted_by'] == $userId && $role !== 'learner');

// Check if user has bookmarked this course
$bookmarked = false;
if ($role === 'learner') {
    $stmt = $mysqli->prepare("SELECT 1 FROM bookmarks WHERE user_id = ? AND course_id = ?");
    $stmt->bind_param('ii', $userId, $course_id);
    $stmt->execute();
    $bookmarked = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}

// Convert YouTube URL to embeddable format
function get_youtube_embed($url) {
    // Handle different YouTube URL formats
    $patterns = [
        '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',  // youtube.com/watch?v=ID
        '/youtu\.be\/([a-zA-Z0-9_-]+)/',                // youtu.be/ID
        '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',      // youtube.com/embed/ID
        '/youtube\.com\/playlist\?list=([a-zA-Z0-9_-]+)/', // youtube.com/playlist?list=ID
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            $id = $matches[1];
            // Check if it's a playlist
            if (strpos($url, 'playlist') !== false) {
                return "https://www.youtube.com/embed/videoseries?list=" . $id;
            }
            return "https://www.youtube.com/embed/" . $id;
        }
    }
    return null;
}

$embed_url = get_youtube_embed($course['url']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($course['title']) ?> - EduScout</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
  <style>
    .course-view-container {
      display: flex;
      flex-direction: column;
      max-width: 1000px;
      margin: 0 auto;
      padding: 2rem;
    }
    .video-container {
      position: relative;
      width: 100%;
      padding-bottom: 56.25%; /* 16:9 aspect ratio */
      margin-bottom: 2rem;
      background: #000;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    .video-container iframe {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border: none;
    }
    .course-info {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      margin-bottom: 2rem;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    .course-info h1 {
      margin-top: 0;
      color: #333;
      font-size: 2rem;
    }
    .course-meta {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin: 1.5rem 0;
    }
    .meta-item {
      padding: 1rem;
      background: #f0f7ff;
      border-left: 4px solid #667eea;
      border-radius: 6px;
    }
    .meta-item strong {
      color: #667eea;
      display: block;
      font-size: 0.85em;
      text-transform: uppercase;
      margin-bottom: 0.5rem;
    }
    .meta-item span {
      display: block;
      color: #555;
      font-size: 0.95rem;
    }
    .course-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-top: 2rem;
      padding-top: 1.5rem;
      border-top: 1px solid #e0e0e0;
    }
    .btn {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.95em;
      text-decoration: none;
      display: inline-block;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .btn-primary {
      background: #667eea;
      color: white;
      flex: 1;
      min-width: 150px;
    }
    .btn-primary:hover {
      background: #764ba2;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    .btn-bookmark {
      background: #ffc107;
      color: #333;
    }
    .btn-bookmark.bookmarked {
      background: #28a745;
      color: white;
    }
    .btn-bookmark:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    .back-link {
      margin-bottom: 1.5rem;
    }
    .back-link a {
      color: #667eea;
      text-decoration: none;
      font-size: 0.95em;
      font-weight: 600;
    }
    .back-link a:hover {
      text-decoration: underline;
    }
    .edit-delete-actions {
      display: flex;
      gap: 1rem;
      width: 100%;
      margin-top: 1rem;
    }
    .edit-delete-actions a {
      flex: 1;
      padding: 0.75rem 1.5rem;
      border-radius: 6px;
      text-decoration: none;
      text-align: center;
      font-weight: 600;
      transition: all 0.3s ease;
      color: white;
    }
    .edit-delete-actions .edit-btn {
      background: #28a745;
    }
    .edit-delete-actions .edit-btn:hover {
      background: #218838;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }
    .edit-delete-actions .delete-btn {
      background: #dc3545;
    }
    .edit-delete-actions .delete-btn:hover {
      background: #c82333;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }
    @media (max-width: 768px) {
      .course-view-container {
        padding: 1rem;
      }
      .course-info {
        padding: 1.5rem;
      }
      .course-info h1 {
        font-size: 1.5rem;
      }
      .course-actions {
        flex-direction: column;
        gap: 0.75rem;
      }
      .btn {
        width: 100%;
      }
      .edit-delete-actions {
        flex-direction: column;
      }
      .edit-delete-actions a {
        flex: auto;
      }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>
  
  <div class="course-view-container">
    <div class="back-link">
      <a href="index.php?field=<?= htmlspecialchars($course['field']) ?>">← Back to Courses</a>
    </div>

    <?php if ($embed_url): ?>
      <div class="video-container">
        <iframe src="<?= htmlspecialchars($embed_url) ?>" 
                allowfullscreen 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
        </iframe>
      </div>
    <?php else: ?>
      <div style="background:#f0f0f0; padding:40px; text-align:center; border-radius:8px; margin-bottom:30px;">
        <p style="color:#999;">⚠️ Could not embed video. <a href="<?= htmlspecialchars($course['url']) ?>" target="_blank">Open on YouTube</a></p>
      </div>
    <?php endif; ?>

    <div class="course-info">
      <h1><?= htmlspecialchars($course['title']) ?></h1>
      
      <div class="course-meta">
        <div class="meta-item">
          <strong>Duration</strong>
          <span><?= htmlspecialchars($course['duration']) ?></span>
        </div>
        <div class="meta-item">
          <strong>Provider</strong>
          <span><?= htmlspecialchars($course['provider']) ?></span>
        </div>
        <div class="meta-item">
          <strong>Field</strong>
          <span><?= htmlspecialchars($course['field']) ?></span>
        </div>
        <div class="meta-item">
          <strong>Status</strong>
          <span><?= ucfirst(htmlspecialchars($course['status'])) ?></span>
        </div>
      </div>

      <div class="course-actions">
        <a href="<?= htmlspecialchars($course['url']) ?>" class="btn btn-primary" target="_blank">🎬 Open on YouTube</a>
        <?php if ($role === 'learner'): ?>
          <button class="btn btn-bookmark<?= $bookmarked ? ' bookmarked' : '' ?>" 
                  data-course-id="<?= $course_id ?>"
                  onclick="toggleBookmark(this, <?= $course_id ?>)">
            <?= $bookmarked ? '⭐ Bookmarked' : '☆ Bookmark' ?>
          </button>
        <?php endif; ?>
      </div>
        
        <?php if ($can_edit_delete): ?>
          <div class="edit-delete-actions">
            <a href="edit_course.php?id=<?= $course_id ?>" class="edit-btn">✏️ Edit Course</a>
            <a href="delete_course.php?id=<?= $course_id ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this course? This action cannot be undone.');">🗑️ Delete Course</a>
          </div>
        <?php endif; ?>
    </div>
  </div>

  <script src="assets/js/scripts.js"></script>
  <script>
    function toggleBookmark(btn, courseId) {
      const isBookmarked = btn.classList.contains('bookmarked');
      const action = isBookmarked ? 'remove' : 'add';

      fetch('bookmark.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `course_id=${courseId}&action=${action}`
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            if (data.status === 'bookmarked') {
              btn.classList.add('bookmarked');
              btn.textContent = '✓ Bookmarked';
            } else {
              btn.classList.remove('bookmarked');
              btn.textContent = 'Bookmark';
            }
          } else {
            alert('Error: ' + data.error);
          }
        })
        .catch(err => console.error('AJAX error:', err));
    }
  </script>
</body>
</html>



