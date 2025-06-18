<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch the post and ensure it belongs to the logged-in user
$sql = "SELECT * FROM posts WHERE id = $id AND user_id = $user_id";
$result = mysqli_query($conn, $sql);
$post = mysqli_fetch_assoc($result);

if (!$post) {
    echo "<div style='margin:50px; font-family:sans-serif; color:red;'>❌ Unauthorized access or post not found.</div>";
    exit();
}

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);

    $update_sql = "UPDATE posts SET title = '$title', content = '$content' WHERE id = $id AND user_id = $user_id";
    if (mysqli_query($conn, $update_sql)) {
        $msg = "✅ Post updated successfully!";
        //  Refresh post data
        $post['title'] = $title;
        $post['content'] = $content;
    } else {
        $msg = "❌ Update failed.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Post</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

  <h2 class="mb-4">✏️ Edit Your Post</h2>

  <?php if ($msg): ?>
    <div class="alert <?= str_contains($msg, '✅') ? 'alert-success' : 'alert-danger' ?>">
      <?= $msg ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="p-4 border rounded shadow-sm bg-light">
    <div class="mb-3">
      <label class="form-label">Post Title</label>
      <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($post['title']) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Post Content</label>
      <textarea name="content" class="form-control" rows="5" required><?= htmlspecialchars($post['content']) ?></textarea>
    </div>

    <button type="submit" class="btn btn-warning">💾 Save Changes</button>
    <a href="index.php" class="btn btn-secondary ms-2">⬅ Back</a>
  </form>

</body>
</html>
