<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';

$msg = "";

// 🔒 Admin/editor can edit any post; normal user can only edit their own
if (in_array($user_role, ['admin', 'editor'])) {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
} else {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    echo "<div style='margin:50px; font-family:sans-serif; color:red;'>❌ Unauthorized access or post not found.</div>";
    exit();
}

// ✅ Update logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $image_path = $post['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        $filename = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $filename;
        }
    }

    // Again, handle role check for update
    if (in_array($user_role, ['admin', 'editor'])) {
        $update_stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, image_path = ? WHERE id = ?");
        $update_stmt->bind_param("sssi", $title, $content, $image_path, $id);
    } else {
        $update_stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, image_path = ? WHERE id = ? AND user_id = ?");
        $update_stmt->bind_param("sssii", $title, $content, $image_path, $id, $user_id);
    }

    if ($update_stmt->execute()) {
        $msg = "✅ Post updated successfully!";
        $post['title'] = $title;
        $post['content'] = $content;
        $post['image_path'] = $image_path;
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

  <form method="POST" enctype="multipart/form-data" class="p-4 border rounded shadow-sm bg-light">
    <div class="mb-3">
      <label class="form-label">Post Title</label>
      <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($post['title']) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Post Content</label>
      <textarea name="content" class="form-control" rows="5" required><?= htmlspecialchars($post['content']) ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Update Image (optional)</label>
      <input type="file" name="image" class="form-control" accept="image/*">
      <?php if (!empty($post['image_path'])): ?>
        <img src="uploads/<?= htmlspecialchars($post['image_path']) ?>" class="img-fluid mt-2" style="max-height: 300px;">
      <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-warning">💾 Save Changes</button>
    <a href="index.php" class="btn btn-secondary ms-2">⬅ Back</a>
  </form>

</body>
</html>