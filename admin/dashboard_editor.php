<?php
session_start();
require_once '../config/database.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'editor') {
    header("Location: ../index.php");
    exit();
}

// Inisialisasi koneksi database
$database = new Database();
$pdo = $database->getConnection();

$total_posts = 0;
$total_categories = 0;
$recent_posts = [];
$categories_data = [];
$error = '';

// Lanjutkan hanya jika koneksi database berhasil
if ($pdo) {
    // Get statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total_posts FROM posts");
    $total_posts = $stmt->fetch()['total_posts'];

    $stmt = $pdo->query("SELECT COUNT(*) as total_categories FROM categories");
    $total_categories = $stmt->fetch()['total_categories'];

    // Get recent posts
    $stmt = $pdo->query("SELECT p.*, c.name as category_name 
                         FROM posts p 
                         LEFT JOIN categories c ON p.category_id = c.id 
                         ORDER BY p.created_at DESC LIMIT 5");
    $recent_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get categories for chart data
    $stmt = $pdo->query("SELECT c.name, COUNT(p.id) as post_count 
                         FROM categories c 
                         LEFT JOIN posts p ON c.id = p.category_id 
                         GROUP BY c.id 
                         ORDER BY post_count DESC 
                         LIMIT 5");
    $categories_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $error = "Koneksi database gagal. Statistik tidak tersedia.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor Dashboard - CMS Sederhana</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --info-color: #4895ef;
            --warning-color: #f72585;
            --danger-color: #f72585;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 70px;
            --header-height: 60px;
            --transition-speed: 0.3s;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f6f9fc;
            color: #444;
            overflow-x: hidden;
        }

        /* Layout */
        .wrapper {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #3a0ca3 0%, #4361ee 100%);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            transition: all var(--transition-speed) ease;
        }

        .sidebar-collapsed .sidebar {
            width: var(--sidebar-collapsed-width);
        }

        .brand-container {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 20px;
            background: rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .brand-link {
            display: flex;
            align-items: center;
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
            text-decoration: none;
            width: 100%;
            overflow: hidden;
            white-space: nowrap;
        }

        .brand-icon {
            font-size: 1.5rem;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .sidebar-collapsed .brand-text {
            display: none;
        }

        .user-profile {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .user-info {
            overflow: hidden;
        }

        .sidebar-collapsed .user-info {
            display: none;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-block;
        }

        .menu-container {
            padding: 15px 10px;
        }

        .menu-header {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 10px;
            margin-bottom: 10px;
        }

        .sidebar-collapsed .menu-header {
            display: none;
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            overflow: hidden;
        }

        .nav-link:hover, .nav-link:focus {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 500;
        }

        .nav-icon {
            font-size: 1.1rem;
            margin-right: 12px;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar-collapsed .nav-text {
            display: none;
        }

        .sidebar-collapsed .nav-link {
            justify-content: center;
            padding: 12px;
        }

        .sidebar-collapsed .nav-icon {
            margin-right: 0;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left var(--transition-speed) ease;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .sidebar-collapsed .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Header */
        .header {
            height: var(--header-height);
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            padding: 0 25px;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .toggle-sidebar {
            background: none;
            border: none;
            color: #555;
            font-size: 1.25rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            margin-right: 15px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .toggle-sidebar:hover {
            background-color: #f5f5f5;
            color: var(--primary-color);
        }

        .header-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: #333;
            flex-grow: 1;
        }

        .header-actions {
            display: flex;
            align-items: center;
        }

        .header-link {
            display: flex;
            align-items: center;
            color: #666;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 6px;
            transition: all 0.2s;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .header-link:hover {
            background-color: #f5f5f5;
            color: var(--primary-color);
        }

        .header-link i {
            margin-right: 8px;
        }

        /* Content Area */
        .content {
            padding: 25px;
            flex-grow: 1;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            display: flex;
            align-items: center;
            padding: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
        }

        .stat-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border-radius: 12px;
            font-size: 1.5rem;
            margin-right: 20px;
            color: white;
            flex-shrink: 0;
        }

        .stat-icon.posts {
            background: linear-gradient(135deg, #4cc9f0 0%, #4895ef 100%);
        }

        .stat-icon.categories {
            background: linear-gradient(135deg, #3a0ca3 0%, #4361ee 100%);
        }

        .stat-content {
            flex-grow: 1;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 3px;
            line-height: 1;
        }

        .stat-label {
            color: #888;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Charts & Tables */
        .card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 25px;
            border: none;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #eee;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .card-tools {
            display: flex;
            align-items: center;
        }

        .chart-container {
            padding: 20px;
            height: 300px;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            font-size: 0.85rem;
            border-top: none;
            white-space: nowrap;
            color: #666;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }

        .table td {
            padding: 15px 20px;
            vertical-align: middle;
            color: #444;
            border-color: #eee;
        }

        .table th:first-child, .table td:first-child {
            padding-left: 20px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-badge.published {
            background-color: rgba(76, 201, 240, 0.15);
            color: #4895ef;
        }

        .status-badge.draft {
            background-color: rgba(247, 37, 133, 0.15);
            color: #f72585;
        }

        .post-title {
            font-weight: 500;
            color: #333;
            margin: 0;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .category-badge {
            background-color: #f0f2f5;
            color: #666;
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .date-text {
            color: #888;
            font-size: 0.85rem;
        }

        /* Footer */
        .footer {
            background-color: white;
            padding: 15px 25px;
            text-align: center;
            font-size: 0.9rem;
            color: #888;
            border-top: 1px solid #eee;
        }

        .footer a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .action-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            text-align: center;
            padding: 25px 15px;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
        }

        .action-icon {
            width: 50px;
            height: 50px;
            background-color: rgba(67, 97, 238, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: var(--primary-color);
            font-size: 1.25rem;
        }

        .action-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .action-desc {
            color: #888;
            font-size: 0.85rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            :root {
                --sidebar-width: 240px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar-open .sidebar {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            .header {
                padding: 0 15px;
            }
            
            .content {
                padding: 15px;
            }
            
            .header-title {
                display: none;
            }
            
            .chart-container {
                height: 250px;
            }
        }

        /* Dark Mode Toggle */
        .dark-mode-toggle {
            background: none;
            border: none;
            color: #666;
            font-size: 1.25rem;
            cursor: pointer;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .dark-mode-toggle:hover {
            background-color: #f5f5f5;
            color: var(--primary-color);
        }

        /* Tooltip */
        .tooltip-container {
            position: relative;
            display: inline-block;
        }

        .tooltip-content {
            visibility: hidden;
            background-color: #333;
            color: white;
            text-align: center;
            border-radius: 6px;
            padding: 5px 10px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.75rem;
            white-space: nowrap;
        }

        .tooltip-container:hover .tooltip-content {
            visibility: visible;
            opacity: 1;
        }

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
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="brand-container">
                <a href="dashboard_editor.php" class="brand-link">
                    <i class="fas fa-newspaper brand-icon"></i>
                    <span class="brand-text">CMS Sederhana</span>
                </a>
            </div>
            
            <div class="user-profile">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    <span class="user-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
            </div>
            
            <div class="menu-container">
                <div class="menu-header">Main Menu</div>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="dashboard_editor.php" class="nav-link active">
                            <i class="fas fa-home nav-icon"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="posts.php" class="nav-link">
                            <i class="fas fa-file-alt nav-icon"></i>
                            <span class="nav-text">Posts</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="categories.php" class="nav-link">
                            <i class="fas fa-folder nav-icon"></i>
                            <span class="nav-text">Categories</span>
                        </a>
                    </li>
                </ul>
                
                <div class="menu-header mt-4">Content</div>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="posts.php?action=add" class="nav-link">
                            <i class="fas fa-plus-circle nav-icon"></i>
                            <span class="nav-text">New Post</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="media.php" class="nav-link">
                            <i class="fas fa-images nav-icon"></i>
                            <span class="nav-text">Media</span>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="header-title">Dashboard</div>
                
                <div class="d-flex align-items-center ms-auto">
                    <button id="toggleDarkMode" class="btn btn-outline-secondary me-2" title="Toggle Dark/Light Mode">
                        <i class="fas fa-moon"></i>
                    </button>
                    
                    <a href="../index.php" class="header-link tooltip-container">
                        <i class="fas fa-external-link-alt"></i>
                        <span>View Site</span>
                        <span class="tooltip-content">Visit your website</span>
                    </a>
                    
                    <a href="../logout.php" class="header-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </header>
            
            <!-- Content -->
            <div class="content">
                <h1 class="page-title">Dashboard</h1>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon posts">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $total_posts; ?></div>
                            <div class="stat-label">Total Posts</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon categories">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $total_categories; ?></div>
                            <div class="stat-label">Categories</div>
                        </div>
                    </div>
                </div>
                
                <div class="quick-actions">
                    <a href="posts.php?action=add" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h3 class="action-title">Add New Post</h3>
                        <p class="action-desc">Create a new blog post</p>
                    </a>
                    
                    <a href="categories.php?action=add" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-folder-plus"></i>
                        </div>
                        <h3 class="action-title">Add Category</h3>
                        <p class="action-desc">Create a new category</p>
                    </a>
                </div>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Recent Posts</h3>
                                <div class="card-tools">
                                    <a href="posts.php" class="btn btn-sm btn-outline-primary">
                                        View All
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(count($recent_posts) > 0): ?>
                                                <?php foreach($recent_posts as $post): ?>
                                                <tr>
                                                    <td>
                                                        <h6 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h6>
                                                    </td>
                                                    <td>
                                                        <span class="category-badge"><?php echo htmlspecialchars($post['category_name']); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge <?php echo $post['status']; ?>">
                                                            <?php echo ucfirst($post['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="date-text"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center py-4">No posts found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Categories Distribution</h3>
                            </div>
                            <div class="chart-container">
                                <canvas id="categoriesChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Activity Log</h3>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex align-items-center py-3">
                                        <div class="me-3 text-primary">
                                            <i class="fas fa-user-circle fa-lg"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
                                            <div class="text-muted small"><?php echo date('M d, Y H:i'); ?></div>
                                        </div>
                                    </li>
                                    <?php if(count($recent_posts) > 0): ?>
                                    <li class="list-group-item d-flex align-items-center py-3">
                                        <div class="me-3 text-info">
                                            <i class="fas fa-edit fa-lg"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium">Latest Post Created</div>
                                            <div class="text-muted small"><?php echo date('M d, Y', strtotime($recent_posts[0]['created_at'])); ?></div>
                                        </div>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <footer class="footer">
                <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="../index.php">CMS Sederhana</a>.</strong> All rights reserved.
                <div class="float-end">
                    <b>Version</b> 1.0.0
                </div>
            </footer>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar Toggle
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
            
            // For mobile devices
            if (window.innerWidth < 768) {
                document.body.classList.toggle('sidebar-open');
            }
        });
        
        // Handle responsive behavior
        window.addEventListener('resize', function() {
            if (window.innerWidth < 768) {
                document.body.classList.remove('sidebar-collapsed');
                document.body.classList.remove('sidebar-open');
            } else {
                document.body.classList.remove('sidebar-open');
            }
        });
        
        // Dark Mode Toggle
        const darkModeToggle = document.getElementById('toggleDarkMode');
        const htmlElement = document.documentElement;
        
        // Check if user has a preference saved
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            enableDarkMode();
        }
        
        darkModeToggle.addEventListener('click', function() {
            if (htmlElement.classList.contains('dark-theme')) {
                disableDarkMode();
            } else {
                enableDarkMode();
            }
        });
        
        function enableDarkMode() {
            htmlElement.classList.add('dark-theme');
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            localStorage.setItem('theme', 'dark');
            
            // Apply dark mode styles dynamically
            document.head.insertAdjacentHTML('beforeend', `
                <style id="dark-mode-styles">
                    .dark-theme {
                        --light-color: #1a1d21;
                        --dark-color: #e4e6eb;
                    }
                    
                    .dark-theme body {
                        background-color: #121317;
                        color: #e4e6eb;
                    }
                    
                    .dark-theme .header,
                    .dark-theme .footer,
                    .dark-theme .card,
                    .dark-theme .stat-card,
                    .dark-theme .action-card {
                        background-color: #1a1d21;
                        color: #e4e6eb;
                    }
                    
                    .dark-theme .card-header {
                        border-bottom-color: #2d3239;
                    }
                    
                    .dark-theme .card-title,
                    .dark-theme .page-title,
                    .dark-theme .stat-value,
                    .dark-theme .action-title,
                    .dark-theme .post-title {
                        color: #e4e6eb;
                    }
                    
                    .dark-theme .toggle-sidebar,
                    .dark-theme .dark-mode-toggle,
                    .dark-theme .header-link {
                        color: #a0a0a0;
                    }
                    
                    .dark-theme .toggle-sidebar:hover,
                    .dark-theme .dark-mode-toggle:hover,
                    .dark-theme .header-link:hover {
                        background-color: #2d3239;
                    }
                    
                    .dark-theme .table th,
                    .dark-theme .table td {
                        border-color: #2d3239;
                        color: #e4e6eb;
                    }
                    
                    .dark-theme .table th {
                        color: #a0a0a0;
                    }
                    
                    .dark-theme .stat-label,
                    .dark-theme .action-desc,
                    .dark-theme .date-text {
                        color: #a0a0a0;
                    }
                    
                    .dark-theme .category-badge {
                        background-color: #2d3239;
                        color: #a0a0a0;
                    }
                    
                    .dark-theme .list-group-item {
                        background-color: #1a1d21;
                        border-color: #2d3239;
                    }
                    
                    .dark-theme .text-muted {
                        color: #a0a0a0 !important;
                    }
                </style>
            `);
        }
        
        function disableDarkMode() {
            htmlElement.classList.remove('dark-theme');
            darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            localStorage.setItem('theme', 'light');
            
            const darkModeStyles = document.getElementById('dark-mode-styles');
            if (darkModeStyles) {
                darkModeStyles.remove();
            }
        }
        
        // Categories Chart
        const categoriesData = <?php echo json_encode($categories_data); ?>;
        
        if (categoriesData.length > 0) {
            const ctx = document.getElementById('categoriesChart').getContext('2d');
            
            const categoryNames = categoriesData.map(item => item.name);
            const postCounts = categoriesData.map(item => item.post_count);
            
            const categoryColors = [
                'rgba(76, 201, 240, 0.7)',
                'rgba(67, 97, 238, 0.7)',
                'rgba(58, 12, 163, 0.7)',
                'rgba(114, 9, 183, 0.7)',
                'rgba(247, 37, 133, 0.7)'
            ];
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: categoryNames,
                    datasets: [{
                        data: postCounts,
                        backgroundColor: categoryColors,
                        borderColor: 'rgba(255, 255, 255, 0.8)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        title: {
                            display: false
                        }
                    },
                    cutout: '65%'
                }
            });
        } else {
            document.getElementById('categoriesChart').parentNode.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100"><p class="text-muted">No category data available</p></div>';
        }

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