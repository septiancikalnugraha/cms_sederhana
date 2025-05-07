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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html> 