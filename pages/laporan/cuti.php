<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';
auth_check();

$page_title = "Laporan Cuti";
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';

// Ambil data cuti
$sql = "SELECT c.id, p.nip, p.nama, c.tanggal_mulai, c.tanggal_selesai, c.jenis_cuti, c.status
        FROM cuti c
        LEFT JOIN pegawai p ON c.pegawai_id=p.id
        ORDER BY c.tanggal_mulai DESC";
$result = $conn->query($sql);
?>

<div class="container">
    <h3><?= $page_title; ?></h3>
    <a href="export.php?type=cuti" class="btn btn-success btn-sm mb-2">Export Excel</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>NIP</th>
                <th>Nama</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Selesai</th>
                <th>Jenis Cuti</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['nip']); ?></td>
                <td><?= htmlspecialchars($row['nama']); ?></td>
                <td><?= htmlspecialchars($row['tanggal_mulai']); ?></td>
                <td><?= htmlspecialchars($row['tanggal_selesai']); ?></td>
                <td><?= htmlspecialchars($row['jenis_cuti']); ?></td>
                <td><?= htmlspecialchars($row['status']); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
