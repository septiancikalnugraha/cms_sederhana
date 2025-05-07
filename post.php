<?php
session_start();
require_once 'config/database.php';

if(!isset($_GET['slug'])) {
    header("Location: index.php");
    exit();
}

$slug = $_GET['slug'];

// Get post data
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                       FROM posts p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.slug = ? AND p.status = 'published'");
$stmt->execute([$slug]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$post) {
    header("Location: index.php");
    exit();
}

// Get related posts
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                       FROM posts p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.category_id = ? AND p.id != ? AND p.status = 'published' 
                       ORDER BY p.created_at DESC LIMIT 3");
$stmt->execute([$post['category_id'], $post['id']]);
$related_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - CMS Sederhana</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/post.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">CMS Sederhana</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <button id="toggleDarkMode" class="btn btn-outline-secondary ms-2" title="Toggle Dark/Light Mode">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <article>
                    <h1 class="mb-3"><?php echo htmlspecialchars($post['title']); ?></h1>
                    <div class="meta mb-3">
                        <span class="badge bg-success"><i class="fas fa-folder"></i> <?php echo htmlspecialchars($post['category_name']); ?></span>
                        <span class="ms-2"><i class="fas fa-calendar-alt"></i> <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                    </div>

                    <?php if($post['featured_image']): ?>
                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" class="featured-img" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <?php endif; ?>

                    <div class="content">
                        <?php echo $post['content']; ?>
                    </div>
                </article>

                <?php if($related_posts): ?>
                <div class="related-posts mt-5">
                    <h3>Related Posts</h3>
                    <div class="row">
                        <?php foreach($related_posts as $related_post): ?>
                        <div class="col-md-4">
                            <div class="card mb-4 related">
                                <?php if($related_post['featured_image']): ?>
                                    <img src="<?php echo htmlspecialchars($related_post['featured_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($related_post['title']); ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="post.php?slug=<?php echo htmlspecialchars($related_post['slug']); ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($related_post['title']); ?>
                                        </a>
                                    </h5>
                                    <p class="card-text text-muted">
                                        <?php echo date('M d, Y', strtotime($related_post['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-md-4 sidebar">
                <!-- Sidebar -->
                <div class="card mb-4">
                    <div class="card-header">
                        Categories
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
                        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach($categories as $category):
                        ?>
                            <a href="category.php?slug=<?php echo htmlspecialchars($category['slug']); ?>" class="d-block mb-2">
                                <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-info-circle me-1"></i> About This Site
                    </div>
                    <div class="card-body">
                        <p class="mb-2">CMS Sederhana adalah platform blog modern, responsif, dan mudah digunakan untuk berbagi informasi, berita, dan inspirasi bagi semua kalangan.</p>
                        <p class="mb-0"><i class="fas fa-users me-1"></i> Untuk penulis, pembaca, dan komunitas!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-3">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> CMS Sederhana. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
    body.dark-mode {
        background: #18191a !important;
        color: #e4e6eb !important;
    }
    body.dark-mode .card,
    body.dark-mode .navbar,
    body.dark-mode .sidebar,
    body.dark-mode .main-content,
    body.dark-mode .footer {
        background: #242526 !important;
        color: #e4e6eb !important;
        border-color: #333 !important;
    }
    body.dark-mode .card-header,
    body.dark-mode .card-footer {
        background: #202124 !important;
        color: #e4e6eb !important;
    }
    body.dark-mode .btn,
    body.dark-mode .btn-primary,
    body.dark-mode .btn-outline-primary {
        background: #3a3b3c !important;
        color: #e4e6eb !important;
        border-color: #555 !important;
    }
    body.dark-mode .table {
        background: #242526 !important;
        color: #e4e6eb !important;
    }
    body.dark-mode .content {
        background: #242526 !important;
        color: #e4e6eb !important;
    }
    body.dark-mode .featured-img,
    body.dark-mode .post-content img {
        background: #18191a !important;
        border-color: #333 !important;
    }
    .post-content img,
    .card-img-top,
    .featured-img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 1.5rem auto;
        background: #fff;
        border-radius: 0.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    body.dark-mode .post-content img,
    body.dark-mode .card-img-top,
    body.dark-mode .featured-img {
        background: #18191a !important;
        border-color: #333 !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (localStorage.getItem('dashboardDarkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }
        const toggleBtn = document.getElementById('toggleDarkMode');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                document.body.classList.toggle('dark-mode');
                localStorage.setItem('dashboardDarkMode', document.body.classList.contains('dark-mode'));
                toggleBtn.innerHTML = document.body.classList.contains('dark-mode')
                    ? '<i class="fas fa-sun"></i>'
                    : '<i class="fas fa-moon"></i>';
            });
            toggleBtn.innerHTML = document.body.classList.contains('dark-mode')
                ? '<i class="fas fa-sun"></i>'
                : '<i class="fas fa-moon"></i>';
        }
    });
    </script>
</body>
</html> 