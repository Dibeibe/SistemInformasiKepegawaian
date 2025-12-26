<?php
require_once "../../config/config.php";
require_once "../../config/functions.php";
require_login();

// Cek role, hanya admin yang boleh hapus
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Akses ditolak: hanya admin yang dapat menghapus data.');
}

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die('ID tidak valid.');
}

// Cek apakah data pegawai masih ada
$stmt = $conn->prepare("SELECT foto FROM pegawai WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    $stmt->close();
    echo "<script>alert('Data tidak ditemukan!'); window.location='list.php';</script>";
    exit;
}

$data = $res->fetch_assoc();
$stmt->close();

// Hapus dari database
$del = $conn->prepare("DELETE FROM pegawai WHERE id = ?");
$del->bind_param("i", $id);

if ($del->execute()) {
    // Hapus file foto kalau bukan default
    if (!empty($data['foto']) && $data['foto'] !== 'noimage.png') {
        $file = __DIR__ . '/../../assets/images/pegawai/' . $data['foto'];
        if (file_exists($file)) {
            unlink($file);
        }
    }

    echo "<script>
            alert('Data pegawai berhasil dihapus!');
            window.location='list.php';
          </script>";
} else {
    echo "<script>
            alert('Gagal menghapus data: " . addslashes($conn->error) . "');
            window.location='list.php';
          </script>";
}

$del->close();
$conn->close();
