<?php
require_once 'config/database.php';
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM posts p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) {
    echo "<div style='padding:2rem;text-align:center;'>Artikel tidak ditemukan.<br><a href='index.php'>Kembali</a></div>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - CMS Sederhana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/article-detail.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <a href="admin/dashboard_view.php" class="btn btn-primary mb-3">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <button id="toggleDarkMode" class="btn btn-outline-secondary ms-2" title="Toggle Dark/Light Mode">
                            <i class="fas fa-moon"></i>
                        </button>
                        <h1 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                        <div class="post-meta mb-3">
                            <span><i class="fas fa-folder"></i> <?php echo htmlspecialchars($post['category_name']); ?></span>
                            <span><i class="fas fa-calendar"></i> <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                        </div>
                        <div class="post-content">
                            <?php echo $post['content']; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <!-- Sidebar content -->
            </div>
        </div>
    </div>
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
    body.dark-mode .card-img-top,
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html> 