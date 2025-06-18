<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $user_id = $_SESSION['user_id']; // 🔐 Get current user ID

    $sql = "INSERT INTO posts (title, content, user_id, created_at) VALUES ('$title', '$content', $user_id, NOW())";
    if (mysqli_query($conn, $sql)) {
        $msg = "✅ Post added successfully!";
    } else {
        $msg = "❌ Failed to add post.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add New Post</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

  <h2 class="mb-4">📝 Add New Post</h2>

  <?php if ($msg): ?>
    <div class="alert <?= str_contains($msg, '✅') ? 'alert-success' : 'alert-danger' ?>">
      <?= $msg ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="p-4 border rounded shadow-sm bg-light">
    <div class="mb-3">
      <label class="form-label">Post Title</label>
      <input type="text" name="title" class="form-control" required placeholder="Enter post title">
    </div>

    <div class="mb-3">
      <label class="form-label">Post Content</label>
      <textarea name="content" class="form-control" rows="5" required placeholder="Write your content here..."></textarea>
    </div>

    <button type="submit" class="btn btn-primary">✅ Submit Post</button>
    <a href="index.php" class="btn btn-secondary ms-2">⬅ Back</a>
  </form>

</body>
</html>
