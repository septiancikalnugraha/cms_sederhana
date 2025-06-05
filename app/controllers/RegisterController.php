<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

class RegisterController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function register() {
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->user->username = trim($_POST['username']);
            $this->user->email = trim($_POST['email']);
            $this->user->full_name = trim($_POST['full_name']);
            $this->user->password = $_POST['password'];
            $this->user->role = strtolower($_POST['role']);

            if (!$this->user->username || !$this->user->email || !$this->user->full_name || !$this->user->password || !$this->user->role) {
                $error = 'Semua field harus diisi!';
            } else {
                if ($this->user->checkUserExists()) {
                    $error = 'Username atau email sudah terdaftar!';
                } else {
                    if ($this->user->create()) {
                        $success = 'Registrasi berhasil! Silakan login.';
                    } else {
                        $error = 'Terjadi kesalahan saat registrasi.';
                    }
                }
            }
        }

        return [
            'error' => $error,
            'success' => $success
        ];
    }
} 