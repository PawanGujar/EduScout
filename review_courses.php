<?php
// review_courses.php
session_start();
require_once __DIR__ . '/config/config.php';

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
$result = $mysqli->query("SELECT c.id, c.title, c.url, c.duration, c.provider, c.field, u.username 
                          FROM courses c 
                          JOIN users u ON c.submitted_by = u.id
                          WHERE c.status='pending'");
if (!$result) {
    error_log('Review courses query failed: ' . $mysqli->error);
    echo "<div style='color:red'><strong>Database error:</strong> " . htmlspecialchars($mysqli->error) . "</div>";
    $result = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Review Courses - EduScout</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
  <style>
    .review-container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 2rem;
    }

    .review-header {
      margin-bottom: 2rem;
    }

    .review-header h1 {
      color: #333;
      margin: 0 0 0.5rem 0;
      font-size: 2rem;
    }

    .review-header p {
      color: #666;
      margin: 0;
      font-size: 0.95rem;
    }

    .review-table-wrapper {
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .review-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.95rem;
    }

    .review-table thead {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      font-weight: 600;
      position: sticky;
      top: 0;
      z-index: 10;
    }

    .review-table th {
      padding: 1.25rem 1rem;
      text-align: left;
      border-bottom: 2px solid #764ba2;
      white-space: nowrap;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .review-table tbody tr {
      border-bottom: 1px solid #e0e0e0;
      transition: background 0.3s ease;
    }

    .review-table tbody tr:hover {
      background: #f9f9f9;
    }

    .review-table tbody tr:last-child {
      border-bottom: none;
    }

    .review-table td {
      padding: 1rem;
      vertical-align: middle;
      color: #333;
    }

    .course-title {
      font-weight: 600;
      color: #667eea;
      max-width: 250px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .course-title:hover {
      text-decoration: underline;
    }

    .badge {
      display: inline-block;
      padding: 0.4rem 0.8rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      white-space: nowrap;
    }

    .badge-field {
      background: #f0f7ff;
      color: #667eea;
      border: 1px solid #667eea;
    }

    .badge-user {
      background: #f0f0f0;
      color: #555;
    }

    .youtube-link {
      color: #dc3545;
      text-decoration: none;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
    }

    .youtube-link:hover {
      text-decoration: underline;
      color: #c82333;
    }

    .action-buttons {
      display: flex;
      gap: 0.75rem;
      flex-wrap: wrap;
    }

    .approve-btn,
    .reject-btn {
      padding: 0.6rem 1rem;
      border: none;
      border-radius: 6px;
      font-size: 0.85rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      transition: all 0.3s ease;
      white-space: nowrap;
    }

    .approve-btn {
      background: #28a745;
      color: white;
    }

    .approve-btn:hover {
      background: #218838;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }

    .reject-btn {
      background: #dc3545;
      color: white;
    }

    .reject-btn:hover {
      background: #c82333;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }

    .empty-state {
      padding: 4rem 2rem;
      text-align: center;
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .empty-state-icon {
      font-size: 3rem;
      margin-bottom: 1rem;
    }

    .empty-state h2 {
      color: #999;
      margin: 0 0 0.5rem 0;
    }

    .empty-state p {
      color: #999;
      margin: 0;
    }

    .pending-count {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.9rem;
      display: inline-block;
      margin-top: 0.5rem;
    }

    /* Mobile Responsive */
    @media (max-width: 1024px) {
      .review-container {
        padding: 1.5rem;
      }

      .review-table {
        font-size: 0.9rem;
      }

      .review-table th,
      .review-table td {
        padding: 0.75rem 0.6rem;
      }

      .course-title {
        max-width: 180px;
      }

      .action-buttons {
        flex-direction: column;
        gap: 0.5rem;
      }

      .approve-btn,
      .reject-btn {
        width: 100%;
        justify-content: center;
      }
    }

    @media (max-width: 768px) {
      .review-container {
        padding: 1rem;
      }

      .review-header h1 {
        font-size: 1.5rem;
      }

      .review-table-wrapper {
        overflow-x: auto;
      }

      .review-table {
        font-size: 0.85rem;
        min-width: 900px;
      }

      .review-table th,
      .review-table td {
        padding: 0.75rem 0.5rem;
      }

      .course-title {
        max-width: 150px;
        font-size: 0.9rem;
      }

      .badge {
        font-size: 0.8rem;
        padding: 0.3rem 0.6rem;
      }

      .approve-btn,
      .reject-btn {
        padding: 0.5rem 0.8rem;
        font-size: 0.8rem;
      }

      .youtube-link {
        font-size: 0.85rem;
      }
    }

    @media (max-width: 480px) {
      .review-container {
        padding: 0.75rem;
      }

      .review-header h1 {
        font-size: 1.3rem;
      }

      .review-table {
        font-size: 0.8rem;
        min-width: 800px;
      }

      .review-table th,
      .review-table td {
        padding: 0.6rem 0.4rem;
      }

      .course-title {
        max-width: 120px;
        font-size: 0.85rem;
      }

      .badge {
        font-size: 0.75rem;
      }

      .approve-btn,
      .reject-btn {
        padding: 0.4rem 0.6rem;
        font-size: 0.75rem;
      }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="review-container">
    <div class="review-header">
      <h1>📝 Review Submitted Courses</h1>
      <?php if ($result && $result->num_rows > 0): ?>
        <span class="pending-count"><?= $result->num_rows ?> pending course<?= $result->num_rows !== 1 ? 's' : '' ?></span>
      <?php endif; ?>
    </div>

    <?php if ($message): ?>
      <div class="<?= strpos($message, '✅') === 0 ? 'success-message' : 'error-message' ?>" style="margin-bottom: 2rem;">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <?php if ($result && $result->num_rows > 0): ?>
      <div class="review-table-wrapper">
        <table class="review-table">
          <thead>
            <tr>
              <th>📖 Title</th>
              <th>⏱️ Duration</th>
              <th>📺 Provider</th>
              <th>📂 Field</th>
              <th>👤 Submitted By</th>
              <th>🎬 YouTube</th>
              <th style="text-align: center;">⚡ Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td>
                  <span class="course-title" title="<?= htmlspecialchars($row['title']) ?>">
                    <?= htmlspecialchars($row['title']) ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($row['duration']) ?></td>
                <td><?= htmlspecialchars($row['provider']) ?></td>
                <td>
                  <span class="badge badge-field">
                    <?= htmlspecialchars($row['field']) ?>
                  </span>
                </td>
                <td>
                  <span class="badge badge-user">
                    <?= htmlspecialchars($row['username']) ?>
                  </span>
                </td>
                <td>
                  <a href="<?= htmlspecialchars($row['url']) ?>" target="_blank" class="youtube-link">
                    🔗 Open
                  </a>
                </td>
                <td>
                  <div class="action-buttons">
                    <a href="review_courses.php?action=approve&id=<?= $row['id'] ?>" class="approve-btn">
                      ✅ Approve
                    </a>
                    <a href="review_courses.php?action=reject&id=<?= $row['id'] ?>" class="reject-btn">
                      ❌ Reject
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <div class="empty-state-icon">✅</div>
        <h2>All Caught Up!</h2>
        <p>No pending courses to review. All courses have been reviewed.</p>
      </div>
    <?php endif; ?>
  </div>

  <script src="assets/js/scripts.js"></script>
</body>
</html>



