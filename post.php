<?php
session_start();
include 'db.php';

$post_id = $_GET['id'] ?? 0;

$post_stmt = $conn->prepare(
    "SELECT posts.*, users.username 
     FROM posts 
     JOIN users ON posts.user_id = users.id 
     WHERE posts.id = ?"
);
$post_stmt->bind_param("i", $post_id);
$post_stmt->execute();
$post_result = $post_stmt->get_result();
$post = $post_result->fetch_assoc();

if (!$post) {
    echo "<div style='margin:50px; font-family:sans-serif; color:red;'>❌ Post not found.</div>";
    exit();
}

$is_owner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id'];
$is_admin = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'editor']);
$is_logged_in = isset($_SESSION['user_id']);

$msg = "";

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment']) && $is_logged_in) {
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];

    $insert_stmt = $conn->prepare(
        "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)"
    );
    $insert_stmt->bind_param("iis", $post_id, $user_id, $comment);

    $msg = $insert_stmt->execute() ? "✅ Comment added!" : "❌ Failed to add comment.";
}

// Fetch comments
$comments_stmt = $conn->prepare(
    "SELECT comments.*, users.username 
     FROM comments 
     JOIN users ON comments.user_id = users.id 
     WHERE post_id = ? 
     ORDER BY created_at DESC"
);
$comments_stmt->bind_param("i", $post_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();

$imgPath = "uploads/" . $post['image_path'];
if (!empty($post['image_path']) && file_exists($imgPath)) {
    [$width, $height] = getimagesize($imgPath);
}
?>
<!DOCTYPE html>
<html>
<head>
  <title><?= htmlspecialchars($post['title']) ?></title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script>
    if (localStorage.getItem('darkMode') === 'true') {
      document.documentElement.classList.add('dark');
    }
  </script>
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body {
      background-color: #f8f9fa;
      color: #212529;
    }
    .dark body {
      background-color: #121212;
      color: #e0e0e0;
    }
    .dark .form-control,
    .dark .form-select {
      background-color: #2c2c2c;
      color: #e0e0e0;
      border: 1px solid #555;
    }
    .dark .text-muted {
      color: #bbb !important;
    }
    .blog-post-image {
      max-width: 100%;
      max-height: 500px;
      transition: all 0.3s ease-in-out;
      cursor: zoom-in;
      border-radius: 12px;
      box-shadow: 0 4px 18px rgba(0, 0, 0, 0.1);
    }
    .blog-post-image:hover {
      opacity: 0.95;
      transform: scale(1.01);
    }
    .modal-content img {
      border-radius: 0.5rem;
    }
  </style>
</head>
<body class="container mt-5">
  <button id="darkToggle" class="btn btn-outline-secondary btn-sm position-absolute top-0 end-0 m-3">🌙 Toggle Dark</button>

  <?php if (!empty($post['image_path']) && file_exists($imgPath)): ?>
  <div class="text-center my-4">
    <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal">
      <img src="<?= $imgPath ?>" alt="Post Image" class="blog-post-image img-fluid rounded shadow">
    </a>
    <p class="text-muted mt-2">🖐️ Size: <?= $width ?> &times; <?= $height ?> px</p>
  </div>
  <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content bg-dark border-0">
        <img src="<?= $imgPath ?>" class="img-fluid rounded" alt="Full Image">
      </div>
    </div>
  </div>
  <?php endif; ?>

  <h2><?= htmlspecialchars($post['title']) ?></h2>
  <p style="color: var(--author-meta-color); font-size: 0.95rem;">
    🕋️ By <b><?= htmlspecialchars($post['username']) ?></b> on <?= $post['created_at'] ?>
  </p>

  <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

  <?php if ($is_logged_in && !$is_owner): ?>
    <button class="btn btn-outline-danger btn-sm float-end mb-2" data-bs-toggle="modal" data-bs-target="#reportModal">🚩 Report</button>
  <?php endif; ?>
  <hr>

  <h4>💬 Comments</h4>
  <?php if ($msg): ?>
    <div class="alert <?= str_contains($msg, '✅') ? 'alert-success' : 'alert-danger' ?>"><?= $msg ?></div>
  <?php endif; ?>

  <?php if ($is_logged_in): ?>
    <form method="POST" class="mb-4">
      <div class="mb-3">
        <textarea name="comment" class="form-control" rows="3" required placeholder="Write a comment..."></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Post Comment</button>
    </form>
  <?php else: ?>
    <p class="text-muted">🔐 <a href="login.php">Login</a> to comment.</p>
  <?php endif; ?>

  <?php if ($comments_result->num_rows > 0): ?>
    <?php while ($comment = $comments_result->fetch_assoc()): ?>
      <div class="border rounded p-3 mb-3 bg-light">
        <p class="mb-1"><b><?= htmlspecialchars($comment['username']) ?></b> <small class="text-muted"><?= $comment['created_at'] ?></small></p>
        <p class="mb-0"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p class="text-muted">No comments yet. Be the first to comment!</p>
  <?php endif; ?>

  <a href="index.php" class="btn btn-secondary mt-4">⬅ Back to Posts</a>

  <?php if ($is_logged_in && !$is_owner): ?>
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form method="POST" action="report_user.php" class="modal-content">
          <input type="hidden" name="reported_id" value="<?= $post['user_id'] ?>">
          <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
          <div class="modal-header">
            <h5 class="modal-title" id="reportModalLabel">🚩 Report Post</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <label class="form-label">Reason</label>
            <select name="reason" class="form-select mb-2" required>
              <option value="" disabled selected>Select a reason</option>
              <option value="Spam or misleading">Spam or misleading</option>
              <option value="Offensive content">Offensive content</option>
              <option value="Harassment">Harassment</option>
              <option value="Fake news / Misinformation">Fake news / Misinformation</option>
              <option value="Other">Other</option>
            </select>
            <input type="text" name="extra_reason" class="form-control" placeholder="Additional details (optional)">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Submit Report</button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <script>
    document.getElementById('darkToggle')?.addEventListener('click', () => {
      document.documentElement.classList.toggle('dark');
      const isDark = document.documentElement.classList.contains('dark');
      localStorage.setItem('darkMode', isDark);
      document.querySelectorAll('.modal').forEach(modal => {
        if (isDark) modal.classList.add('dark');
        else modal.classList.remove('dark');
      });
    });
  </script>
</body>
</html>
