<?php
session_start();
include 'db.php';

// ✅ Only admin can change roles
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "❌ Unauthorized access.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $new_role = $_POST['new_role'] ?? '';

    $valid_roles = ['user', 'editor', 'admin'];
    if (!in_array($new_role, $valid_roles)) {
        echo "❌ Invalid role selected.";
        exit();
    }

    // Prevent admin from demoting themselves
    if ($user_id == $_SESSION['user_id']) {
        echo "❌ You cannot change your own role.";
        exit();
    }

    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $new_role, $user_id);

    if ($stmt->execute()) {
        header("Location: admin.php"); // ✅ Redirect back to dashboard
        exit();
    } else {
        echo "❌ Failed to update role.";
    }
}
?>
