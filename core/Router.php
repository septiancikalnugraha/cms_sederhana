<?php

class Router {
    private $routes = [];

    public function addRoute($method, $path, $controller, $action, $roles = []) {
        // Pastikan path rute selalu dimulai dengan slash
        $path = '/' . ltrim($path, '/');
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
            'roles' => $roles
        ];
    }

    public function get($path, $controller, $action, $roles = []) {
        $this->addRoute('GET', $path, $controller, $action, $roles);
    }

    public function post($path, $controller, $action, $roles = []) {
        $this->addRoute('POST', $path, $controller, $action, $roles);
    }

    // Tambahkan metode untuk metode HTTP lain jika diperlukan (PUT, DELETE, etc.)

    public function dispatch($requestMethod, $requestUri) {
        // Hapus logika pemrosesan basePath di sini karena sudah ditangani di index.php
        // Router hanya menerima requestUri yang sudah bersih.

        // Pastikan requestUri dimulai dengan slash, dan adalah '/' untuk root
        $requestUri = '/' . ltrim($requestUri, '/');
        // Hapus trailing slash, kecuali untuk root
        if ($requestUri !== '/') {
            $requestUri = rtrim($requestUri, '/');
        }

        // Iterasi melalui rute yang terdaftar
        foreach ($this->routes as $route) {
            // Cocokkan rute statis secara tepat
            if ($route['method'] === $requestMethod && $route['path'] === $requestUri) {
                $this->executeRoute($route);
                return;
            }

            // Cocokkan rute dinamis menggunakan regex
            // {parameter} akan dicocokkan oleh ([^/]+)
            $routePathPattern = preg_replace('/\{\w+\}/', '([^/]+)', $route['path']);

            // Buat pola regex lengkap
            $regex = '/^' . str_replace('/', '\/', $routePathPattern) . '$/';

            if (preg_match($regex, $requestUri, $matches)) {
                if ($route['method'] === $requestMethod) {
                    // Rute dinamis ditemukan
                    // Hapus elemen pertama ($matches[0] adalah seluruh string yang cocok)
                    array_shift($matches);

                    $this->executeRoute($route, $matches);
                    return;
                }
            }
        }

        // Jika tidak ada rute yang cocok, tampilkan 404
        header("HTTP/1.0 404 Not Found");
        // Include view 404, pastikan pathnya benar
        $notFoundView = BASE_PATH . 'app/views/404.php';
        if (file_exists($notFoundView)) {
            require_once $notFoundView;
        } else {
            echo "404 Not Found"; // Fallback jika view 404 tidak ada
        }
    }

    private function executeRoute($route, $params = []) {
         // RBAC Check
         // Pastikan AuthMiddleware tersedia dan terdaftar
         if (class_exists('AuthMiddleware')) {
             $authMiddleware = new AuthMiddleware();
             // Metode handle di middleware harus mengembalikan true jika diizinkan, dan menangani redirect/error jika tidak
             if (isset($route['roles']) && !$authMiddleware->handle($route['roles'])) {
                 // Middleware sudah menangani redirect, jadi cukup keluar dari sini
                 return; // Penting untuk berhenti setelah redirect/error
             }
         }

         // Muat controller
         // Autoloader seharusnya sudah bisa menemukan controller sekarang
         $controllerName = $route['controller'];
         $controllerFile = BASE_PATH . 'app/controllers/' . $controllerName . '.php';
         
         // Optional: Require explicitely just in case autoloader fails for some reason, but autoloader should handle this.\n         // if (!class_exists($controllerName)) {\n         //    if (file_exists($controllerFile)) {\n         //        require_once $controllerFile;\n         //    }\n         // }\n
        // Periksa apakah kelas controller ada dan metode action ada
        if (!class_exists($controllerName)) {
            echo "Error: Controller class \'" . htmlspecialchars($controllerName) . "\' not found.";
            // Ideally log this error
            return; // Stop execution
        }
        
        $controllerInstance = new $controllerName();
        $actionName = $route['action'];

        if (!method_exists($controllerInstance, $actionName)) {
            echo "Error: Controller method \'" . htmlspecialchars($controllerName) . "::" . htmlspecialchars($actionName) . "\' not found.";
            // Ideally log this error
            return; // Stop execution
        }

        // Panggil metode action pada controller instance, lewati parameter dinamis
        call_user_func_array([$controllerInstance, $actionName], $params);
    }
}

?> 