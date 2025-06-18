<?php
session_start();
include 'db.php';

$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Count total posts
$count_sql = "SELECT COUNT(*) as total FROM posts WHERE title LIKE '%$search%' OR content LIKE '%$search%'";
$count_result = mysqli_query($conn, $count_sql);
$total = mysqli_fetch_assoc($count_result)['total'];
$totalPages = ceil($total / $limit);

// Fetch posts with username and user id
$sql = "SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id 
        WHERE title LIKE '%$search%' OR content LIKE '%$search%' 
        ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
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
  </style>
</head>

<body class="container mt-4">

 
  <div class="d-flex justify-content-between mb-4 align-items-center header">
    <h2>📰 Blog Posts</h2>
    <div class="d-flex align-items-center">
      <?php if (isset($_SESSION['username'])): ?>
        <span class="me-2">👋 Hello, <b><?= htmlspecialchars($_SESSION['username']) ?></b></span>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-primary btn-sm me-2">Login</a>
        <a href="register.php" class="btn btn-success btn-sm">Register</a>
      <?php endif; ?>

      <!-- Dark Mode Toggle Button -->
      <button id="darkToggle" class="btn btn-outline-secondary btn-sm dark-toggle">🌙 Toggle Dark</button>
    </div>
  </div>

  <!-- Search -->
  <form method="GET" class="mb-4 d-flex gap-2 search-bar">
    <input type="text" name="search" class="form-control" placeholder="Search posts..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn btn-outline-primary">Search</button>
  </form>

  <!-- Create Button -->
  <?php if (isset($_SESSION['username'])): ?>
    <a href="create.php" class="btn btn-success mb-4">➕ Create New Post</a>
  <?php endif; ?>

  <!-- Posts List -->
  <?php while($row = mysqli_fetch_assoc($result)): ?>
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
        <p class="card-text"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
        <p class="post-meta mb-2">Posted by <b><?= htmlspecialchars($row['username']) ?></b> on <?= $row['created_at'] ?></p>

        <div>
          <a href="post.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm">View Post</a>

          <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
            <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this post?')">Delete</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endwhile; ?>

  <!-- Pagination -->
  <nav>
    <ul class="pagination justify-content-center">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
          <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>

  <!-- JS to Toggle Dark Mode -->
  <script>
    document.getElementById('darkToggle').addEventListener('click', () => {
      document.documentElement.classList.toggle('dark');
      const isDark = document.documentElement.classList.contains('dark');
      localStorage.setItem('darkMode', isDark);
    });
  </script>

</body>
</html>
