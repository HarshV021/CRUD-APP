<?php
session_start();
include 'db.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // ✅ Server-side validation
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $msg = "❌ Username must be 3–20 characters and contain only letters, numbers, or underscores.";
    } elseif (strlen($password) < 6) {
        $msg = "❌ Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $msg = "❌ Passwords do not match!";
    } else {
        // ✅ Check if username exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $msg = "⚠️ Username already taken!";
        } else {
            // ✅ Secure password hash and insert
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $insert_stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $insert_stmt->bind_param("ss", $username, $hashed);

            if ($insert_stmt->execute()) {
                $msg = "✅ Registered successfully! Please <a href='login.php'>login here</a>.";
            } else {
                $msg = "❌ Registration failed. Please try again.";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f0f2f5;
    }
    .register-box {
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

  <div class="register-box">
    <h2 class="text-center mb-4">🧾 Register New Account</h2>

    <?php if ($msg): ?>
      <div class="alert <?= str_contains($msg, '✅') ? 'alert-success' : 'alert-danger' ?>">
        <?= $msg ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input 
          type="text" 
          name="username" 
          class="form-control" 
          required 
          minlength="3" 
          maxlength="20" 
          pattern="^[a-zA-Z0-9_]+$" 
          placeholder="Create a username">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input 
          type="password" 
          name="password" 
          class="form-control" 
          required 
          minlength="6" 
          placeholder="Create a password">
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input 
          type="password" 
          name="confirm_password" 
          class="form-control" 
          required 
          minlength="6" 
          placeholder="Confirm password">
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-success">📝 Register</button>
      </div>
      <p class="text-center mt-3">
        Already have an account? <a href="login.php">Login here</a>
      </p>
    </form>
  </div>

</body>
</html>
