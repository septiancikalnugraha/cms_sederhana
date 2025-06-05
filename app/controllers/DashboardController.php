<?php
require_once BASE_PATH . 'core/Controller.php';
require_once BASE_PATH . 'app/models/Post.php';
require_once BASE_PATH . 'app/models/Category.php';

class DashboardController extends Controller {

    private $postModel;
    private $categoryModel;

    public function __construct() {
        parent::__construct(); // Initialize properties from base Controller (db, auth, currentUser)
        $this->postModel = new Post($this->db);
        $this->categoryModel = new Category($this->db);
    }

    public function index() {
        // Check if the user has the required role to access the dashboard
        // Assuming all logged-in users (admin, editor, viewer) can access the dashboard
        $allowedRoles = ['admin', 'editor', 'viewer'];
        if (!$this->checkRole($allowedRoles)) {
            // checkRole() handles redirection if access is denied
            return;
        }

        // Data fetching for the dashboard
        $post_count = $this->postModel->read()->rowCount();
        $category_count = $this->categoryModel->read()->rowCount();

        // Get recent posts (you might want to add a limit to the read() method or create a new method)
        $recent_posts_stmt = $this->postModel->read(); // Assuming read() fetches posts with author and category names, ordered by date
        $recent_posts = $recent_posts_stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [
            'user' => $this->currentUser, // User data from base controller
            'post_count' => $post_count,
            'category_count' => $category_count,
            'recent_posts' => $recent_posts,
        ];

        // Load the dashboard view
        $this->loadView('dashboard/index', $data);
    }

    // Add other dashboard related methods here if needed
} 