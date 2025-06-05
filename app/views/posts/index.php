<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts - CMS Sederhana</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/cms_sederhana/assets/css/style.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
        }
        .sidebar .nav-link:hover {
            color: rgba(255,255,255,1);
        }
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,.1);
        }
        .main-content {
            padding: 20px;
        }
        .card-dashboard {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <h4 class="text-center mb-4">CMS Sederhana</h4>
                     <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/cms_sederhana/dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/cms_sederhana/posts">
                                <i class="fas fa-file-alt me-2"></i> Posts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/cms_sederhana/categories">
                                <i class="fas fa-folder me-2"></i> Categories
                            </a>
                        </li>
                         <li class="nav-item">
                            <a class="nav-link" href="/cms_sederhana/users">
                                <i class="fas fa-users me-2"></i> Users
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link text-danger" href="/cms_sederhana/logout">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage Posts</h2>
                     <?php if ($user && ($user['role'] === 'admin' || $user['role'] === 'editor')): // Only admin/editor can create ?>
                         <a href="/cms_sederhana/posts/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Add New Post
                        </a>
                    <?php endif; ?>
                </div>

                 <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card card-dashboard">
                    <div class="card-body">
                         <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Author</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($posts)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No posts found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($posts as $post): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                                <td><?php echo htmlspecialchars($post['category_name']); ?></td>
                                                <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $post['status'] == 'published' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($post['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?></td>
                                                <td>
                                                     <?php if ($user && ($user['role'] === 'admin' || ($user['role'] === 'editor' && $post['author_id'] === $user['id']))): // Admin can edit any, Editor can only edit their own ?>
                                                        <a href="/cms_sederhana/posts/edit/<?php echo $post['id']; ?>" class="btn btn-sm btn-warning me-2"><i class="fas fa-edit"></i> Edit</a>
                                                    <?php endif; ?>
                                                    <?php if ($user && ($user['role'] === 'admin' || ($user['role'] === 'editor' && $post['author_id'] === $user['id']))): // Admin can delete any, Editor can only delete their own ?>
                                                         <a href="/cms_sederhana/posts/delete/<?php echo $post['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this post?');"><i class="fas fa-trash"></i> Delete</a>
                                                    <?php endif; ?>
                                                </td>
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
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
     <!-- TinyMCE (if needed for index view, e.g., for preview - but likely not needed here) -->
    <!-- <script src="https://cdn.tiny.cloud/1/YOUR_API_KEY/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script> -->
</body>
</html> 