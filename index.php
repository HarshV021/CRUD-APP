<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
}
include 'db.php';
$result = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
?>

<a href="create.php">+ New Post</a>
<h2>All Posts</h2>

<?php while ($row = $result->fetch_assoc()): ?>
    <h3><?= $row['title'] ?></h3>
    <p><?= $row['content'] ?></p>
    <a href="edit.php?id=<?= $row['id'] ?>">Edit</a>
    <a href="delete.php?id=<?= $row['id'] ?>">Delete</a>
    <hr>
<?php endwhile; ?>
