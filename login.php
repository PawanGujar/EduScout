<?php
// login.php
session_start();
require_once __DIR__ . '/config/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  $stmt = $mysqli->prepare("SELECT id, username, password_hash, role FROM users WHERE email = ?");
  if (!$stmt) {
    error_log('Prepare failed: ' . $mysqli->error);
    $error = 'Server error. Try again later.';
  } else {
    $stmt->bind_param('s', $email);
    if (!$stmt->execute()) {
      error_log('Execute failed: ' . $stmt->error);
      $error = 'Server error. Try again later.';
    } else {
      // Prefer get_result when available, but fall back to bind_result for environments without mysqlnd
      $result = $stmt->get_result();
      $row = null;
      if ($result !== false) {
        $row = $result->fetch_assoc();
      } else {
        // bind_result fallback
        $stmt->bind_result($id, $uname, $p_hash, $urole);
        if ($stmt->fetch()) {
          $row = [
            'id' => $id,
            'username' => $uname,
            'password_hash' => $p_hash,
            'role' => $urole
          ];
        }
      }

      if ($row) {
        $password_hash = $row['password_hash'];
        $password_ok = false;
        
        // Support both bcrypt/argon2 hashes and legacy plain-text passwords
        if (strpos($password_hash, '$') === 0) {
          // Modern hash (starts with $ — bcrypt, argon2, etc.)
          $password_ok = password_verify($password, $password_hash);
          if ($password_ok) {
            error_log('Login success (hash verified): ' . $email);
          } else {
            error_log('Login failed (hash mismatch): ' . $email);
          }
        } else {
          // Legacy plain-text password (backward compatibility)
          $password_ok = ($password === $password_hash);
          if ($password_ok) {
            error_log('Login success (legacy plain-text): ' . $email . ' — consider re-hashing');
          } else {
            error_log('Login failed (plain-text mismatch): ' . $email);
          }
        }
        
        if ($password_ok) {
          session_regenerate_id(true);
          $_SESSION['user_id'] = $row['id'];
          $_SESSION['username'] = $row['username'];
          $_SESSION['role'] = $row['role'];

          header('Location: index.php');
          exit;
        } else {
          $error = "Invalid email or password.";
        }
      } else {
        error_log('Login failed (user not found): ' . $email);
        $error = "Invalid email or password.";
      }
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>EduScout Login</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 20px;
    }
    .login-wrapper {
      width: 100%;
      max-width: 900px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
    }
    .login-intro {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 60px 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
    }
    .login-intro .logo {
      font-size: 4em;
      margin-bottom: 20px;
    }
    .login-intro h1 {
      font-size: 2.2em;
      margin-bottom: 10px;
      font-weight: 700;
    }
    .login-intro p {
      font-size: 1.05em;
      opacity: 0.9;
      line-height: 1.6;
      margin-bottom: 20px;
    }
    .login-intro .features {
      text-align: left;
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-top: 30px;
      font-size: 0.95em;
    }
    .login-intro .features li {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .login-intro .features li:before {
      content: "✓";
      font-weight: bold;
      font-size: 1.2em;
    }
    .login-container {
      background: white;
      padding: 50px 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .login-container h2 {
      color: #333;
      margin-bottom: 10px;
      font-size: 1.8em;
      font-weight: 700;
    }
    .login-subtitle {
      color: #999;
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
    .form-group input {
      width: 100%;
      padding: 12px;
      border: 2px solid #e0e0e0;
      border-radius: 6px;
      font-size: 1em;
      box-sizing: border-box;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .form-group input:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    .login-button {
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
    .login-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }
    .login-button:active {
      transform: translateY(0);
    }
    .error-message {
      background: #f8d7da;
      color: #721c24;
      padding: 12px 15px;
      border-radius: 6px;
      margin-bottom: 20px;
      border: 1px solid #f5c6cb;
      font-size: 0.9em;
    }
    .auth-links {
      text-align: center;
      margin-top: 25px;
      color: #666;
      font-size: 0.95em;
      border-top: 1px solid #eee;
      padding-top: 20px;
    }
    .auth-links a {
      color: #667eea;
      text-decoration: none;
      font-weight: 600;
    }
    .auth-links a:hover {
      text-decoration: underline;
    }
    @media (max-width: 768px) {
      .login-wrapper {
        grid-template-columns: 1fr;
      }
      .login-intro {
        padding: 40px 30px;
      }
      .login-container {
        padding: 40px 30px;
      }
    }
  </style>
</head>
<body>
  <div class="login-wrapper">
    <!-- Left side: Intro -->
    <div class="login-intro">
      <div class="logo">🧠</div>
      <h1>EduScout</h1>
      <p>Curated Free Learning for Focused Growth</p>
      
      <ul class="features">
        <li>Browse curated courses</li>
        <li>Learn without distractions</li>
        <li>Bookmark your favorites</li>
        <li>Submit educational content</li>
      </ul>
    </div>

    <!-- Right side: Login Form -->
    <div class="login-container">
      <h2>Welcome Back</h2>
      <p class="login-subtitle">Sign in to your account</p>

      <?php if ($error): ?>
        <div class="error-message">
          ❌ <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" required placeholder="you@example.com" autofocus>
        </div>

        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required placeholder="••••••••">
        </div>

        <button type="submit" class="login-button">Sign In</button>
      </form>

      <div class="auth-links">
        New to EduScout? <a href="signup.php">Create an account</a>
      </div>
    </div>
  </div>
</body>
</html>

