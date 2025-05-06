<?php
session_start();
require_once '../config/database.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'view') {
    header("Location: ../index.php");
    exit();
}

// Fetch some recent articles for preview
$stmt = $pdo->prepare("SELECT id, title, created_at FROM posts ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories
$stmt = $pdo->prepare("SELECT id, name FROM categories ORDER BY name ASC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard View - CMS Sederhana</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/admin-dashboard-view.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-newspaper"></i> CMS Sederhana</h3>
                <button id="sidebarToggle" class="d-md-none"><i class="fas fa-times"></i></button>
            </div>
            <div class="sidebar-user">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <span class="user-role badge bg-primary"><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item active">
                        <a href="dashboard_view.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="../logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <nav class="top-navbar">
                <div class="nav-left">
                    <button id="sidebarCollapseBtn" class="btn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2 class="page-title">Dashboard</h2>
                </div>
                <div class="nav-right">
                    <div class="user-dropdown dropdown">
                        <button class="btn dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                            <li><a class="dropdown-item" href="../profile.php"><i class="fas fa-user me-2"></i>Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Content Area -->
            <div class="content-container">
                <div class="container-fluid px-4">
                    <!-- Welcome Message -->
                    <div class="welcome-card mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h4 class="welcome-title">Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h4>
                                        <p class="welcome-text">
                                            Anda login sebagai <b>View</b>. Dengan akses ini, Anda dapat membaca semua artikel yang tersedia di sistem. 
                                            Jika membutuhkan akses untuk mengedit atau membuat konten, silakan hubungi administrator.
                                        </p>
                                        <a href="../index.php" class="btn btn-primary mt-2">
                                            <i class="fas fa-home me-1"></i> Lihat Artikel
                                        </a>
                                    </div>
                                    <div class="col-md-4 d-none d-md-block text-end">
                                        <i class="fas fa-user-shield welcome-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dashboard Stats -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-icon bg-primary">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                                <div class="stat-details">
                                    <h5>Artikel Terbaru</h5>
                                    <span class="stat-number"><?php echo count($recent_articles); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-folder"></i>
                                </div>
                                <div class="stat-details">
                                    <h5>Kategori</h5>
                                    <span class="stat-number"><?php echo count($categories); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-icon bg-info">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-details">
                                    <h5>Status</h5>
                                    <span class="stat-text">Aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Sections -->
                    <div class="row">
                        <!-- Recent Articles -->
                        <div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(to right, var(--primary-color), #1d4ed8); color: #fff; border-radius: 1rem 1rem 0 0; font-weight: 600; font-size: 1.1rem;">
                                    <h5 class="card-title mb-0"><i class="fas fa-newspaper me-2"></i>Artikel Terbaru</h5>
                                </div>
                                <div class="card-body" style="padding: 2rem 2rem 1.5rem 2rem;">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle" style="background: #fff; border-radius: 0.75rem;">
                                            <thead>
                                                <tr style="background: var(--gray-50); color: var(--dark-color); font-weight: 600;">
                                                    <th>Judul</th>
                                                    <th>Tanggal</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($recent_articles)): ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center">Belum ada artikel</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($recent_articles as $article): ?>
                                                        <tr>
                                                            <td style="font-weight: 500; color: var(--dark-color); font-size: 1.05rem;"> <?php echo htmlspecialchars($article['title']); ?> </td>
                                                            <td style="color: var(--gray-500);"> <?php echo date('d M Y', strtotime($article['created_at'])); ?> </td>
                                                            <td>
                                                                <a href="../article.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-primary" style="border-radius: 0.5rem; font-weight: 500; padding: 0.5rem 1.1rem;"><i class="fas fa-eye"></i> Lihat</a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer text-end" style="background: #f8fafc; border-radius: 0 0 1rem 1rem;">
                                    <a href="../articles.php" class="btn btn-sm btn-outline-primary" style="border-radius: 0.5rem;">Lihat Semua</a>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar Content -->
                        <div class="col-lg-4">
                            <!-- Categories -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-folder me-2"></i>Kategori</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="category-list">
                                        <?php if (empty($categories)): ?>
                                            <li class="text-center">Belum ada kategori</li>
                                        <?php else: ?>
                                            <?php foreach ($categories as $category): ?>
                                                <li>
                                                    <a href="../categories.php?id=<?php echo $category['id']; ?>">
                                                        <i class="fas fa-angle-right me-2"></i> <?php echo htmlspecialchars($category['name']); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>

                            
            </div>
            
            <!-- Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <p>&copy; <?php echo date('Y'); ?> CMS Sederhana. All rights reserved.</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p>Version 1.0.0</p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle for mobile
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
            const sidebar = document.getElementById('sidebar');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.remove('show-sidebar');
                });
            }
            
            if (sidebarCollapseBtn) {
                sidebarCollapseBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('show-sidebar');
                });
            }
            
            // Collapse sidebar for desktop
            const body = document.querySelector('body');
            const collapseBtn = document.getElementById('sidebarCollapseBtn');
            
            if (collapseBtn) {
                collapseBtn.addEventListener('click', function() {
                    body.classList.toggle('sidebar-collapsed');
                });
            }
        });
    </script>
</body>
</html>