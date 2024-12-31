<?php
// config/database.php
$host = 'localhost';
$dbname = 'library';
$username = 'root';
$password = '';

define('ROOT_PATH', dirname(__DIR__));
define('BASE_URL', 'http://localhost:8000');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper Functions
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireAdmin()
{
    if (!isLoggedIn() || !isAdmin()) {
        redirect('/auth/login.php', 'Anda harus login sebagai admin untuk mengakses halaman ini.', 'error');
    }
}


function redirect($path, $message = '', $type = 'info')
{
    if ($message) {
        $_SESSION['flash'] = [
            'message' => $message,
            'type' => $type
        ];
    }

    if (!str_starts_with($path, '/')) {
        $path = '/' . $path;
    }

    // Tentukan lokasi file atau direktori yang diminta
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $path;

    // Periksa apakah file atau folder ada di server
    if (!file_exists($fullPath)) {
        // Jika file tidak ditemukan, arahkan ke halaman 404
        header("Location: " . BASE_URL . '/404.php');
        exit();
    }

    // Jika file atau folder ada, lanjutkan redirect
    header("Location: " . BASE_URL . $path);
    exit();
}

// Fungsi untuk handle upload
function handleUpload($file, $allowed_types = ['jpg', 'jpeg', 'png'])
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'File tidak ditemukan atau error'];
    }

    $file_info = pathinfo($file['name']);
    $extension = strtolower($file_info['extension']);

    if (!in_array($extension, $allowed_types)) {
        return [false, 'Format file tidak diizinkan'];
    }

    $upload_dir = UPLOAD_PATH . '/covers/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $filename = uniqid() . '.' . $extension;
    $destination = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return [false, 'Gagal mengupload file'];
    }

    return [true, $filename];
}
