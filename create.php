<?php
include 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $conn->query("INSERT INTO posts (title, content) VALUES ('$title', '$content')");
    header("Location: index.php");
}
?>

<form method="POST">
    Title: <input name="title"><br>
    Content:<br><textarea name="content"></textarea><br>
    <button type="submit">Post</button>
</form>
