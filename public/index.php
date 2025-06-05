<?php

// Start the session if it hasn't been started yet
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define base path
define('BASE_PATH', str_replace('\\', '/', __DIR__ . '/../'));

// Register the autoloader
require_once BASE_PATH . 'core/Autoloader.php';
Autoloader::register();

// Include necessary files
require_once BASE_PATH . 'config/config.php';
require_once BASE_PATH . 'config/Database.php';
require_once BASE_PATH . 'core/Router.php';
require_once BASE_PATH . 'app/middleware/AuthMiddleware.php';

// Database connection
$database = new Database();
$db = $database->getConnection();

// Router initialization
$router = new Router();

// Load routes
require_once BASE_PATH . 'config/routes.php';

// Get the requested URI and method
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // Get only the path
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Define the base path of your application if it's in a subdirectory
$basePath = '/cms_sederhana';

// Remove base path from request URI and ensure it starts with a slash
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Ensure $requestUri starts with a slash, and is just '/' for the root
$requestUri = '/' . ltrim($requestUri, '/');

// --- Debugging --- //
echo 'Debug: Processed REQUEST_URI = ' . htmlspecialchars($requestUri) . '<br>';
// --- End Debugging --- //

// Dispatch the request
$router->dispatch($requestMethod, $requestUri); 