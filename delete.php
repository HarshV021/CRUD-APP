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

// 🔐 Check if user is allowed to delete this post
if (in_array($user_role, ['admin', 'editor'])) {
    // Admin/editor can delete any post
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
} else {
    // Normal user must own the post
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    echo "<div style='margin:50px; font-family:sans-serif; color:red;'>❌ Unauthorized delete attempt or post not found.</div>";
    exit();
}

// 🔥 Delete the post
if (in_array($user_role, ['admin', 'editor'])) {
    $delete_stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $delete_stmt->bind_param("i", $id);
} else {
    $delete_stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $id, $user_id);
}

if ($delete_stmt->execute()) {
    header("Location: index.php");
    exit();
} else {
    echo "<div style='margin:50px; font-family:sans-serif; color:red;'>❌ Failed to delete the post. Please try again.</div>";
}
?>

