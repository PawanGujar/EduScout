<?php
// submit_course.php
session_start();
require_once __DIR__ . '/config/config.php';

// Only allow editors or admins
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['editor','admin'])) {
    header('Location: login.php');
    exit;
}

$message = '';

// Fetch categories from database
$categories = [];
$stmt = $mysqli->prepare("SELECT name FROM categories ORDER BY created_at ASC");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['name'];
    }
    $stmt->close();
}

// If no categories, use defaults
if (empty($categories)) {
    $categories = ['IT', 'Medical', 'Engineering', 'Arts'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $url = trim($_POST['url']);
    $duration = trim($_POST['duration']);
    $provider = trim($_POST['provider']);
    $field = trim($_POST['field']);
    $submitted_by = $_SESSION['user_id'];

    if ($title && $url && $duration && $provider && $field) {
        $stmt = $mysqli->prepare("INSERT INTO courses (title, url, duration, provider, field, status, submitted_by) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
        if (!$stmt) {
            error_log('Submit course prepare failed: ' . $mysqli->error);
            $message = "❌ Server error: " . htmlspecialchars($mysqli->error);
        } else {
            $stmt->bind_param('sssssi', $title, $url, $duration, $provider, $field, $submitted_by);
            if ($stmt->execute()) {
                $message = "✅ Course submitted for review. Admins will review and publish it soon!";
            } else {
                error_log('Submit course execute failed: ' . $stmt->error);
                $message = "❌ Error: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        }
    } else {
        $message = "⚠️ Please fill all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Submit Course - EduScout</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
  <style>
    .submit-form-container {
      max-width: 700px;
      margin: 2rem auto;
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .submit-form-container h2 {
      color: #333;
      margin-bottom: 0.5rem;
      font-size: 1.8rem;
    }

    .submit-form-container .subtitle {
      color: #666;
      margin-bottom: 1.5rem;
      font-size: 0.95rem;
    }

    .info-box {
      background: linear-gradient(135deg, #f0f7ff 0%, #e6f2ff 100%);
      border-left: 5px solid #667eea;
      padding: 1.25rem;
      border-radius: 6px;
      margin-bottom: 2rem;
      font-size: 0.95rem;
      color: #555;
      line-height: 1.6;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
    }

    .form-row.full {
      grid-template-columns: 1fr;
    }

    .submit-form-container form {
      display: flex;
      flex-direction: column;
      gap: 0;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group label {
      display: block;
      font-weight: 700;
      margin-bottom: 0.65rem;
      color: #333;
      font-size: 0.95rem;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 0.85rem;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-size: 1rem;
      font-family: inherit;
      background: white;
      transition: all 0.3s ease;
    }

    .form-group input::placeholder {
      color: #999;
    }

    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
      background: #fafbff;
    }

    .form-group small {
      display: block;
      margin-top: 0.5rem;
      font-size: 0.8rem;
      color: #999;
      font-weight: 500;
    }

    .submit-form-container .btn {
      width: 100%;
      padding: 0.9rem 1.5rem;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 1.05rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 0.5rem;
    }

    .submit-form-container .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
    }

    .submit-form-container .btn:active {
      transform: translateY(-1px);
    }

    .footer-section {
      margin-top: 2.5rem;
      padding-top: 2rem;
      border-top: 2px solid #f0f0f0;
      text-align: center;
      color: #666;
      font-size: 0.9rem;
    }

    .footer-section p {
      margin: 0.5rem 0;
    }

    .footer-section a {
      color: #667eea;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s ease;
    }

    .footer-section a:hover {
      color: #764ba2;
      text-decoration: underline;
    }

    @media (max-width: 768px) {
      .submit-form-container {
        margin: 1rem;
        padding: 1.5rem;
        border-radius: 8px;
      }

      .submit-form-container h2 {
        font-size: 1.5rem;
      }

      .form-row {
        grid-template-columns: 1fr;
        gap: 1.25rem;
      }

      .info-box {
        padding: 1rem;
        margin-bottom: 1.5rem;
      }
    }

    @media (max-width: 500px) {
      .submit-form-container {
        margin: 0.75rem;
        padding: 1.25rem;
      }

      .submit-form-container h2 {
        font-size: 1.3rem;
        margin-bottom: 0.25rem;
      }

      .submit-form-container .subtitle {
        margin-bottom: 1.25rem;
        font-size: 0.9rem;
      }

      .form-group input,
      .form-group select {
        padding: 0.75rem;
        font-size: 16px;
      }

      .form-group label {
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
      }

      .form-group small {
        font-size: 0.75rem;
      }

      .submit-form-container .btn {
        padding: 0.8rem 1.25rem;
        font-size: 1rem;
      }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="submit-form-container">
    <h2>📚 Submit a Course</h2>
    <p class="subtitle">Share quality educational content with the community</p>

    <div class="info-box">
      ℹ️ <strong>Share Quality Content:</strong> Submit educational YouTube videos or playlists for your field. All submissions are reviewed by admins before publishing.
    </div>

    <?php if ($message): ?>
      <div class="<?= strpos($message, '✅') === 0 ? 'success-message' : 'error-message' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="submit_course.php">
      <div class="form-row full">
        <div class="form-group">
          <label for="title">📖 Course Title</label>
          <input type="text" id="title" name="title" required placeholder="e.g., Complete Python Beginner to Advanced" 
                 value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
          <small>Clear, descriptive title (50-100 characters)</small>
        </div>
      </div>

      <div class="form-row full">
        <div class="form-group">
          <label for="url">🎥 YouTube URL</label>
          <input type="url" id="url" name="url" placeholder="https://www.youtube.com/watch?v=..." required
                 value="<?= htmlspecialchars($_POST['url'] ?? '') ?>">
          <small>Video or Playlist link</small>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="duration">⏱️ Duration</label>
          <input type="text" id="duration" name="duration" placeholder="e.g., 12 hours, 24 videos" required
                 value="<?= htmlspecialchars($_POST['duration'] ?? '') ?>">
          <small>Total length of course</small>
        </div>

        <div class="form-group">
          <label for="provider">📺 Provider/Creator</label>
          <input type="text" id="provider" name="provider" placeholder="e.g., freeCodeCamp" required
                 value="<?= htmlspecialchars($_POST['provider'] ?? '') ?>">
          <small>Creator or channel name</small>
        </div>
      </div>

      <div class="form-row full">
        <div class="form-group">
          <label for="field">📂 Category/Field</label>
          <select id="field" name="field" required>
            <option value="">-- Select a category --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>" <?= (isset($_POST['field']) && $_POST['field'] === $cat) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <small>Which category best fits this course?</small>
        </div>
      </div>

      <button type="submit" class="btn">🚀 Submit for Review</button>
    </form>

    <div class="footer-section">
      <p>All courses are reviewed by admins before becoming visible to learners.</p>
      <p><a href="index.php">← Back to Home</a></p>
    </div>
  </div>

  <script src="assets/js/scripts.js"></script>
</body>
</html>



