<?php
session_start();
include 'db.php';

if ($_SESSION['role'] !== 'admin') {
    echo "<div style='margin:50px; font-family:sans-serif; color:red;'>❌ Access denied. Admins only.</div>";
    exit();
}

// Count totals
$users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$posts = $conn->query("SELECT COUNT(*) AS total FROM posts")->fetch_assoc()['total'];
$comments = $conn->query("SELECT COUNT(*) AS total FROM comments")->fetch_assoc()['total'];

// Recent logs
$logs_stmt = $conn->prepare(
    "SELECT logs.*, users.username FROM logs 
     JOIN users ON logs.user_id = users.id 
     ORDER BY logs.created_at DESC 
     LIMIT 10"
);
$logs_stmt->execute();
$logs_result = $logs_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

  <h2 class="mb-4">🧑‍💼 Admin Dashboard</h2>

  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card text-white bg-primary mb-3">
        <div class="card-body">
          <h5 class="card-title">👥 Users</h5>
          <p class="card-text fs-4"><?= $users ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-success mb-3">
        <div class="card-body">
          <h5 class="card-title">📝 Posts</h5>
          <p class="card-text fs-4"><?= $posts ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-info mb-3">
        <div class="card-body">
          <h5 class="card-title">💬 Comments</h5>
          <p class="card-text fs-4"><?= $comments ?></p>
        </div>
      </div>
    </div>
  </div>

  <h4>📜 Recent Activity Logs</h4>
  <?php if ($logs_result->num_rows > 0): ?>
    <ul class="list-group">
      <?php while ($log = $logs_result->fetch_assoc()): ?>
        <li class="list-group-item">
          <b><?= htmlspecialchars($log['username']) ?></b> — <?= htmlspecialchars($log['action']) ?> 
          <small class="text-muted">(<?= $log['created_at'] ?>)</small><br>
          <?= nl2br(htmlspecialchars($log['details'])) ?>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php else: ?>
    <p class="text-muted">No logs available.</p>
  <?php endif; ?>

  <a href="index.php" class="btn btn-secondary mt-4">⬅ Back to Home</a>

</body>
</html>
