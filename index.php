<?php
session_start();
include 'db.php';

$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$searchTerm = "%{$search}%";

// Count total matching posts
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM posts WHERE title LIKE ? OR content LIKE ?");
$count_stmt->bind_param("ss", $searchTerm, $searchTerm);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total = $count_result->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);

// Fetch posts with pagination and search
$post_stmt = $conn->prepare(
    "SELECT posts.*, users.username 
     FROM posts 
     JOIN users ON posts.user_id = users.id 
     WHERE title LIKE ? OR content LIKE ? 
     ORDER BY created_at DESC 
     LIMIT ? OFFSET ?"
);
$post_stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
$post_stmt->execute();
$result = $post_stmt->get_result();

// RBAC Setup
$is_logged_in = isset($_SESSION['user_id']);
$current_user_id = $_SESSION['user_id'] ?? 0;
$current_user_role = $_SESSION['role'] ?? 'guest';

function can_edit_post($post_user_id) {
    global $current_user_id, $current_user_role;
    return $current_user_role === 'admin' || $current_user_role === 'editor' || $post_user_id == $current_user_id;
}

function can_delete_post($post_user_id) {
    global $current_user_id, $current_user_role;
    return $current_user_role === 'admin' || ($current_user_role === 'user' && $post_user_id == $current_user_id);
}

// Fetch logs if admin
$logs_result = null;
if ($current_user_role === 'admin') {
    $log_stmt = $conn->prepare("SELECT logs.*, users.username FROM logs JOIN users ON logs.user_id = users.id ORDER BY logs.created_at DESC LIMIT 5");
    $log_stmt->execute();
    $logs_result = $log_stmt->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Blog Posts</title>
   
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <script>
    // Apply dark mode 
    if (localStorage.getItem('darkMode') === 'true') {
      document.documentElement.classList.add('dark');
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  function toggleReportForm(postId) {
    const form = document.getElementById('report-form-' + postId);
    if (form.classList.contains('d-none')) {
      form.classList.remove('d-none');
    } else {
      form.classList.add('d-none');
    }
  }
</script>



  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
    }

    .card {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      border-radius: 12px;
      transition: transform 0.2s ease;
    }

    .card:hover {
      transform: scale(1.01);
    }

    .card-title {
      font-size: 1.4rem;
      font-weight: 600;
    }

    .header h2 {
      font-weight: bold;
      color: #0d6efd;
    }

    .post-meta {
      font-size: 0.9rem;
      color: #6c757d;
    }

    .btn-sm {
      margin-right: 8px;
    }

    .pagination .page-link {
      color: #0d6efd;
    }

    .pagination .page-item.active .page-link {
      background-color: #0d6efd;
      border-color: #0d6efd;
      color: white;
    }

    .search-bar input {
      max-width: 300px;
    }

    
    .dark body {
      background-color: #121212;
      color: #e0e0e0;
    }

    .dark .card {
      background-color: #1f1f1f;
      color: #e0e0e0;
      border-color: #2c2c2c;
    }

    .dark .form-control {
      background-color: #1f1f1f;
      border: 1px solid #444;
      color: #e0e0e0;
    }

    .dark .btn-outline-primary {
      border-color: #90caf9;
      color: #90caf9;
    }

    .dark .btn-outline-primary:hover {
      background-color: #90caf9;
      color: #121212;
    }

    .dark .pagination .page-link {
      background-color: #1f1f1f;
      color: #90caf9;
    }

    .dark .pagination .page-item.active .page-link {
      background-color: #90caf9;
      color: #121212;
    }

    .dark-toggle {
      margin-left: 10px;
    }
    .dark ::placeholder {
  color: #ccc; /* light gray for visibility in dark mode */
}

    .flash-popup {
  position: fixed;
  top: 20%;
  left: 50%;
  transform: translateX(-50%);
  max-width: 80%;
  background-color: #d1e7dd;
  color: #0f5132;
  border: 1px solid #badbcc;
  padding: 12px 20px;
  border-radius: 10px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
  z-index: 9999;
  font-weight: 500;
  text-align: center;
  animation: fadeOut 4s forwards;
  font-size: 16px;
}
@keyframes fadeOut {
  0% { opacity: 1; }
  80% { opacity: 1; }
  100% { opacity: 0; visibility: hidden; }
}

  </style>
</head>

<body class="container mt-4">
 
<?php if (isset($_SESSION['flash'])): ?>
  <div class="flash-popup" id="flashPopup">
    <?= $_SESSION['flash'] ?>
  </div>
  <?php unset($_SESSION['flash']); ?>
<?php endif; ?>



  <div class="d-flex justify-content-between mb-4 align-items-center header">
    <h2>📰 Blog Posts</h2>
    <div class="d-flex align-items-center">
      <?php if (isset($_SESSION['username'])): ?>
       <span class="me-2">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-2">🏠 Home</a>
        👋 Hello, <b><?= htmlspecialchars($_SESSION['username']) ?></b>
        <span class="badge bg-secondary ms-2"><?= ucfirst($_SESSION['role'] ?? 'guest') ?></span>
      </span>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-primary btn-sm me-2">Login</a>
        <a href="register.php" class="btn btn-success btn-sm">Register</a>
      <?php endif; ?>
      <button id="darkToggle" class="btn btn-outline-secondary btn-sm dark-toggle">🌙 Toggle Dark</button>
    </div>
  </div>

  <form method="GET" class="mb-4 d-flex gap-2 search-bar">
    <input type="text" name="search" class="form-control" placeholder="Search posts..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn btn-outline-primary">Search</button>
  </form>

  <?php if (isset($_SESSION['username'])): ?>
  <div class="mb-4">
    <div class="d-flex flex-wrap gap-2">
      <a href="create.php" class="btn btn-success">➕ Create New Post</a>

      <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <a href="admin.php" class="btn btn-warning">🧑‍💼 Admin Dashboard</a>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>



  <?php while($row = mysqli_fetch_assoc($result)): ?>
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
        <p class="card-text"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
        <p class="post-meta mb-2">Posted by <b><?= htmlspecialchars($row['username']) ?></b> on <?= $row['created_at'] ?></p>

        <div>
          <a href="post.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm">View Post</a>

          <?php
  $is_logged_in = isset($_SESSION['user_id']);
  $current_user_id = $_SESSION['user_id'] ?? 0;
  $current_user_role = $_SESSION['role'] ?? 'guest';
  $is_owner = $is_logged_in && $current_user_id == $row['user_id'];
  $is_admin = in_array($current_user_role, ['admin', 'editor']);
?>
<?php
$can_edit = ($current_user_role === 'admin' || $current_user_role === 'editor' || $is_owner);
$can_delete = ($current_user_role === 'admin' || ($current_user_role === 'user' && $is_owner));
?>

<?php if ($can_edit): ?>
  <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
<?php endif; ?>

<?php if ($can_delete): ?>
  <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this post?')">Delete</a>
<?php endif; ?>

<?php if ($is_logged_in && $row['user_id'] != $current_user_id): ?>
  <div class="text-end mt-2" id="report-section-<?= $row['id'] ?>">
    <!-- Initial Report Button -->
    <button type="button" class="btn btn-outline-danger btn-sm" onclick="toggleReportForm(<?= $row['id'] ?>)">
      🚩 Report
    </button>

    <!-- Hidden Report Form -->
    <form method="POST" action="report_user.php" class="d-none mt-2 report-form" id="report-form-<?= $row['id'] ?>">
      <input type="hidden" name="reported_id" value="<?= $row['user_id'] ?>">
      <input type="hidden" name="post_id" value="<?= $row['id'] ?>">

      <div class="d-flex flex-wrap justify-content-end align-items-center gap-2">
        <select name="reason" class="form-select form-select-sm w-auto" required>
          <option value="" disabled selected>🚩 Reason</option>
          <option value="Spam or misleading">Spam or misleading</option>
          <option value="Offensive content">Offensive content</option>
          <option value="Harassment">Harassment</option>
          <option value="Fake news / Misinformation">Fake news / Misinformation</option>
          <option value="Other">Other</option>
        </select>

        <input type="text" name="extra_reason" placeholder="Details (optional)" class="form-control form-control-sm w-auto">

        <button type="submit" class="btn btn-outline-danger btn-sm">Submit Report</button>
      </div>
    </form>
  </div>
<?php endif; ?>

   </div>
      </div>
    </div>
  <?php endwhile; ?>

  <nav>
    <ul class="pagination justify-content-center">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
          <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>

  <?php if ($current_user_role === 'admin'): ?>
    <hr>
    <h4>📜 Recent Activity Logs</h4>
    <?php if ($logs_result && $logs_result->num_rows > 0): ?>
      <ul class="list-group mb-4">
        <?php while ($log = $logs_result->fetch_assoc()): ?>
          <li class="list-group-item">
            <b><?= htmlspecialchars($log['username']) ?></b> — <?= htmlspecialchars($log['action']) ?> 
            <small class="text-muted">(<?= $log['created_at'] ?>)</small><br>
            <?= nl2br(htmlspecialchars($log['details'])) ?>
          </li>
        <?php endwhile; ?>
      </ul>
    <?php else: ?>
      <p class="text-muted">No logs to show.</p>
    <?php endif; ?>
  <?php endif; ?>

  <script>
    document.getElementById('darkToggle').addEventListener('click', () => {
      document.documentElement.classList.toggle('dark');
      const isDark = document.documentElement.classList.contains('dark');
      localStorage.setItem('darkMode', isDark);
    });
  </script>
  <script>
  const searchInput = document.querySelector('input[name="search"]');

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      if (this.value.trim() === '') {
        // Redirect to home page without query params
        window.location.href = 'index.php';
      }
    });
  }
</script>


</body>
</html>