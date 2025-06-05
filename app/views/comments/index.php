<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments - CMS Sederhana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #2c3e50;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 10px 20px;
            margin: 5px 0;
        }
        .sidebar .nav-link:hover {
            background-color: #34495e;
        }
        .sidebar .nav-link.active {
            background-color: #3498db;
        }
        .main-content {
            padding: 20px;
        }
        .card-dashboard {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .comment-content {
            max-height: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="d-flex flex-column">
                    <h4 class="text-white text-center mb-4">CMS Sederhana</h4>
                    <a href="/cms_sederhana/dashboard" class="nav-link">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <a href="/cms_sederhana/posts" class="nav-link">
                        <i class="fas fa-file-alt me-2"></i> Posts
                    </a>
                    <a href="/cms_sederhana/categories" class="nav-link">
                        <i class="fas fa-folder me-2"></i> Categories
                    </a>
                    <a href="/cms_sederhana/comments" class="nav-link active">
                        <i class="fas fa-comments me-2"></i> Comments
                    </a>
                    <a href="/cms_sederhana/users" class="nav-link">
                        <i class="fas fa-users me-2"></i> Users
                    </a>
                    <a href="/cms_sederhana/logout" class="nav-link">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Comments</h2>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
                <?php endif; ?>

                <div class="card card-dashboard">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Post</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Comment</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($comments as $comment): ?>
                                    <tr>
                                        <td>
                                            <a href="/cms_sederhana/post/<?php echo $comment['post_slug']; ?>" target="_blank">
                                                <?php echo htmlspecialchars($comment['post_title']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($comment['name']); ?></td>
                                        <td><?php echo htmlspecialchars($comment['email']); ?></td>
                                        <td>
                                            <div class="comment-content">
                                                <?php echo htmlspecialchars($comment['content']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($comment['status'] == 'pending'): ?>
                                            <span class="badge bg-warning">Pending</span>
                                            <?php elseif ($comment['status'] == 'approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                            <?php else: ?>
                                            <span class="badge bg-danger">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($comment['created_at'])); ?></td>
                                        <td>
                                            <?php if ($comment['status'] == 'pending'): ?>
                                            <a href="/cms_sederhana/comments/approve/<?php echo $comment['id']; ?>" 
                                               class="btn btn-sm btn-success" 
                                               onclick="return confirm('Are you sure you want to approve this comment?')">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="/cms_sederhana/comments/reject/<?php echo $comment['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to reject this comment?')">
                                                <i class="fas fa-times"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="/cms_sederhana/comments/delete/<?php echo $comment['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this comment?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 