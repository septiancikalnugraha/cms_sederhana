<?php

// Konfigurasi Aplikasi Umum

// Pengaturan Error Reporting:
// E_ALL - Tampilkan semua jenis error (untuk pengembangan)
// E_ERROR | E_WARNING | E_PARSE - Tampilkan error, warning, dan parse error (untuk produksi, lebih sedikit detail)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pengaturan Zona Waktu Default
date_default_timezone_set('Asia/Jakarta'); // Ganti dengan zona waktu yang sesuai jika perlu

// Konfigurasi lain bisa ditambahkan di sini, seperti:
// define('APP_NAME', 'CMS Sederhana');
// define('DEFAULT_LANGUAGE', 'id');

?> 