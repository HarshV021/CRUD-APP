<?php
include 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, password) VALUES ('$username', '$password')");
    echo "Registered!";
}
?>

<form method="POST">
    Username: <input name="username"><br>
    Password: <input name="password" type="password"><br>
    <button type="submit">Register</button>
</form>
