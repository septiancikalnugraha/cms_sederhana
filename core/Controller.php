<?php

require_once BASE_PATH . 'app/models/Auth.php';
require_once BASE_PATH . 'app/models/User.php';
require_once BASE_PATH . 'config/Database.php';

class Controller {
    protected $db;
    protected $auth;
    protected $currentUser;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->auth = new Auth($this->db);
        $this->currentUser = $this->auth->getCurrentUser();
    }

    protected function loadView($view, $data = []) {
        // Extract data to make variables available in the view
        extract($data);

        // Define the path to the view file
        $viewPath = BASE_PATH . 'app/views/' . $view . '.php';

        // Check if the view file exists
        if (file_exists($viewPath)) {
            // Use output buffering to capture the view content
            ob_start();
            require_once $viewPath;
            $output = ob_get_clean();

            // Output the captured content
            echo $output;
        } else {
            // Handle view not found error (e.g., log error, show generic message)
            // Log the error instead of echoing directly in production
            error_log("Error: View file not found: " . $viewPath);
            echo "Error: View file not found."; // Friendly error message for user
        }
    }

    // Check if the current user has one of the allowed roles
    protected function checkRole($allowedRoles) {
        // Ensure user is logged in first
        if (!$this->auth->isLoggedIn()) {
            header('Location: /cms_sederhana/login'); // Redirect to login if not logged in
            exit;
        }

        // Check if current user data is available
        if (!$this->currentUser) {
             // Should not happen if isLoggedIn() is true, but as a fallback
             header('Location: /cms_sederhana/logout'); // Logout if user data is missing
             exit;
        }

        // Create a User object with the current user's data to use hasRole method
        // We need to pass the db connection, but it's not strictly needed for hasRole
        // A simpler approach might be to just pass the role string.
        // Let's pass the user object data directly and create a temporary User object.
        $tempUser = new User($this->db); // Passing db might be redundant for hasRole check
        // Manually set the role for the temporary user object
        $tempUser->role = $this->currentUser['role'];


        if (!$tempUser->hasRole($allowedRoles)) {
            // User does not have the required role
            // Redirect to dashboard or show an unauthorized message
            header('Location: /cms_sederhana/dashboard'); // Redirect to dashboard (or access denied page)
            exit;
        }

        return true; // User has the required role
    }
} 