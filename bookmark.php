<?php
// bookmark.php
session_start();
require_once 'config.php';

// Only logged-in learners can bookmark
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId   = $_SESSION['user_id'];
$courseId = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
$action   = isset($_POST['action']) ? $_POST['action'] : '';

if ($courseId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid course']);
    exit;
}

if ($action === 'add') {
    // Insert bookmark if not already there
    $stmt = $mysqli->prepare("INSERT IGNORE INTO bookmarks (user_id, course_id, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param('ii', $userId, $courseId);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true, 'status' => 'bookmarked']);
    exit;

} elseif ($action === 'remove') {
    // Remove bookmark
    $stmt = $mysqli->prepare("DELETE FROM bookmarks WHERE user_id = ? AND course_id = ?");
    $stmt->bind_param('ii', $userId, $courseId);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true, 'status' => 'removed']);
    exit;

} else {
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
    exit;
}
