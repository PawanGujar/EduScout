<?php
// manage_categories.php - Admin page to manage course categories
session_start();
require_once __DIR__ . '/config/config.php';

// Only admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo '403 - Access Denied';
    exit;
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $slug = trim(strtolower(preg_replace('/[^a-z0-9]+/', '-', $name)));
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? '📚');

        if (!$name) {
            $error = '❌ Category name is required.';
        } elseif (strlen($name) > 100) {
            $error = '❌ Category name must be 100 characters or less.';
        } else {
            $stmt = $mysqli->prepare("INSERT INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                $error = '❌ Database error: ' . htmlspecialchars($mysqli->error);
            } else {
                $stmt->bind_param('ssss', $name, $slug, $description, $icon);
                if ($stmt->execute()) {
                    $message = "✅ Category '$name' created successfully.";
                } else {
                    if (strpos($stmt->error, 'Duplicate entry') !== false) {
                        $error = "❌ Category '$name' already exists.";
                    } else {
                        $error = '❌ Error creating category: ' . htmlspecialchars($stmt->error);
                    }
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? '📚');

        if (!$id || !$name) {
            $error = '❌ Invalid input.';
        } else {
            $stmt = $mysqli->prepare("UPDATE categories SET name = ?, description = ?, icon = ? WHERE id = ?");
            $stmt->bind_param('sssi', $name, $description, $icon, $id);
            if ($stmt->execute()) {
                $message = "✅ Category updated successfully.";
            } else {
                $error = '❌ Error updating category: ' . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);

        if (!$id) {
            $error = '❌ Invalid category ID.';
        } else {
            // Check if category is used in any courses
            $stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM courses WHERE field = (SELECT name FROM categories WHERE id = ?)");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($result['count'] > 0) {
                $error = "❌ Cannot delete category: It has {$result['count']} course(s) assigned to it.";
            } else {
                $stmt = $mysqli->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->bind_param('i', $id);
                if ($stmt->execute()) {
                    $message = "✅ Category deleted successfully.";
                } else {
                    $error = '❌ Error deleting category: ' . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            }
        }
    }
}

// Fetch all categories
$categories = [];
$stmt = $mysqli->prepare("SELECT id, name, slug, description, icon, created_at FROM categories ORDER BY created_at DESC");
$stmt->execute();
$categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Categories - EduScout</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
  <style>
    .manage-categories {
      display: grid;
      grid-template-columns: 1fr 2fr;
      gap: 2rem;
      max-width: 1200px;
      margin: 2rem auto;
    }

    .category-form {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      height: fit-content;
    }

    .category-form h3 {
      margin-top: 0;
      color: #333;
    }

    .category-list {
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .category-item {
      display: grid;
      grid-template-columns: 60px 1fr auto;
      align-items: center;
      gap: 1.5rem;
      padding: 1.5rem;
      border-bottom: 1px solid #e0e0e0;
      transition: background 0.3s ease;
    }

    .category-item:last-child {
      border-bottom: none;
    }

    .category-item:hover {
      background: #f9f9f9;
    }

    .category-icon {
      font-size: 2rem;
      text-align: center;
    }

    .category-info h4 {
      margin: 0 0 0.25rem 0;
      color: #333;
    }

    .category-info p {
      margin: 0;
      font-size: 0.9rem;
      color: #666;
    }

    .category-info small {
      display: block;
      color: #999;
      margin-top: 0.3rem;
      font-size: 0.85rem;
    }

    .category-actions {
      display: flex;
      gap: 0.5rem;
    }

    .category-actions button {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.85rem;
      transition: all 0.3s ease;
    }

    .category-actions .edit-btn {
      background: #667eea;
      color: white;
    }

    .category-actions .delete-btn {
      background: #dc3545;
      color: white;
    }

    .category-actions button:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .icon-picker {
    .icon-picker {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(40px, 1fr));
      gap: 0.5rem;
      margin-top: 0.5rem;
    }

    .icon-picker button {
      padding: 0.75rem;
      border: 2px solid #e0e0e0;
      background: white;
      border-radius: 4px;
      cursor: pointer;
      font-size: 1.5rem;
      transition: all 0.3s ease;
    }

    .icon-picker button.selected {
      border-color: #667eea;
      background: #f0f7ff;
    }

    .icon-picker button:hover {
      border-color: #667eea;
    }

    .form-actions {
      display: flex;
      gap: 0.5rem;
      margin-top: 1rem;
    }

    .form-actions button {
      flex: 1;
    }

    .cancel-btn {
      background: #999;
      color: white;
    }

    .cancel-btn:hover {
      background: #777;
    }

    @media (max-width: 768px) {
      .manage-categories {
        grid-template-columns: 1fr;
        gap: 1rem;
        margin: 1rem auto;
      }

      .category-item {
        grid-template-columns: 50px 1fr;
        gap: 1rem;
      }

      .category-actions {
        grid-column: 1 / -1;
        margin-top: 1rem;
      }

      .category-actions button {
        flex: 1;
      }
      .icon-picker {
        grid-template-columns: repeat(auto-fit, minmax(36px, 1fr));
        gap: 0.35rem;
      }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="main-content" style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
    <h2>Manage Categories</h2>

    <?php if ($message): ?>
      <div class="success-message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="manage-categories">
      <!-- Add/Edit Form -->
      <div class="category-form">
        <h3>➕ New Category</h3>
        <form method="POST" action="manage_categories.php">
          <input type="hidden" name="action" value="add">

          <div class="form-group">
            <label for="name">Category Name:</label>
            <input type="text" id="name" name="name" required placeholder="e.g., Data Science" maxlength="100">
          </div>

          <div class="form-group">
            <label>Choose Icon:</label>
            <div class="icon-picker">
              <button type="button" class="icon-select selected" data-icon="📚">📚</button>
              <button type="button" class="icon-select" data-icon="💻">💻</button>
              <button type="button" class="icon-select" data-icon="🏥">🏥</button>
              <button type="button" class="icon-select" data-icon="⚙️">⚙️</button>
              <button type="button" class="icon-select" data-icon="🎨">🎨</button>
              <button type="button" class="icon-select" data-icon="📊">📊</button>
              <button type="button" class="icon-select" data-icon="🔬">🔬</button>
              <button type="button" class="icon-select" data-icon="🎓">🎓</button>
              <button type="button" class="icon-select" data-icon="🌐">🌐</button>
              <button type="button" class="icon-select" data-icon="🤖">🤖</button>
              <button type="button" class="icon-select" data-icon="📱">📱</button>
              <button type="button" class="icon-select" data-icon="🎬">🎬</button>
            </div>
            <input type="hidden" id="icon" name="icon" value="📚" required>
          </div>

          <div class="form-group">
            <label for="description">Description (Optional):</label>
            <textarea id="description" name="description" placeholder="Brief description of this category..."></textarea>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn">Create Category</button>
          </div>
        </form>
      </div>

      <!-- Categories List -->
      <div class="category-list">
        <h3 style="padding: 1.5rem 1.5rem 0; margin: 0;">📋 Existing Categories (<?= count($categories) ?>)</h3>
        
        <?php if (count($categories) > 0): ?>
          <?php foreach ($categories as $cat): ?>
            <div class="category-item">
              <div class="category-icon"><?= htmlspecialchars($cat['icon']) ?></div>
              <div class="category-info">
                <h4><?= htmlspecialchars($cat['name']) ?></h4>
                <p><?= htmlspecialchars($cat['description'] ?: 'No description') ?></p>
                <small>Slug: <code><?= htmlspecialchars($cat['slug']) ?></code></small>
              </div>
              <div class="category-actions">
                <form method="POST" action="manage_categories.php" style="display: inline;">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                  <button type="submit" class="delete-btn" onclick="return confirm('Delete this category? Courses must be reassigned first.');">Delete</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="padding: 2rem; text-align: center; color: #999;">No categories found. Create your first one!</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
    // Icon picker functionality
    document.querySelectorAll('.icon-select').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelectorAll('.icon-select').forEach(b => b.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('icon').value = this.getAttribute('data-icon');
      });
    });
  </script>
</body>
</html>



