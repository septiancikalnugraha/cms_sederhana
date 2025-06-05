<?php
require_once BASE_PATH . 'core/Controller.php';
require_once BASE_PATH . 'app/models/Post.php';
require_once BASE_PATH . 'app/models/Category.php';
require_once BASE_PATH . 'app/models/Comment.php';

class HomeController extends Controller {
    private $postModel;
    private $categoryModel;
    private $commentModel;

    public function __construct() {
        parent::__construct();
        $this->postModel = new Post($this->db);
        $this->categoryModel = new Category($this->db);
        $this->commentModel = new Comment($this->db);
    }

    public function index() {
        try {
            // Ambil post yang sudah dipublikasikan
            $posts = $this->postModel->readPublished();
            
            // Ambil semua kategori
            $categories = $this->categoryModel->read();
            
            $data = [
                'posts' => $posts->fetchAll(PDO::FETCH_ASSOC),
                'categories' => $categories->fetchAll(PDO::FETCH_ASSOC)
            ];
            
            $this->loadView('home/index', $data);

        } catch (Exception $e) {
            // Tampilkan halaman error atau pesan jika gagal memuat post
            // Untuk sementara, tampilkan pesan error
            $_SESSION['error'] = 'Gagal memuat post: ' . $e->getMessage();
            $data = [
                'posts' => [], // Kirim array kosong ke view
                'categories' => []
            ]; 
            $this->loadView('home/index', $data);
        }
    }

    public function show($slug) {
        try {
            $post = $this->postModel->readBySlug($slug);

            if (!$post) {
                // Tampilkan halaman 404 jika post tidak ditemukan
                // TODO: Implement 404 page
                http_response_code(404);
                echo "Post tidak ditemukan";
                return;
            }

             // Get all categories
            $categories = $this->categoryModel->read();

            // Get approved comments for this post
            $comments = $this->commentModel->readByPost($post['id']);

            // Handle comment submission
            $error = '';
            $success = '';
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // Basic validation
                if (empty(trim($_POST['name'])) || empty(trim($_POST['email'])) || empty(trim($_POST['content']))) {
                    $error = 'Nama, email, dan komentar harus diisi.';
                } elseif (!filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL)) {
                     $error = 'Format email tidak valid.';
                } else {
                    $this->commentModel->post_id = $post['id'];
                    $this->commentModel->name = trim($_POST['name']);
                    $this->commentModel->email = trim($_POST['email']);
                    $this->commentModel->content = trim($_POST['content']);

                    if($this->commentModel->create()) {
                        $success = 'Komentar Anda telah dikirim dan menunggu persetujuan admin.';
                        // Clear form fields after successful submission if needed
                        // $_POST['name'] = $_POST['email'] = $_POST['content'] = '';
                    } else {
                        $error = 'Gagal mengirim komentar.';
                    }
                }
            }

            $data = [
                'post' => $post,
                'categories' => $categories->fetchAll(PDO::FETCH_ASSOC),
                'comments' => $comments,
                'error' => $error,
                'success' => $success
            ];

            $this->loadView('home/show', $data);

        } catch (Exception $e) {
            // Tampilkan halaman error
            $_SESSION['error'] = 'Gagal memuat post: ' . $e->getMessage();
            // TODO: Implement error page
            http_response_code(500);
            echo "Terjadi kesalahan saat memuat post";
        }
    }

    public function category($slug) {
        try {
            // Get posts by category slug
            $posts = $this->postModel->readByCategory($slug);
            
            // Get category info by slug
            $category = $this->categoryModel->readBySlug($slug);
            
            if (!$category) {
                // Tampilkan halaman 404 jika kategori tidak ditemukan
                // TODO: Implement 404 page
                http_response_code(404);
                echo "Kategori tidak ditemukan";
                return;
            }

            // Get all categories for the sidebar/navigation
            $categories = $this->categoryModel->readWithPostCount();

            $data = [
                'category' => $category,
                'posts' => $posts->fetchAll(PDO::FETCH_ASSOC),
                'categories' => $categories->fetchAll(PDO::FETCH_ASSOC),
            ];

            $this->loadView('home/category', $data);

        } catch (Exception $e) {
            // Tampilkan halaman error
            $_SESSION['error'] = 'Gagal memuat kategori: ' . $e->getMessage();
            // TODO: Implement error page
            http_response_code(500);
            echo "Terjadi kesalahan saat memuat kategori";
        }
    }
} 