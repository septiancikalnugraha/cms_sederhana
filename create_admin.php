<?php
require_once 'config/database.php';

// Data admin
$username = 'admin';
$password_plain = 'admin123';
$password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);
$email = 'admin@example.com';

// Cek apakah user admin sudah ada
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    // Jika sudah ada, update password dan email
    $stmt = $pdo->prepare("UPDATE users SET password = ?, email = ? WHERE username = ?");
    $stmt->execute([$password_hashed, $email, $username]);
    echo "Akun admin berhasil diupdate dan divalidasi!<br>";
} else {
    // Jika belum ada, insert user baru
    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role, status) VALUES (?, ?, ?, 'admin', 'active')");
    $stmt->execute([$username, $password_hashed, $email]);
    echo "Akun admin berhasil dibuat dan divalidasi!<br>";
}

echo "Sekarang Anda bisa login dengan:<br>";
echo "Username: <b>admin</b><br>";
echo "Password: <b>admin123</b><br>";
?>