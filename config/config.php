<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sistem_kepegawaian";

$conn = new mysqli($host, $user, $pass, $db, 3307);


// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// === BASE URL PROJECT ===
// Sesuaikan folder lokasi project kamu
define('APP_BASE', '/sistem_kepegawaian/');

// Helper biar gampang bikin link
function app_url(string $path = ''): string {
    return APP_BASE . ltrim($path, '/');
}
?>
