<?php
// edit_course.php — edit course details
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
$stmt = $mysqli->prepare("SELECT id, title, url, duration, provider, field, submitted_by FROM courses WHERE id = ?");
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

// Check permissions: only admin or the submitter can edit
if ($role !== 'admin' && $course['submitted_by'] != $userId) {
    http_response_code(403);
    die('You do not have permission to edit this course.');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $url = trim($_POST['url']);
    $duration = trim($_POST['duration']);
    $provider = trim($_POST['provider']);
    $field = trim($_POST['field']);

    if ($title && $url && $duration && $provider && $field) {
        $stmt = $mysqli->prepare("UPDATE courses SET title=?, url=?, duration=?, provider=?, field=? WHERE id=?");
        if (!$stmt) {
            error_log('Edit course prepare failed: ' . $mysqli->error);
            $message = "Server error: " . htmlspecialchars($mysqli->error);
        } else {
            $stmt->bind_param('sssssi', $title, $url, $duration, $provider, $field, $course_id);
            if ($stmt->execute()) {
                $message = "✅ Course updated successfully.";
                // Refresh course data
                $course['title'] = $title;
                $course['url'] = $url;
                $course['duration'] = $duration;
                $course['provider'] = $provider;
                $course['field'] = $field;
            } else {
                error_log('Edit course execute failed: ' . $stmt->error);
                $message = "Error: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        }
    } else {
        $message = "⚠ Please fill all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Course - EduScout</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
  <style>
    .edit-form-container {
      max-width: 600px;
      margin: 40px auto;
      padding: 30px;
      background: #f9f9f9;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #333;
    }
    .form-group input,
    .form-group select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1em;
      box-sizing: border-box;
    }
    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: #007bff;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }
    .form-actions {
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
      display: inline-block;
      transition: all 0.3s ease;
    }
    .btn-primary {
      background: #007bff;
      color: white;
      flex: 1;
    }
    .btn-primary:hover {
      background: #0056b3;
    }
    .btn-secondary {
      background: #6c757d;
      color: white;
      flex: 1;
    }
    .btn-secondary:hover {
      background: #545b62;
    }
    .message {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 4px;
    }
    .message.success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .message.error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>
  
  <div class="edit-form-container">
    <h1>Edit Course</h1>
    
    <?php if ($message): ?>
      <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="edit_course.php?id=<?= $course_id ?>">
      <div class="form-group">
        <label>Course Title:</label>
        <input type="text" name="title" value="<?= htmlspecialchars($course['title']) ?>" required>
      </div>

      <div class="form-group">
        <label>YouTube URL:</label>
        <input type="url" name="url" value="<?= htmlspecialchars($course['url']) ?>" placeholder="https://www.youtube.com/watch?v=..." required>
      </div>

      <div class="form-group">
        <label>Duration:</label>
        <input type="text" name="duration" value="<?= htmlspecialchars($course['duration']) ?>" placeholder="e.g., 3 hours" required>
      </div>

      <div class="form-group">
        <label>Provider:</label>
        <input type="text" name="provider" value="<?= htmlspecialchars($course['provider']) ?>" placeholder="e.g., freeCodeCamp" required>
      </div>

      <div class="form-group">
        <label>Field:</label>
        <select name="field" required>
          <option value="">-- Select a field --</option>
          <option value="IT" <?= $course['field'] === 'IT' ? 'selected' : '' ?>>IT</option>
          <option value="Medical" <?= $course['field'] === 'Medical' ? 'selected' : '' ?>>Medical</option>
          <option value="Engineering" <?= $course['field'] === 'Engineering' ? 'selected' : '' ?>>Engineering</option>
          <option value="Arts" <?= $course['field'] === 'Arts' ? 'selected' : '' ?>>Arts</option>
        </select>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">✓ Update Course</button>
        <a href="course_view.php?id=<?= $course_id ?>" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</body>
</html>



