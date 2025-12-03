<?php
// profile.php
session_start();
require_once __DIR__ . '/config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// Fetch user details
$stmt = $mysqli->prepare("SELECT username, email, role, created_at FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch user stats
$stats = ['courses_submitted' => 0, 'courses_approved' => 0, 'courses_bookmarked' => 0];

if ($_SESSION['role'] === 'editor' || $_SESSION['role'] === 'admin') {
    $stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM courses WHERE submitted_by = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stats['courses_submitted'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM courses WHERE submitted_by = ? AND status = 'approved'");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stats['courses_approved'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
}

if ($_SESSION['role'] === 'learner') {
    $stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM bookmarks WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stats['courses_bookmarked'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Profile - EduScout</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
  <style>
    .profile-container {
      max-width: 800px;
      margin: 2rem auto;
      display: grid;
      grid-template-columns: 1fr;
      gap: 2rem;
    }

    .profile-card {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .profile-header {
      display: flex;
      align-items: center;
      gap: 2rem;
      margin-bottom: 2rem;
    }

    .profile-avatar {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3.5rem;
      flex-shrink: 0;
    }

    .profile-info h1 {
      margin: 0 0 0.5rem 0;
      color: #333;
      font-size: 2rem;
    }

    .profile-info p {
      margin: 0.25rem 0;
      color: #666;
      font-size: 1rem;
    }

    .role-badge {
      display: inline-block;
      padding: 0.5rem 1rem;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-radius: 20px;
      font-size: 0.9rem;
      font-weight: 600;
      margin-top: 0.5rem;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      margin-top: 2rem;
    }

    .stat-card {
      background: linear-gradient(135deg, #f0f7ff 0%, #f9f0ff 100%);
      padding: 1.5rem;
      border-radius: 8px;
      border-left: 4px solid #667eea;
      text-align: center;
    }

    .stat-card h3 {
      margin: 0;
      color: #667eea;
      font-size: 2rem;
      font-weight: 700;
    }

    .stat-card p {
      margin: 0.5rem 0 0 0;
      color: #666;
      font-size: 0.95rem;
    }

    .details-section {
      margin-top: 2rem;
      border-top: 1px solid #e0e0e0;
      padding-top: 2rem;
    }

    .details-section h2 {
      color: #333;
      font-size: 1.3rem;
      margin-bottom: 1rem;
    }

    .detail-row {
      display: grid;
      grid-template-columns: 150px 1fr;
      gap: 1rem;
      padding: 1rem 0;
      border-bottom: 1px solid #f0f0f0;
    }

    .detail-row:last-child {
      border-bottom: none;
    }

    .detail-label {
      font-weight: 600;
      color: #667eea;
    }

    .detail-value {
      color: #333;
    }

    @media (max-width: 768px) {
      .profile-container {
        margin: 1rem;
        gap: 1rem;
      }

      .profile-card {
        padding: 1.5rem;
      }

      .profile-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
      }

      .profile-avatar {
        width: 100px;
        height: 100px;
        font-size: 3rem;
      }

      .profile-info h1 {
        font-size: 1.5rem;
      }

      .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
      }

      .detail-row {
        grid-template-columns: 1fr;
        gap: 0.5rem;
      }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="profile-container">
    <div class="profile-card">
      <div class="profile-header">
        <div class="profile-avatar">
          <?= $_SESSION['role'] === 'learner' ? '📚' : ($_SESSION['role'] === 'editor' ? '✏️' : '👨‍💼') ?>
        </div>
        <div class="profile-info">
          <h1><?= htmlspecialchars($user['username']) ?></h1>
          <p><?= htmlspecialchars($user['email']) ?></p>
          <span class="role-badge">
            <?= htmlspecialchars(ucfirst($user['role'])) ?>
            <?= $_SESSION['role'] === 'learner' ? '(Student)' : ($_SESSION['role'] === 'editor' ? '(Content Creator)' : '(Administrator)') ?>
          </span>
        </div>
      </div>

      <?php if (($stats['courses_submitted'] > 0 || $stats['courses_approved'] > 0 || $stats['courses_bookmarked'] > 0)): ?>
        <div class="stats-grid">
          <?php if ($_SESSION['role'] !== 'learner' && $stats['courses_submitted'] > 0): ?>
            <div class="stat-card">
              <h3><?= $stats['courses_submitted'] ?></h3>
              <p>Courses Submitted</p>
            </div>
          <?php endif; ?>

          <?php if ($_SESSION['role'] !== 'learner' && $stats['courses_approved'] > 0): ?>
            <div class="stat-card">
              <h3><?= $stats['courses_approved'] ?></h3>
              <p>Approved Courses</p>
            </div>
          <?php endif; ?>

          <?php if ($_SESSION['role'] === 'learner' && $stats['courses_bookmarked'] > 0): ?>
            <div class="stat-card">
              <h3><?= $stats['courses_bookmarked'] ?></h3>
              <p>Bookmarked Courses</p>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <div class="details-section">
        <h2>Account Details</h2>
        <div class="detail-row">
          <div class="detail-label">Username:</div>
          <div class="detail-value"><?= htmlspecialchars($user['username']) ?></div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Email:</div>
          <div class="detail-value"><?= htmlspecialchars($user['email']) ?></div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Role:</div>
          <div class="detail-value"><?= htmlspecialchars(ucfirst($user['role'])) ?></div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Member Since:</div>
          <div class="detail-value"><?= htmlspecialchars(date('M d, Y', strtotime($user['created_at']))) ?></div>
        </div>
      </div>
    </div>
  </div>

  <script src="assets/js/scripts.js"></script>
</body>
</html>



