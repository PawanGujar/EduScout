<?php
// login_debug.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/config/config.php';

function dd($label, $val) {
    echo "<pre><strong>$label</strong> = ";
    var_dump($val);
    echo "</pre>";
}

echo "<h3>DEBUG LOGIN</h3>";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<form method="POST">
            <label>Email</label><br>
            <input name="email" type="email" required><br><br>
            <label>Password</label><br>
            <input name="password" type="password" required><br><br>
            <button type="submit">Test Login</button>
          </form>';
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = $_POST['password'] ?? '';

dd('POST', $_POST);
dd('email (trimmed)', '['.$email.'] len='.strlen($email));
dd('password length', strlen($password));

// 1) Check DB connection
dd('mysqli connect_errno', $mysqli->connect_errno);
dd('mysqli connect_error', $mysqli->connect_error);

// 2) Prepare statement
$sql = "SELECT id, username, password_hash, role FROM users WHERE email = ?";
dd('SQL', $sql);
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    dd('prepare() failed', $mysqli->error);
    exit;
}

$stmt->bind_param('s', $email);
$ok = $stmt->execute();
dd('execute()', $ok);
if (!$ok) {
    dd('execute error', $stmt->error);
    exit;
}

// 3) Fetch row (method A: get_result)
$result = $stmt->get_result();
if ($result === false) {
    dd('get_result() returned', $result);
    dd('Possible cause', 'mysqlnd missing; try bind_result() method below');
} else {
    dd('num_rows (get_result)', $result->num_rows);
}

$row = $result ? $result->fetch_assoc() : null;
dd('row (assoc)', $row);

// 4) If no row, stop here
if (!$row) {
    echo "<p style='color:red'>No user found for that email.</p>";
    exit;
}

// 5) Test password_verify
$hash = $row['password_hash'] ?? '';
dd('stored hash (first 20 chars)', substr($hash,0,20).'...');
$verify = password_verify($password, $hash);
dd('password_verify', $verify);

if ($verify) {
    echo "<p style='color:green'>✅ PASSWORD OK — would log in now.</p>";
    $_SESSION['user_id']  = $row['id'];
    $_SESSION['username'] = $row['username'];
    $_SESSION['role']     = $row['role'];
    echo "<p>Session set. <a href='index.php'>Go to index.php</a></p>";
} else {
    echo "<p style='color:red'>❌ Password mismatch.</p>";
}


