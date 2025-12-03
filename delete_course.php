<?php
// delete_course.php — delete a course with confirmation
session_start();
require_once __DIR__ . '/config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($course_id <= 0) {
    die('Invalid course ID.');
}

// Fetch the course
$stmt = $mysqli->prepare("SELECT id, title, submitted_by FROM courses WHERE id = ?");
if (!$stmt) {
    die('Database error: ' . $mysqli->error);
}
$stmt->bind_param('i', $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    die('Course not found.');
}

// Check permissions: only admin or the submitter can delete
if ($role !== 'admin' && $course['submitted_by'] != $userId) {
    http_response_code(403);
    die('You do not have permission to delete this course.');
}

// Handle delete confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Delete bookmarks first (due to foreign key constraint)
    $stmt = $mysqli->prepare("DELETE FROM bookmarks WHERE course_id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $course_id);
        $stmt->execute();
        $stmt->close();
    }

    // Delete the course
    $stmt = $mysqli->prepare("DELETE FROM courses WHERE id = ?");
    if (!$stmt) {
        error_log('Delete course prepare failed: ' . $mysqli->error);
        die('Database error: ' . $mysqli->error);
    }
    $stmt->bind_param('i', $course_id);
    if ($stmt->execute()) {
        $stmt->close();
        header('Location: index.php?deleted=1');
        exit;
    } else {
        error_log('Delete course execute failed: ' . $stmt->error);
        die('Error deleting course: ' . $stmt->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Delete Course - EduScout</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
  <style>
    .delete-container {
      max-width: 500px;
      margin: 60px auto;
      padding: 30px;
      background: #fff5f5;
      border: 2px solid #f08080;
      border-radius: 8px;
      text-align: center;
    }
    .delete-container h1 {
      color: #c41e3a;
      margin-bottom: 20px;
    }
    .delete-container p {
      color: #555;
      font-size: 1.1em;
      margin-bottom: 15px;
      line-height: 1.6;
    }
    .course-title {
      background: #ffe0e0;
      padding: 15px;
      border-radius: 4px;
      font-weight: bold;
      color: #333;
      margin: 20px 0;
    }
    .warning {
      background: #fff3cd;
      color: #856404;
      padding: 15px;
      border-radius: 4px;
      margin: 20px 0;
      border-left: 4px solid #ffc107;
    }
    .button-group {
      display: flex;
      gap: 10px;
      margin-top: 30px;
    }
    .btn {
      padding: 12px 24px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 1em;
      font-weight: bold;
      text-decoration: none;
      flex: 1;
      transition: all 0.3s ease;
    }
    .btn-danger {
      background: #c41e3a;
      color: white;
    }
    .btn-danger:hover {
      background: #a01830;
    }
    .btn-cancel {
      background: #6c757d;
      color: white;
    }
    .btn-cancel:hover {
      background: #545b62;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>
  
  <div class="delete-container">
    <h1>⚠️ Delete Course</h1>
    
    <p>Are you sure you want to delete this course?</p>
    
    <div class="course-title">
      <?= htmlspecialchars($course['title']) ?>
    </div>

    <div class="warning">
      <strong>⚠️ Warning:</strong> This action cannot be undone. All bookmarks for this course will also be deleted.
    </div>

    <form method="POST" action="delete_course.php?id=<?= $course_id ?>">
      <div class="button-group">
        <button type="submit" name="confirm_delete" value="1" class="btn btn-danger">🗑️ Delete Course</button>
        <a href="course_view.php?id=<?= $course_id ?>" class="btn btn-cancel">Cancel</a>
      </div>
    </form>
  </div>
</body>
</html>



