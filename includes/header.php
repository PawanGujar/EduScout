<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Grab user info if logged in
$isLoggedIn = isset($_SESSION['user_id']);
$username   = $isLoggedIn ? $_SESSION['username'] : null;
$role       = $isLoggedIn ? $_SESSION['role'] : null;
?>
<header class="site-header">
  <h1>EduScout</h1>
  <div class="hamburger">
    <span></span>
    <span></span>
    <span></span>
  </div>
  <nav>
    <ul>
      <?php if ($isLoggedIn): ?>
        <li><a href="index.php" <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'class="active"' : '' ?>>Home</a></li>

        <?php if ($role === 'learner'): ?>
          <li><a href="bookmarks.php" <?= basename($_SERVER['PHP_SELF']) === 'bookmarks.php' ? 'class="active"' : '' ?>>My Bookmarks</a></li>
        <?php endif; ?>

        <li><a href="profile.php" <?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'class="active"' : '' ?>>
          Profile (<?= htmlspecialchars($username) ?>)
        </a></li>

        <?php if ($role === 'editor' || $role === 'admin'): ?>
          <li><a href="submit_course.php" <?= basename($_SERVER['PHP_SELF']) === 'submit_course.php' ? 'class="active"' : '' ?>>Submit Course</a></li>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
          <li><a href="review_courses.php" <?= basename($_SERVER['PHP_SELF']) === 'review_courses.php' ? 'class="active"' : '' ?>>Review Courses</a></li>
          <li><a href="manage_categories.php" <?= basename($_SERVER['PHP_SELF']) === 'manage_categories.php' ? 'class="active"' : '' ?>>Manage Categories</a></li>
        <?php endif; ?>

        <li><a href="logout.php">Logout</a></li>

      <?php else: ?>
        <li><a href="login.php" <?= basename($_SERVER['PHP_SELF']) === 'login.php' ? 'class="active"' : '' ?>>Login</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>
<script src="assets/js/scripts.js"></script>
