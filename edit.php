<?php
include 'db.php';
$id = $_GET['id'];
$post = $conn->query("SELECT * FROM posts WHERE id=$id")->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $conn->query("UPDATE posts SET title='$title', content='$content' WHERE id=$id");
    header("Location: index.php");
}
?>

<form method="POST">
    Title: <input name="title" value="<?= $post['title'] ?>"><br>
    Content:<br><textarea name="content"><?= $post['content'] ?></textarea><br>
    <button type="submit">Update</button>
</form>
