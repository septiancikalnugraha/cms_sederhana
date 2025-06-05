<?php
require_once BASE_PATH . 'core/Controller.php';
require_once BASE_PATH . 'app/models/Post.php';
require_once BASE_PATH . 'app/models/Category.php'; // Assuming Post creation/editing needs categories

class PostController extends Controller {

    private $postModel;
    private $categoryModel;
    private $uploadDir = 'uploads/posts/';

    public function __construct() {
        parent::__construct(); // Initialize properties from base Controller (db, auth, currentUser)
        $this->postModel = new Post($this->db);
        $this->categoryModel = new Category($this->db);
        
        // Create upload directory if it doesn't exist
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    // Display a list of posts (accessible by admin, editor, viewer)
    public function index() {
        $allowedRoles = ['admin', 'editor', 'viewer'];
        if (!$this->checkRole($allowedRoles)) {
            return; // checkRole handles redirection
        }

        // Fetch posts (all posts for admin/editor, maybe only published for viewer depending on requirements)
        // For simplicity now, fetching all posts as read() does
        $posts_stmt = $this->postModel->read(); 
        $posts = $posts_stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [
            'user' => $this->currentUser,
            'posts' => $posts,
        ];

        $this->loadView('posts/index', $data);
    }

    // Display the form to create a new post (accessible by admin, editor)
    public function create() {
        $allowedRoles = ['admin', 'editor'];
        if (!$this->checkRole($allowedRoles)) {
            return; // checkRole handles redirection
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate input
                if (empty($_POST['title']) || empty($_POST['content']) || empty($_POST['category_id'])) {
                    throw new Exception('Semua field harus diisi');
                }

                // Handle file upload
                $featured_image = null;
                if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                    $featured_image = $this->handleFileUpload($_FILES['featured_image']);
                }

                // Set post data
                $this->postModel->title = $_POST['title'];
                $this->postModel->content = $_POST['content'];
                $this->postModel->category_id = $_POST['category_id'];
                $this->postModel->author_id = $this->currentUser['id'];
                $this->postModel->status = $_POST['status'] ?? 'draft';
                $this->postModel->featured_image = $featured_image;

                if ($this->postModel->create()) {
                    $_SESSION['success'] = 'Post berhasil dibuat';
                    header('Location: /cms_sederhana/posts');
                    exit;
                } else {
                    throw new Exception('Gagal membuat post');
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                // Get categories for the form
                $categories = $this->categoryModel->read();
                $data = [
                    'categories' => $categories->fetchAll(PDO::FETCH_ASSOC),
                ];
                $this->loadView('posts/create', $data);
            }
        } else {
            // Get categories for the form
            $categories = $this->categoryModel->read();
            $data = [
                'categories' => $categories->fetchAll(PDO::FETCH_ASSOC),
            ];
            $this->loadView('posts/create', $data);
        }
    }

    // Display the form to edit an existing post (accessible by admin, editor)
    public function edit($id) {
        $allowedRoles = ['admin', 'editor'];
        if (!$this->checkRole($allowedRoles)) {
            return; // checkRole handles redirection
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate input
                if (empty($_POST['title']) || empty($_POST['content']) || empty($_POST['category_id'])) {
                    throw new Exception('Semua field harus diisi');
                }

                // Get current post
                $this->postModel->id = $id;
                $currentPost = $this->postModel->readOne();

                if (!$currentPost) {
                    throw new Exception('Post tidak ditemukan');
                }

                // Check if user has permission to edit
                if ($this->currentUser['role'] !== 'admin' && $currentPost['author_id'] !== $this->currentUser['id']) {
                    throw new Exception('Anda tidak memiliki akses untuk mengedit post ini');
                }

                // Handle file upload
                $featured_image = $currentPost['featured_image'];
                if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                    // Delete old image if exists
                    if ($featured_image && file_exists($featured_image)) {
                        unlink($featured_image);
                    }
                    $featured_image = $this->handleFileUpload($_FILES['featured_image']);
                }

                // Update post
                $this->postModel->title = $_POST['title'];
                $this->postModel->content = $_POST['content'];
                $this->postModel->category_id = $_POST['category_id'];
                $this->postModel->status = $_POST['status'] ?? $currentPost['status'];
                $this->postModel->featured_image = $featured_image;

                if ($this->postModel->update()) {
                    $_SESSION['success'] = 'Post berhasil diperbarui';
                    header('Location: /cms_sederhana/posts');
                    exit;
                } else {
                    throw new Exception('Gagal memperbarui post');
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                // Get post and categories for the form
                $this->postModel->id = $id;
                $post = $this->postModel->readOne();
                $categories = $this->categoryModel->read();
                $data = [
                    'post' => $post,
                    'categories' => $categories->fetchAll(PDO::FETCH_ASSOC),
                ];
                $this->loadView('posts/edit', $data);
            }
        } else {
            // Get post and categories for the form
            $this->postModel->id = $id;
            $post = $this->postModel->readOne();
            $categories = $this->categoryModel->read();
            $data = [
                'post' => $post,
                'categories' => $categories->fetchAll(PDO::FETCH_ASSOC),
            ];
            $this->loadView('posts/edit', $data);
        }
    }

    // Delete a post (accessible by admin, editor - editor can only delete their own)
    public function delete($id) {
        $allowedRoles = ['admin', 'editor'];
        if (!$this->checkRole($allowedRoles)) {
            return; // checkRole handles redirection
        }

        try {
            // Get post
            $this->postModel->id = $id;
            $post = $this->postModel->readOne();

            if (!$post) {
                throw new Exception('Post tidak ditemukan');
            }

            // Check if user has permission to delete
            if ($this->currentUser['role'] !== 'admin' && $post['author_id'] !== $this->currentUser['id']) {
                throw new Exception('Anda tidak memiliki akses untuk menghapus post ini');
            }

            // Delete featured image if exists
            if ($post['featured_image'] && file_exists($post['featured_image'])) {
                unlink($post['featured_image']);
            }

            if ($this->postModel->delete()) {
                $_SESSION['success'] = 'Post berhasil dihapus';
            } else {
                throw new Exception('Gagal menghapus post');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: /cms_sederhana/posts');
        exit;
    }

    private function handleFileUpload($file) {
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipe file tidak didukung. Gunakan JPG, PNG, atau GIF');
        }

        // Validate file size (2MB max)
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception('Ukuran file terlalu besar. Maksimal 2MB');
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $this->uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Gagal mengupload file');
        }

        return $filepath;
    }

    // You might add methods for viewing a single post on the admin side if needed, 
    // but typically post details are viewed on the frontend via HomeController.
} 