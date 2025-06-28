<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized.");
}

$reporter_id = $_SESSION['user_id'];
$reported_id = $_POST['reported_id'];
$reason = $_POST['reason'] ?? 'No reason provided';

$stmt = $conn->prepare("INSERT INTO logs (user_id, action, details) VALUES (?, 'report_user', ?)");
$details = "Reported user ID $reported_id — Reason: " . $reason;
$stmt->bind_param("is", $reporter_id, $details);
$stmt->execute();

echo "✅ Report submitted.";
?>
