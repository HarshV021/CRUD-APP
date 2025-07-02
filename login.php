<?php
session_start();
include 'db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);  // Can be username or email
    $password = $_POST['password'];

    if (!filter_var($login, FILTER_VALIDATE_EMAIL) && !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $login)) {
        $error = "❌ Enter a valid username or email.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && $user['is_blocked']) {
            $error = "❌ Your account has been blocked by an admin.";
        } elseif ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            header("Location: index.php");
            exit();
        } else {
            $error = "❌ Invalid login credentials!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f0f2f5;
    }
    .login-box {
      max-width: 400px;
      margin: 80px auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>

  <div class="login-box">
    <h2 class="text-center mb-4">🔐 Login to Your Account</h2>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Username or Email</label>
        <input 
          type="text" 
          name="login" 
          class="form-control" 
          required 
          placeholder="Enter your username or email"
          autocomplete="username">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input 
          type="password" 
          name="password" 
          class="form-control" 
          required 
          placeholder="Enter your password"
          autocomplete="current-password">
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary">🔓 Login</button>
      </div>
      <p class="text-center mt-3">
        Don't have an account? <a href="register.php">Register here</a>
      </p>
    </form>
  </div>

</body>
</html>
