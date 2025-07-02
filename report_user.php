<?php
ob_start(); // ✅ Enable output buffering to prevent header errors
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized.");
}

if (!isset($_POST['reported_id']) || !is_numeric($_POST['reported_id'])) {
    exit("Invalid user ID.");
}

$reporter_id = $_SESSION['user_id'];
$reported_id = (int)$_POST['reported_id'];
$reason = htmlspecialchars($_POST['reason'] ?? 'No reason provided', ENT_QUOTES);
$details = "Reported user ID $reported_id — Reason: $reason";

$stmt = $conn->prepare("INSERT INTO logs (user_id, action, details) VALUES (?, 'report_user', ?)");
if (!$stmt) {
    exit("Prepare failed: " . $conn->error);
}
$stmt->bind_param("is", $reporter_id, $details);
$stmt->execute();

// ✅ Flash message for success
$_SESSION['flash'] = "✅ Report submitted successfully.";

// ✅ Redirect back to previous page
$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header("Location: $redirect");
exit;
?>
