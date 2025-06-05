<?php
require_once BASE_PATH . 'core/Controller.php';
require_once BASE_PATH . 'app/models/Category.php';
require_once BASE_PATH . 'app/models/Post.php';

class CategoryController extends Controller {

    private $categoryModel;
    private $postModel;

    public function __construct() {
        parent::__construct(); // Initialize properties from base Controller (db, auth, currentUser)
        $this->categoryModel = new Category($this->db);
        $this->postModel = new Post($this->db);
    }

    // Display a list of categories (accessible by admin, editor, viewer)
    public function index() {
        try {
            $categories = $this->categoryModel->readWithPostCount();
            $data = [
                'categories' => $categories->fetchAll(PDO::FETCH_ASSOC)
            ];
            $this->loadView('categories/index', $data);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Gagal memuat daftar kategori: ' . $e->getMessage();
            header('Location: /cms_sederhana/dashboard');
            exit;
        }
    }

    // Display the form to create a new category (accessible by admin, editor)
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate input
                if (empty($_POST['name'])) {
                    throw new Exception('Nama kategori harus diisi');
                }

                // Check if category name already exists
                $this->categoryModel->name = $_POST['name'];
                if ($this->categoryModel->nameExists()) {
                    throw new Exception('Nama kategori sudah ada');
                }

                // Set category data
                $this->categoryModel->name = $_POST['name'];
                $this->categoryModel->description = $_POST['description'] ?? '';

                if ($this->categoryModel->create()) {
                    $_SESSION['success'] = 'Kategori berhasil dibuat';
                    header('Location: /cms_sederhana/categories');
                    exit;
                } else {
                    throw new Exception('Gagal membuat kategori');
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                $this->loadView('categories/create');
            }
        } else {
            $this->loadView('categories/create');
        }
    }

    // Display the form to edit an existing category (accessible by admin, editor)
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate input
                if (empty($_POST['name'])) {
                    throw new Exception('Nama kategori harus diisi');
                }

                // Get current category
                $this->categoryModel->id = $id;
                $currentCategory = $this->categoryModel->readOne();

                if (!$currentCategory) {
                    throw new Exception('Kategori tidak ditemukan');
                }

                // Check if new name already exists (excluding current category)
                $this->categoryModel->name = $_POST['name'];
                if ($this->categoryModel->nameExists()) {
                    throw new Exception('Nama kategori sudah ada');
                }

                // Update category
                $this->categoryModel->name = $_POST['name'];
                $this->categoryModel->description = $_POST['description'] ?? '';

                if ($this->categoryModel->update()) {
                    $_SESSION['success'] = 'Kategori berhasil diperbarui';
                    header('Location: /cms_sederhana/categories');
                    exit;
                } else {
                    throw new Exception('Gagal memperbarui kategori');
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                // Get category for the form
                $this->categoryModel->id = $id;
                $category = $this->categoryModel->readOne();
                $data = ['category' => $category];
                $this->loadView('categories/edit', $data);
            }
        } else {
            // Get category for the form
            $this->categoryModel->id = $id;
            $category = $this->categoryModel->readOne();
            $data = ['category' => $category];
            $this->loadView('categories/edit', $data);
        }
    }

    // Delete a category (accessible by admin only, to prevent accidental deletion by editors)
    public function delete($id) {
        try {
            // Get category
            $this->categoryModel->id = $id;
            $category = $this->categoryModel->readOne();

            if (!$category) {
                throw new Exception('Kategori tidak ditemukan');
            }

            // Check if category has posts
            $this->postModel->category_id = $id;
            $posts = $this->postModel->getByCategory($id);
            if ($posts->rowCount() > 0) {
                throw new Exception('Tidak dapat menghapus kategori yang memiliki post');
            }

            if ($this->categoryModel->delete()) {
                $_SESSION['success'] = 'Kategori berhasil dihapus';
            } else {
                throw new Exception('Gagal menghapus kategori');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: /cms_sederhana/categories');
        exit;
    }
} 