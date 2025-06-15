<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'dizi_film_oneri_sistemi');
define('DB_USER', 'root');
define('DB_PASS', '');

// TMDB API configuration
define('TMDB_API_KEY', getenv('TMDB_API_KEY') ?: '3fb7d900dbc90b58d44c338c48d8e855');
define('TMDB_API_URL', 'https://api.themoviedb.org/3');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1); // Geliştirme aşamasında hataları göster
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Localhost için 0 yapıldı
ini_set('session.cookie_samesite', 'Lax'); // Localhost için Lax yapıldı
ini_set('session.gc_maxlifetime', 3600);

// Database connection using PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Veritabanına bağlanırken bir hata oluştu. Lütfen daha sonra tekrar deneyin.");
}

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// XSS Protection
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Content-Security-Policy: default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval';"); 