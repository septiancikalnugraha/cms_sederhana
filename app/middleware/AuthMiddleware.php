<?php

// Pastikan session sudah dimulai sebelum menggunakan $_SESSION
// Panggilan session_start() harus dilakukan di titik masuk utama (index.php)
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }

class AuthMiddleware {
    // Metode untuk menangani middleware
    // $requiredRoles adalah array berisi role yang diizinkan untuk rute ini
    public function handle(array $requiredRoles = []) {
        // Jika tidak ada role yang dibutuhkan, izinkan akses (misal: halaman publik)
        if (empty($requiredRoles)) {
            return true;
        }

        // Periksa apakah pengguna sudah login
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
            // Jika belum login, redirect ke halaman login
            $_SESSION['error'] = 'Anda harus login untuk mengakses halaman ini.';
            header('Location: /cms_sederhana/login');
            exit;
        }

        // Ambil role pengguna saat ini dari session
        $currentUserRole = $_SESSION['role'];

        // Periksa apakah role pengguna termasuk dalam daftar role yang diizinkan
        if (!in_array($currentUserRole, $requiredRoles)) {
            // Jika role tidak diizinkan, redirect ke halaman dashboard (atau halaman access denied)
            $_SESSION['error'] = 'Anda tidak memiliki izin untuk mengakses halaman ini.';
            header('Location: /cms_sederhana/dashboard'); // Atau redirect ke halaman 403/access denied
            exit;
        }

        // Jika pengguna memiliki role yang sesuai, izinkan akses
        return true;
    }
} 