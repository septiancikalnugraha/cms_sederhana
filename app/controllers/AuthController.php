<?php

require_once BASE_PATH . 'core/Controller.php';
require_once BASE_PATH . 'app/models/Auth.php';

class AuthController extends Controller {

    private $authModel;

    public function __construct() {
        parent::__construct(); // Initialize properties from base Controller
        $this->authModel = new Auth($this->db); // Auth model needs DB connection
    }

    public function login() {
        $data = [];
        $data['error'] = '';
        $data['success'] = '';

        // If user is already logged in, redirect to dashboard
        if ($this->authModel->isLoggedIn()) {
            header('Location: /cms_sederhana/dashboard'); // Redirect to dashboard
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $data['error'] = 'Username dan password harus diisi.';
            } else {
                $loggedInUser = $this->authModel->login($username, $password);

                if ($loggedInUser) {
                    // Set session variables upon successful login
                    $_SESSION['user_id'] = $loggedInUser['id'];
                    // Store more user data in session if needed, e.g., role
                    $_SESSION['user_role'] = $loggedInUser['role'];

                    // Redirect to dashboard or a role-specific page
                    // For now, redirect all to dashboard. RBAC in controllers will handle access within dashboard.
                    header('Location: /cms_sederhana/dashboard'); 
                    exit;
                } else {
                    $data['error'] = 'Username atau password salah.';
                }
            }
        }

        // Load the login view, passing error/success messages
        $this->loadView('login', $data);
    }

    public function logout() {
        $this->authModel->logout();
        // Redirect to login page after logout
        header('Location: /cms_sederhana/login');
        exit;
    }

    // Method to display registration form (optional, based on requirements)
    public function register() {
        // Check if registration is allowed (e.g., only by admin, or open registration)
        // For now, let's assume registration is open for simplicity, but it should be controlled.

        $data = [];
        $data['error'] = '';
        $data['success'] = '';

        // If registration is handled via POST, process here
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             require_once BASE_PATH . 'app/models/User.php'; // Include User model if not already via parent
             $userModel = new User($this->db);

             $userModel->username = trim($_POST['username'] ?? '');
             $userModel->email = trim($_POST['email'] ?? '');
             $userModel->full_name = trim($_POST['full_name'] ?? '');
             $userModel->password = $_POST['password'] ?? '';
             // Default role for new registrations
             $userModel->role = 'viewer'; // Or another default role

             if (empty($userModel->username) || empty($userModel->email) || empty($userModel->full_name) || empty($userModel->password)) {
                 $data['error'] = 'Semua field harus diisi.';
             } else if ($userModel->checkUserExists()) {
                  $data['error'] = 'Username atau email sudah terdaftar.';
             } else {
                  if ($userModel->create()) {
                      $data['success'] = 'Registrasi berhasil! Silakan login.';
                  } else {
                      $data['error'] = 'Terjadi kesalahan saat registrasi.';
                  }
             }
        }

        // Load the registration view
        $this->loadView('register', $data);
    }

} 