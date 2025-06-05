<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Latest Posts - CMS Sederhana</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="/cms_sederhana/assets/css/style.css" rel="stylesheet">
    <style>
        .post-card {
            margin-bottom: 30px;
        }
        .post-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }
        .post-card .card-body {
            padding: 15px;
        }
        .post-card .card-title {
            font-size: 1.25rem;
            margin-bottom: 10px;
        }
        .post-card .post-meta {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .post-card .post-meta a {
             text-decoration: none;
             color: #6c757d;
        }
         .post-card .post-meta a:hover {
             text-decoration: underline;
         }
        .post-card .post-excerpt {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="/cms_sederhana/">CMS Sederhana</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="/cms_sederhana/">Home</a>
                    </li>
                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownCategories" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Categories
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownCategories">
                            <?php
                            // Pastikan $categories ada dan berupa array sebelum diulang
                            if (isset($categories) && is_array($categories)) {
                                // Loop melalui setiap kategori
                                foreach ($categories as $category) {
                                    // Pastikan $category adalah array asosiatif dan memiliki kunci 'slug' dan 'name'
                                    if (is_array($category) && isset($category['slug'], $category['name'])) {
                                        // Tampilkan link kategori
                                        echo '<li><a class="dropdown-item" href="/cms_sederhana/category/' . htmlspecialchars($category['slug']) . '">' . htmlspecialchars($category['name']) . '</a></li>';
                                    } else {
                                        // Optional: Log warning jika format item kategori salah
                                        // error_log('Warning: Invalid category data format in home/index.php');
                                    }
                                }
                            } else {
                                // Tampilkan pesan jika tidak ada kategori atau $categories bukan array
                                echo '<li><span class="dropdown-item">No categories available</span></li>';
                            }
                            ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/cms_sederhana/dashboard">Dashboard</a>
                    </li>
                    <?php // Check if user is logged in ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/cms_sederhana/logout">Logout</a>
                        </li>
                    <?php else: ?>
                         <li class="nav-item">
                            <a class="nav-link" href="/cms_sederhana/login">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Latest Posts</h2>

        <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php
            // Pastikan $posts ada dan berupa array sebelum diulang
            if (isset($posts) && is_array($posts) && count($posts) > 0) {
                // Loop melalui setiap post
                foreach ($posts as $post) {
                    // Pastikan $post adalah array asosiatif
                    if (is_array($post)) {
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card post-card">
                                <?php if (isset($post['featured_image']) && !empty($post['featured_image'])): ?>
                                    <img src="/cms_sederhana/<?php echo htmlspecialchars($post['featured_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title'] ?? 'Post Image'); ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($post['title'] ?? 'No Title'); ?></h5>
                                    <p class="post-meta">
                                        By <?php echo htmlspecialchars($post['author_name'] ?? 'Unknown Author'); ?> •
                                        <?php echo isset($post['created_at']) ? date('M d, Y', strtotime($post['created_at'])) : 'Unknown Date'; ?> •
                                        <?php if (isset($post['category_name']) && !empty($post['category_name'])): ?>
                                            <a href="/cms_sederhana/category/<?php echo htmlspecialchars($post['category_slug'] ?? '#'); ?>">
                                                <?php echo htmlspecialchars($post['category_name']); ?>
                                            </a>
                                        <?php endif; ?>
                                    </p>
                                    <p class="card-text post-excerpt">
                                        <?php // Tampilkan kutipan singkat konten (misal, 150 karakter pertama) ?>
                                        <?php
                                        $content_excerpt = strip_tags($post['content'] ?? ''); // Hapus tag HTML
                                        echo htmlspecialchars(substr($content_excerpt, 0, 150)) . (strlen($content_excerpt) > 150 ? '...' : '');
                                        ?>
                                    </p>
                                    <a href="/cms_sederhana/post/<?php echo htmlspecialchars($post['slug'] ?? '#'); ?>" class="btn btn-primary">Read More</a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
            } else {
                // Tampilkan pesan jika tidak ada post
                ?>
                <div class="col-12">
                    <p>No posts found.</p>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 