<?php

// Example Route:
// $router->addRoute('GET', '/', 'HomeController', 'index');

// Frontend Routes (accessible by anyone)
// Homepage
$router->addRoute('GET', '/', 'HomeController', 'index');

// Single Article Page (assuming articles are accessed by ID)
$router->addRoute('GET', '/article/{id}', 'PostController', 'show'); // Assuming a PostController with a show method

// Category Page (assuming categories are accessed by ID or slug)
$router->addRoute('GET', '/category/{id}', 'CategoryController', 'show'); // Assuming a CategoryController with a show method

// Auth Routes (accessible by anyone)
$router->get('/login', 'AuthController', 'login');
$router->post('/login', 'AuthController', 'login');
$router->get('/register', 'AuthController', 'register');
$router->post('/register', 'AuthController', 'register');
$router->get('/logout', 'AuthController', 'logout');

// Admin/Dashboard Routes (accessible by logged-in users)
$router->get('/dashboard', 'DashboardController', 'index', ['admin', 'editor', 'view']); // Changed 'viewer' to 'view' based on previous debug

// Category Management Routes (accessible by admin and editor)
// Note: Access control for specific actions (create, edit, delete) is handled by AuthMiddleware based on roles
$router->get('/categories', 'CategoryController', 'index', ['admin', 'editor', 'view']); // Viewer can see the list
$router->get('/categories/create', 'CategoryController', 'create', ['admin', 'editor']);
$router->post('/categories/create', 'CategoryController', 'create', ['admin', 'editor']);
$router->get('/categories/edit/{id}', 'CategoryController', 'edit', ['admin', 'editor']);
$router->post('/categories/edit/{id}', 'CategoryController', 'edit', ['admin', 'editor']);
// Delete route restricted to admin only
$router->get('/categories/delete/{id}', 'CategoryController', 'delete', ['admin']);
$router->post('/categories/delete/{id}', 'CategoryController', 'delete', ['admin']); // Allow POST too for forms if needed

// Post Management Routes
// Note: Access control for specific actions (create, edit, delete) is handled by AuthMiddleware based on roles
$router->get('/posts', 'PostController', 'index', ['admin', 'editor', 'view']); // Viewer can see the list
$router->get('/posts/create', 'PostController', 'create', ['admin', 'editor']);
$router->post('/posts/create', 'PostController', 'create', ['admin', 'editor']);
$router->get('/posts/edit/{id}', 'PostController', 'edit', ['admin', 'editor']);
$router->post('/posts/edit/{id}', 'PostController', 'edit', ['admin', 'editor']);
// Delete route: Editor can only delete their own posts, Admin can delete any
$router->get('/posts/delete/{id}', 'PostController', 'delete', ['admin', 'editor']); 
$router->post('/posts/delete/{id}', 'PostController', 'delete', ['admin', 'editor']); // Allow POST too for forms if needed

// Add profile route (assuming it's a simple page)
$router->get('/profile', 'UserController', 'profile', ['admin', 'editor', 'view']); // Assuming a UserController with a profile method

// TODO: Refine roles for specific actions (e.g., editor only deletes their posts)
// TODO: Add routes for Comments management if applicable

// TODO: Add routes for Users, Comments, and frontend views 