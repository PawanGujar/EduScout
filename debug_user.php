<?php
// debug_user.php
// Temporary debug endpoint — only accessible from localhost.
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/config/config.php';

$email = isset($_GET['email']) ? trim($_GET['email']) : '';
if (!$email) {
    echo "Usage: debug_user.php?email=you@example.com\n";
    exit;
}

$stmt = $mysqli->prepare("SELECT id, email, username, password_hash, role FROM users WHERE email = ?");
if (!$stmt) {
    echo "Prepare failed: " . $mysqli->error . "\n";
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();

$result = $stmt->get_result();
$row = null;
if ($result !== false) {
    $row = $result->fetch_assoc();
} else {
    $stmt->bind_result($id, $e, $u, $ph, $r);
    if ($stmt->fetch()) {
        $row = ['id'=>$id, 'email'=>$e, 'username'=>$u, 'password_hash'=>$ph, 'role'=>$r];
    }
}

header('Content-Type: text/plain');
if ($row) {
    print_r($row);
} else {
    echo "No user found for: $email\n";
}

$stmt->close();

?>


