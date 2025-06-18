<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch post to ensure it belongs to the logged-in user
$sql = "SELECT * FROM posts WHERE id = $id AND user_id = $user_id";
$result = mysqli_query($conn, $sql);
$post = mysqli_fetch_assoc($result);

if (!$post) {
    echo "<div style='margin:50px; font-family:sans-serif; color:red;'>❌ Unauthorized delete attempt or post not found.</div>";
    exit();
}

// Delete the post
$delete_sql = "DELETE FROM posts WHERE id = $id AND user_id = $user_id";
if (mysqli_query($conn, $delete_sql)) {
    header("Location: index.php");
    exit();
} else {
    echo "<div style='margin:50px; font-family:sans-serif; color:red;'>❌ Failed to delete the post. Please try again.</div>";
}
?>
