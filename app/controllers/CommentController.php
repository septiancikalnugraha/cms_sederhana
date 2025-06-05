<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Auth.php';
require_once __DIR__ . '/../models/Comment.php';

class CommentController {
    private $db;
    private $auth;
    private $comment;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->auth = new Auth($this->db);
        $this->comment = new Comment($this->db);
    }

    public function create() {
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->comment->post_id = $_POST['post_id'];
            $this->comment->name = trim($_POST['name']);
            $this->comment->email = trim($_POST['email']);
            $this->comment->content = trim($_POST['content']);

            if($this->comment->create()) {
                $success = 'Komentar Anda telah dikirim dan menunggu persetujuan admin.';
            } else {
                $error = 'Terjadi kesalahan saat mengirim komentar.';
            }
        }

        return [
            'error' => $error,
            'success' => $success
        ];
    }

    public function index() {
        // Check if user is logged in
        $user = $this->auth->checkAuth();
        if (!$user) {
            header('Location: /cms_sederhana/login');
            exit;
        }

        // Get all comments
        $comments = $this->comment->readAll();

        return [
            'user' => $user,
            'comments' => $comments
        ];
    }

    public function approve($id) {
        // Check if user is logged in
        $user = $this->auth->checkAuth();
        if (!$user) {
            header('Location: /cms_sederhana/login');
            exit;
        }

        if($this->comment->updateStatus($id, 'approved')) {
            $_SESSION['success'] = 'Komentar berhasil disetujui.';
        } else {
            $_SESSION['error'] = 'Terjadi kesalahan saat menyetujui komentar.';
        }

        header('Location: /cms_sederhana/comments');
        exit;
    }

    public function reject($id) {
        // Check if user is logged in
        $user = $this->auth->checkAuth();
        if (!$user) {
            header('Location: /cms_sederhana/login');
            exit;
        }

        if($this->comment->updateStatus($id, 'rejected')) {
            $_SESSION['success'] = 'Komentar berhasil ditolak.';
        } else {
            $_SESSION['error'] = 'Terjadi kesalahan saat menolak komentar.';
        }

        header('Location: /cms_sederhana/comments');
        exit;
    }

    public function delete($id) {
        // Check if user is logged in
        $user = $this->auth->checkAuth();
        if (!$user) {
            header('Location: /cms_sederhana/login');
            exit;
        }

        $this->comment->id = $id;
        if($this->comment->delete()) {
            $_SESSION['success'] = 'Komentar berhasil dihapus.';
        } else {
            $_SESSION['error'] = 'Terjadi kesalahan saat menghapus komentar.';
        }

        header('Location: /cms_sederhana/comments');
        exit;
    }
} 