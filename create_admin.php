<?php
require_once 'config/database.php';

// Ganti password admin
$new_password = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->execute([$new_password]);

echo "Password admin berhasil direset!";
?>