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
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .post-image {
            max-height: 400px;
            object-fit: cover;
        }
        .sidebar {
            position: sticky;
            top: 20px;
        }
        .post-content {
            line-height: 1.8;
        }
        .post-content img {
            max-width: 100%;
            height: auto;
        }
        .comment {
            border-left: 3px solid #3498db;
            padding-left: 15px;
            margin-bottom: 20px;
        }
        .comment-meta {
            color: #7f8c8d;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white mb-4">
        <div class="container">
            <a class="navbar-brand" href="/cms_sederhana">CMS Sederhana</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/cms_sederhana">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/cms_sederhana/login">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="row">
            <!-- Post Content -->
            <div class="col-lg-8">
                <article>
                    <h1 class="mb-3"><?php echo htmlspecialchars($post['title']); ?></h1>
                    
                    <div class="d-flex align-items-center text-muted mb-4">
                        <small>
                            By <?php echo htmlspecialchars($post['author_name']); ?> • 
                            <?php echo date('M d, Y', strtotime($post['created_at'])); ?> • 
                            <a href="/cms_sederhana/category/<?php echo htmlspecialchars($post['category_slug']); ?>" 
                               class="text-decoration-none">
                                <?php echo htmlspecialchars($post['category_name']); ?>
                            </a>
                        </small>
                    </div>

                    <?php if($post['featured_image']): ?>
                    <img src="/cms_sederhana/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                         class="img-fluid rounded mb-4 post-image" 
                         alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <?php endif; ?>

                    <div class="post-content">
                        <?php echo $post['content']; ?>
                    </div>
                </article>

                <!-- Comments Section -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Comments (<?php echo count($comments); ?>)</h3>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <?php if (empty($comments)): ?>
                        <p class="text-muted">No comments yet. Be the first to comment!</p>
                        <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <div class="comment-meta mb-2">
                                <strong><?php echo htmlspecialchars($comment['name']); ?></strong> |
                                <?php echo date('M d, Y', strtotime($comment['created_at'])); ?>
                            </div>
                            <div class="comment-content">
                                <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Comment Form -->
                        <h4 class="mt-4 mb-3">Leave a Comment</h4>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="content" class="form-label">Comment</label>
                                <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Comment</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card sidebar">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Categories</h5>
                        <div class="list-group list-group-flush">
                            <?php while ($category = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                            <a href="/cms_sederhana/category/<?php echo htmlspecialchars($category['slug']); ?>" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($category['name']); ?>
                                <span class="badge bg-primary rounded-pill">
                                    <?php echo $category['post_count']; ?>
                                </span>
                            </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light mt-5 py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> CMS Sederhana. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 