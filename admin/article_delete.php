<?php
session_start();
require_once '../config/database.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'view') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$id]);
}
header("Location: dashboard_view.php");
exit(); 