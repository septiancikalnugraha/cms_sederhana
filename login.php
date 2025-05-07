<?php
session_start();
require_once 'config/database.php';

if(isset($_SESSION['user_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if($user) {
        if(password_verify($password, $user['password'])) {
            // Debug: tampilkan role di database dan role yang dipilih
            echo "<div style='background:#fff;padding:10px;border:1px solid #ccc;margin-bottom:10px;'>";
            echo "Role di database: <b>" . htmlspecialchars($user['role']) . "</b><br>";
            echo "Role yang dipilih: <b>" . htmlspecialchars($role) . "</b><br>";
            echo "</div>";
            if(strtolower($user['role']) === strtolower($role)) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                if (strtolower($user['role']) === 'view') {
                    header("Location: admin/dashboard_view.php");
                } elseif (strtolower($user['role']) === 'editor') {
                    header("Location: admin/dashboard_editor.php");
                } else {
                    header("Location: admin/dashboard.php");
                }
                exit();
            } else {
                $error = 'Role yang dipilih tidak sesuai.';
            }
        } else {
            $error = 'Password salah.';
        }
    } else {
        $error = 'Username tidak ditemukan.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CMS Sederhana</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/admin-dashboard.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #222d32;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
        }
        .brand-link {
            font-size: 2rem;
            font-weight: bold;
            color: #007bff;
            text-align: center;
            display: block;
            margin-bottom: 1rem;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="card-header bg-dark text-white text-center">
                <span class="brand-link">CMS Sederhana</span>
            </div>
            <div class="card-body">
                <h5 class="text-center mb-3">Sign in to start your session</h5>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form action="login.php" method="post">
                    <div class="mb-3 input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" name="username" placeholder="Username" required>
                    </div>
                    <div class="mb-3 input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                    </div>
                    <div class="mb-3">
                        <select class="form-control" name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="admin">Admin</option>
                            <option value="editor">Editor</option>
                            <option value="view">View</option>
                        </select>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Sign In</button>
                    </div>
                </form>
                <p class="mt-3 mb-1 text-center">
                    <a href="register.php">Belum punya akun? Register</a>
                </p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 