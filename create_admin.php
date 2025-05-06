<?php
require_once 'config/database.php';

// Ganti password admin
$new_password = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->execute([$new_password]);

echo "Password admin berhasil direset!";

function is_admin() { return $_SESSION['role'] == 'admin'; }
function is_editor() { return $_SESSION['role'] == 'editor'; }
function is_author() { return $_SESSION['role'] == 'author'; }
function is_view() { return $_SESSION['role'] == 'view'; }

// Tambahkan kode untuk memeriksa role user
$stmt = $pdo->prepare("SELECT username, role FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch();

if (strtolower($user['role']) === strtolower($role)) {
    // Redirect ke dashboard view
    header('Location: admin/dashboard_view.php');
    exit();
}

UPDATE users SET role = 'view' WHERE username = 'USERNAME_YANG_DICOBA';
?>