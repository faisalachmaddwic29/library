<?php
// auth/logout.php
require_once '../config/database.php';

// Clear all session data
session_unset();
session_destroy();

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Tambahkan header untuk mencegah cache halaman
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Set expired date

redirect('/auth/login.php', 'Anda telah berhasil logout.', 'success');