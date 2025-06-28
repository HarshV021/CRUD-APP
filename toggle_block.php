<?php
session_start();
include 'db.php';

if ($_SESSION['role'] !== 'admin') exit("Access denied.");

$user_id = $_POST['user_id'];
$new_status = $_POST['block'];

$stmt = $conn->prepare("UPDATE users SET is_blocked = ? WHERE id = ?");
$stmt->bind_param("ii", $new_status, $user_id);
$stmt->execute();

header("Location: admin.php");
