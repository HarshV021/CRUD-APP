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
<hr class="my-5">
<h4>🔒 Manage Users</h4>

<?php
$users_stmt = $conn->prepare("SELECT id, username, email, role, is_blocked FROM users");
$users_stmt->execute();
$users_result = $users_stmt->get_result();
?>

<table class="table table-bordered table-hover">
  <thead class="table-light">
    <tr>
      <th>ID</th>
      <th>Username</th>
      <th>Email</th>
      <th>Role</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($user = $users_result->fetch_assoc()): ?>
      <tr>
        <td><?= $user['id'] ?></td>
        <td><?= htmlspecialchars($user['username']) ?></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
        <td>
  <?php if ($user['id'] != $_SESSION['user_id']): ?>
    <form method="POST" action="update_role.php" class="d-flex align-items-center">
      <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
      <select name="new_role" class="form-select form-select-sm me-2" onchange="this.form.submit()" required>
        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
        <option value="editor" <?= $user['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>
    </form>
  <?php else: ?>
    <?= ucfirst($user['role']) ?>
  <?php endif; ?>
</td>

        <td>
          <?= $user['is_blocked'] ? '<span class="badge bg-danger">Blocked</span>' : '<span class="badge bg-success">Active</span>' ?>
        </td>
        <td>
          <?php if ($user['id'] != $_SESSION['user_id']): ?>
            <form method="POST" action="toggle_block.php" class="d-inline">
              <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
              <input type="hidden" name="block" value="<?= $user['is_blocked'] ? 0 : 1 ?>">
              <button type="submit" class="btn btn-sm <?= $user['is_blocked'] ? 'btn-success' : 'btn-danger' ?>">
                <?= $user['is_blocked'] ? 'Unblock' : 'Block' ?>
              </button>
            </form>
          <?php else: ?>
            <span class="text-muted">N/A</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>

 <div class="mt-2 text-start">
  <a href="index.php" class="btn btn-outline-secondary btn-sm px-4 py-2 shadow-sm">
    ⬅ Back to Home
  </a>
</div>



</body>
</html>
