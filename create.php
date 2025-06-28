<?php
session_start();
include 'db.php';

// Block if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        $filename = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $filename;
        }
    }

    $stmt = $conn->prepare("INSERT INTO posts (title, content, user_id, image_path, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssis", $title, $content, $user_id, $image_path);

    if ($stmt->execute()) {
        $msg = "✅ Post added successfully!";

        // Log action
        $log_stmt = $conn->prepare("INSERT INTO logs (user_id, action, details) VALUES (?, 'create_post', ?)");
        $log_stmt->bind_param("is", $user_id, $title);
        $log_stmt->execute();

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

  <form method="POST" enctype="multipart/form-data" class="p-4 border rounded shadow-sm bg-light">
    <div class="mb-3">
      <label class="form-label">Post Title</label>
      <input type="text" name="title" class="form-control" required placeholder="Enter post title">
    </div>

    <div class="mb-3">
      <label class="form-label">Post Content</label>
      <textarea name="content" class="form-control" rows="5" required placeholder="Write your content here..."></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Post Image</label>
      <input type="file" name="image" class="form-control" accept="image/*">
    </div>

    <button type="submit" class="btn btn-primary">✅ Submit Post</button>
    <a href="index.php" class="btn btn-secondary ms-2">⬅ Back</a>
  </form>

</body>
</html>
