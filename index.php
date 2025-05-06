<?php
session_start();
require_once 'config/database.php';

// Get all published posts
$stmt = $pdo->query("SELECT p.*, c.name as category_name, u.full_name as author_name 
                     FROM posts p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     LEFT JOIN users u ON p.author_id = u.id 
                     WHERE p.status = 'published' 
                     ORDER BY p.created_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Sederhana</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
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
                <?php foreach($posts as $post): ?>
                    <article class="card mb-4">
                        <?php if($post['featured_image']): ?>
                            <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h2 class="card-title">
                                <a href="post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h2>
                            <p class="card-text text-muted">
                                By <?php echo htmlspecialchars($post['author_name']); ?> in 
                                <?php echo htmlspecialchars($post['category_name']); ?> | 
                                <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                            </p>
                            <p class="card-text">
                                <?php echo substr(strip_tags($post['content']), 0, 200) . '...'; ?>
                            </p>
                            <a href="post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="btn btn-primary">Read More</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="col-md-4">
                <!-- Sidebar -->
                <div class="card">
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
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        <?php endforeach; ?>
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