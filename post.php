<?php
session_start();
include 'db.php';

$post_id = $_GET['id'] ?? 0;

// Fetch the post
$post_sql = "SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = $post_id";
$post_result = mysqli_query($conn, $post_sql);
$post = mysqli_fetch_assoc($post_result);

if (!$post) {
    echo "<div style='margin:50px; font-family:sans-serif; color:red;'>❌ Post not found.</div>";
    exit();
}

// Add comment
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    $user_id = $_SESSION['user_id'];
    $insert_sql = "INSERT INTO comments (post_id, user_id, content) VALUES ($post_id, $user_id, '$comment')";
    if (mysqli_query($conn, $insert_sql)) {
        $msg = "✅ Comment added!";
    } else {
        $msg = "❌ Failed to add comment.";
    }
}

// Fetch comments
$comments_sql = "SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = $post_id ORDER BY created_at DESC";
$comments_result = mysqli_query($conn, $comments_sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title><?= htmlspecialchars($post['title']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

  <h2><?= htmlspecialchars($post['title']) ?></h2>
  <p class="text-muted">By <b><?= htmlspecialchars($post['username']) ?></b> on <?= $post['created_at'] ?></p>
  <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
  <hr>

  <!-- Comment Form -->
  <h4>💬 Comments</h4>

  <?php if ($msg): ?>
    <div class="alert <?= str_contains($msg, '✅') ? 'alert-success' : 'alert-danger' ?>"><?= $msg ?></div>
  <?php endif; ?>

  <?php if (isset($_SESSION['user_id'])): ?>
    <form method="POST" class="mb-4">
      <div class="mb-3">
        <textarea name="comment" class="form-control" rows="3" required placeholder="Write a comment..."></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Post Comment</button>
    </form>
  <?php else: ?>
    <p class="text-muted">🔒 <a href="login.php">Login</a> to comment.</p>
  <?php endif; ?>

  <!-- Display Comments -->
  <?php if (mysqli_num_rows($comments_result) > 0): ?>
    <?php while ($comment = mysqli_fetch_assoc($comments_result)): ?>
      <div class="border rounded p-3 mb-3 bg-light">
        <p class="mb-1"><b><?= htmlspecialchars($comment['username']) ?></b> <small class="text-muted"><?= $comment['created_at'] ?></small></p>
        <p class="mb-0"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p class="text-muted">No comments yet. Be the first to comment!</p>
  <?php endif; ?>

  <a href="index.php" class="btn btn-secondary mt-4">⬅ Back to Posts</a>

</body>
</html>
