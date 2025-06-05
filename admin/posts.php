<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if(!isset($_SESSION['user_id']) || $_SESSION['role'] == 'view') {
    header("Location: ../index.php");
    exit();
}

// Inisialisasi koneksi database
$database = new Database();
$pdo = $database->getConnection();

$posts = []; // Initialize posts as an empty array
$error = ''; // Initialize error variable

// Lanjutkan hanya jika koneksi database berhasil
if ($pdo) {
    // Handle post deletion
    if(isset($_POST['delete_post'])) {
        $post_id = $_POST['post_id'];
        
        // Periksa apakah pengguna adalah admin atau pemilik post (jika perlu)
        // Misalnya, ambil author_id post dan bandingkan dengan user_id di session
        $stmt_check_author = $pdo->prepare("SELECT author_id FROM posts WHERE id = ?");
        $stmt_check_author->execute([$post_id]);
        $post_to_delete = $stmt_check_author->fetch(PDO::FETCH_ASSOC);

        if ($post_to_delete && ($_SESSION['role'] == 'admin' || ($post_to_delete['author_id'] == $_SESSION['user_id'] && $_SESSION['role'] == 'editor'))) {
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
            $stmt->execute([$post_id]);
            // Redirect setelah berhasil delete
            header("Location: posts.php");
            exit();
        } else if ($post_to_delete) {
            // Jika bukan admin dan bukan pemilik post (untuk editor)
            $error = "Anda tidak memiliki izin untuk menghapus post ini.";
        } else {
             // Jika post tidak ditemukan
             $error = "Post tidak ditemukan.";
        }
    }

    // Get all posts with category and author information
    // Modify query for editor to see only their posts, unless admin
    $sql = "SELECT p.*, c.name as category_name, u.username as author_name 
            FROM posts p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN users u ON p.author_id = u.id";
    
    $params = [];
    if ($_SESSION['role'] == 'editor') {
        $sql .= " WHERE p.author_id = ?";
        $params[] = $_SESSION['user_id'];
    }

    $sql .= " ORDER BY p.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    // Handle error koneksi database
    $error = "Koneksi database gagal. Daftar post tidak tersedia.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts - CMS Sederhana</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->

    <link href="../assets/css/admin-posts.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="dashboard.php" class="brand-link">
                <span class="brand-text font-weight-light">CMS Sederhana</span>
            </a>

            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="info">
                        <a href="#" class="d-block"><?php echo htmlspecialchars($_SESSION['username']); ?> <span class="badge bg-info text-dark ms-2"><?php echo ucfirst($_SESSION['role']); ?></span></a>
                    </div>
                </div>

                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="posts.php" class="nav-link active">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>Posts</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="categories.php" class="nav-link">
                                <i class="nav-icon fas fa-folder"></i>
                                <p>Categories</p>
                            </a>
                        </li>
                        <?php if($_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a href="users.php" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Users</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Posts</h1>
                        </div>
                        <div class="col-sm-6">
                            <?php if($_SESSION['role'] != 'view'): // Hanya Editor dan Admin yang bisa menambah post ?>
                            <a href="post_edit.php" class="btn btn-primary float-right">
                                <i class="fas fa-plus"></i> Add New Post
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="container-fluid">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Author</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <?php if($_SESSION['role'] != 'view'): // Hanya Editor dan Admin yang bisa melihat/melakukan aksi edit/delete ?>
                                        <th>Actions</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($posts)): ?>
                                    <tr>
                                        <td colspan="<?php echo ($_SESSION['role'] != 'view') ? 6 : 5; // Sesuaikan colspan berdasarkan role ?>" class="text-center">Belum ada post</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach($posts as $post): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                                        <td><?php echo htmlspecialchars($post['category_name']); ?></td>
                                        <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $post['status'] == 'published' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($post['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                                        <?php if($_SESSION['role'] != 'view'): // Hanya Editor dan Admin yang bisa melihat/melakukan aksi edit/delete ?>
                                        <td>
                                            <a href="post_edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if($_SESSION['role'] == 'admin' || ($post['author_id'] == $_SESSION['user_id'] && $_SESSION['role'] == 'editor')): // Hanya Admin atau pemilik post (Editor) yang bisa delete ?>
                                            <form action="" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                <button type="submit" name="delete_post" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Version</b> 1.0.0
            </div>
            <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="../index.php">CMS Sederhana</a>.</strong> All rights reserved.
        </footer>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE JS -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html> 