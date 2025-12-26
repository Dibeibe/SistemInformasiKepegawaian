<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';
auth_check();

$page_title = "Laporan Gaji";
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';

// Ambil data gaji
$sql = "SELECT g.id, p.nip, p.nama, g.bulan, g.tahun, g.gaji_pokok, g.potongan, g.total
        FROM gaji g
        LEFT JOIN pegawai p ON g.pegawai_id=p.id
        ORDER BY g.tahun DESC, g.bulan DESC";
$result = $conn->query($sql);
?>

<div class="container">
    <h3><?= $page_title; ?></h3>
    <a href="export.php?type=gaji" class="btn btn-success btn-sm mb-2">Export Excel</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>NIP</th>
                <th>Nama</th>
                <th>Bulan</th>
                <th>Tahun</th>
                <th>Gaji Pokok</th>
                <th>Potongan</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['nip']); ?></td>
                <td><?= htmlspecialchars($row['nama']); ?></td>
                <td><?= htmlspecialchars($row['bulan']); ?></td>
                <td><?= htmlspecialchars($row['tahun']); ?></td>
                <td><?= number_format($row['gaji_pokok'], 0, ',', '.'); ?></td>
                <td><?= number_format($row['potongan'], 0, ',', '.'); ?></td>
                <td><?= number_format($row['total'], 0, ',', '.'); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
