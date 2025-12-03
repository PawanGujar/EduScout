<?php
// signup.php — user registration/signup page
session_start();
require_once __DIR__ . '/config/config.php';

// If already logged in, redirect to home
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $role = $_POST['role'] ?? 'learner'; // Default role is learner

    // Validate input
    $errors = [];

    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters long.';
    } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, underscores, and hyphens.';
    }

    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if ($password !== $password_confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!in_array($role, ['learner', 'editor'])) {
        $role = 'learner';
    }

    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    } else {
        // Check if username or email already exists
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        if (!$stmt) {
            $error = 'Database error: ' . htmlspecialchars($mysqli->error);
        } else {
            $stmt->bind_param('ss', $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            
            if ($result->num_rows > 0) {
                $error = 'Username or email already exists.';
            } else {
                // Hash password and insert user
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt2 = $mysqli->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
                if (!$stmt2) {
                    $error = 'Database error: ' . htmlspecialchars($mysqli->error);
                } else {
                    $stmt2->bind_param('ssss', $username, $email, $password_hash, $role);
                    if ($stmt2->execute()) {
                        error_log('New user registered: ' . $email . ' as ' . $role);
                        $success = '✅ Account created successfully! <a href="login.php">Click here to login</a>';
                    } else {
                        error_log('Signup execute failed: ' . $stmt2->error);
                        $error = 'Error creating account: ' . htmlspecialchars($stmt2->error);
                    }
                    $stmt2->close();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sign Up - EduScout</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .signup-container {
      background: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
      max-width: 450px;
      width: 100%;
      margin: 30px;
    }
    .signup-container h1 {
      text-align: center;
      color: #333;
      margin-bottom: 10px;
      font-size: 2em;
    }
    .signup-subtitle {
      text-align: center;
      color: #666;
      margin-bottom: 30px;
      font-size: 0.95em;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #333;
      font-size: 0.95em;
    }
    .form-group input,
    .form-group select {
      width: 100%;
      padding: 12px;
      border: 2px solid #e0e0e0;
      border-radius: 6px;
      font-size: 1em;
      box-sizing: border-box;
      transition: border-color 0.3s ease;
    }
    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    .form-group input[type="password"] {
      font-family: 'Segoe UI', sans-serif;
      letter-spacing: 0.1em;
    }
    .role-info {
      background: #f0f7ff;
      padding: 12px;
      border-left: 4px solid #667eea;
      border-radius: 4px;
      font-size: 0.85em;
      color: #555;
      margin-top: 8px;
      line-height: 1.5;
    }
    .password-requirements {
      background: #fffbf0;
      padding: 12px;
      border-left: 4px solid #ffc107;
      border-radius: 4px;
      font-size: 0.85em;
      color: #666;
      margin-top: 8px;
      line-height: 1.5;
    }
    .message {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 6px;
      font-size: 0.95em;
    }
    .message.error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    .message.success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .message a {
      color: inherit;
      font-weight: bold;
      text-decoration: underline;
    }
    .signup-button {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 1em;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      margin-top: 10px;
    }
    .signup-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }
    .signup-button:active {
      transform: translateY(0);
    }
    .login-link {
      text-align: center;
      margin-top: 20px;
      color: #666;
      font-size: 0.95em;
    }
    .login-link a {
      color: #667eea;
      text-decoration: none;
      font-weight: 600;
    }
    .login-link a:hover {
      text-decoration: underline;
    }
    .logo {
      text-align: center;
      font-size: 2.5em;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="signup-container">
    <div class="logo">🧠</div>
    <h1>EduScout</h1>
    <p class="signup-subtitle">Create your account</p>

    <?php if ($error): ?>
      <div class="message error">
        <?= $error ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="message success">
        <?= $success ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="signup.php">
      <div class="form-group">
        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required 
               placeholder="johndoe">
        <div style="font-size: 0.85em; color: #999; margin-top: 5px;">
          3+ characters, letters/numbers/- only
        </div>
      </div>

      <div class="form-group">
        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required
               placeholder="you@example.com">
      </div>

      <div class="form-group">
        <label>Password:</label>
        <input type="password" name="password" required placeholder="••••••••">
        <div class="password-requirements">
          ℹ️ <strong>Requirements:</strong> At least 6 characters. Use a mix of letters, numbers, and symbols for better security.
        </div>
      </div>

      <div class="form-group">
        <label>Confirm Password:</label>
        <input type="password" name="password_confirm" required placeholder="••••••••">
      </div>

      <div class="form-group">
        <label>Role:</label>
        <select name="role">
          <option value="learner" selected>Learner (Student)</option>
          <option value="editor">Editor (Content Curator)</option>
        </select>
        <div class="role-info">
          <strong>Learner:</strong> Browse and bookmark curated courses<br>
          <strong>Editor:</strong> Submit and manage your own courses
        </div>
      </div>

      <button type="submit" class="signup-button">Create Account</button>
    </form>

    <div class="login-link">
      Already have an account? <a href="login.php">Login here</a>
    </div>
  </div>
</body>
</html>



