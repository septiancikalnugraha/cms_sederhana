<?php

class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            // Ganti namespace separator dengan directory separator
            $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

            // Definisikan root directory untuk pencarian kelas (misalnya, di dalam direktori 'app', 'core')
            // Sesuaikan path ini berdasarkan struktur folder Anda
            $base_dirs = [
                BASE_PATH . 'core/',
                BASE_PATH . 'app/controllers/',
                BASE_PATH . 'app/models/',
                BASE_PATH . 'app/views/', // Walaupun view biasanya tidak di-autoload, jaga-jaga
                BASE_PATH . 'app/middleware/',
                BASE_PATH . 'config/' // Konfigurasi mungkin juga perlu di-autoload jika berupa kelas
            ];

            foreach ($base_dirs as $base_dir) {
                // Bentuk path lengkap ke file kelas
                $file = $base_dir . $class . '.php';
                
                // Jika file ada, muat file tersebut
                if (file_exists($file)) {
                    require_once $file;
                    return true; // Kelas berhasil dimuat
                }

                // Coba juga dengan nama file yang sama dengan kelas di base_dir itu sendiri
                 $file_in_base = $base_dir . basename($class) . '.php';
                 if (file_exists($file_in_base)) {
                     require_once $file_in_base;
                     return true; // Kelas berhasil dimuat
                 }
            }

            // Optional: Jika kelas tidak ditemukan, bisa log error atau throw exception
            // error_log("Class " . $class . " not found.");
            return false; // Kelas tidak ditemukan
        });
    }
} 