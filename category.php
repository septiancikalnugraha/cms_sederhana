<?php
session_start();
require_once 'config/database.php';

if(!isset($_GET['slug'])) {
    header("Location: index.php");
    exit();
}

$slug = $_GET['slug'];

// Get category data
$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
$stmt->execute([$slug]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$category) {
    header("Location: index.php");
    exit();
}

// Get posts in this category
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, u.full_name as author_name 
                       FROM posts p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       LEFT JOIN users u ON p.author_id = u.id 
                       WHERE p.category_id = ? AND p.status = 'published' 
                       ORDER BY p.created_at DESC");
$stmt->execute([$category['id']]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> - CMS Sederhana</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/category.css" rel="stylesheet">
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
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <h1 class="mb-4"><?php echo htmlspecialchars($category['name']); ?></h1>
                <?php if($category['description']): ?>
                    <p class="lead mb-4"><?php echo htmlspecialchars($category['description']); ?></p>
                <?php endif; ?>

                <?php if($posts): ?>
                    <?php foreach($posts as $post): ?>
                        <article class="card mb-4 post-card">
                            <?php if($post['featured_image']): ?>
                                <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h2 class="card-title">
                                    <a href="post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </h2>
                                <div class="meta mb-2">
                                    <span class="badge bg-primary"><i class="fas fa-user"></i> <?php echo htmlspecialchars($post['author_name']); ?></span>
                                    <span class="ms-2"><i class="fas fa-calendar-alt"></i> <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                                </div>
                                <p class="card-text">
                                    <?php echo substr(strip_tags($post['content']), 0, 200) . '...'; ?>
                                </p>
                                <a href="post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="btn btn-primary">Read More</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        No posts found in this category.
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
                        foreach($categories as $cat):
                        ?>
                            <a href="category.php?slug=<?php echo htmlspecialchars($cat['slug']); ?>" class="d-block mb-2">
                                <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($cat['name']); ?>
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
</body>
</html> 