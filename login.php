<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username='$username'");
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $username;
        header("Location: index.php");
    } else {
        echo "Login failed";
    }
}
?>

<form method="POST">
    Username: <input name="username"><br>
    Password: <input name="password" type="password"><br>
    <button type="submit">Login</button>
</form>
