<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';
auth_check();

$page_title = "Laporan Absensi";
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';

// Ambil data absensi
$sql = "SELECT a.id, p.nip, p.nama, a.tanggal, a.jam_masuk, a.jam_keluar, a.status
        FROM absensi a
        LEFT JOIN pegawai p ON a.pegawai_id=p.id
        ORDER BY a.tanggal DESC";
$result = $conn->query($sql);
?>

<div class="container">
    <h3><?= $page_title; ?></h3>
    <a href="export.php?type=absensi" class="btn btn-success btn-sm mb-2">Export Excel</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>NIP</th>
                <th>Nama</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['nip']); ?></td>
                <td><?= htmlspecialchars($row['nama']); ?></td>
                <td><?= htmlspecialchars($row['tanggal']); ?></td>
                <td><?= htmlspecialchars($row['jam_masuk']); ?></td>
                <td><?= htmlspecialchars($row['jam_keluar']); ?></td>
                <td><?= htmlspecialchars($row['status']); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
