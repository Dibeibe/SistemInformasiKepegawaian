<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';

// Wajib login & admin
require_login();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    redirect('../../index.php');
}

$page_title = "Laporan Absensi";

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';

// Data absensi + pegawai
$sql = "SELECT a.id, p.nip, p.nama, a.tanggal, a.jam_masuk, a.jam_keluar, a.status
        FROM absensi a
        LEFT JOIN pegawai p ON a.pegawai_id = p.id
        ORDER BY a.tanggal DESC, a.jam_masuk DESC";
$result = $conn->query($sql);
$query_error = $result === false ? $conn->error : null;
?>
<div class="container">
    <h3><?= htmlspecialchars($page_title) ?></h3>

    <!-- Karena file ini ada di /pages/absensi/, link export balik satu folder ke /pages/laporan/ -->
    <a href="../laporan/export.php?type=absensi" class="btn btn-success btn-sm mb-2">Export Excel</a>

    <?php if ($query_error): ?>
      <div class="alert alert-danger mb-2">
        <strong>Query Error:</strong> <?= htmlspecialchars($query_error) ?>
      </div>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
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
        <?php if (!$query_error && $result && $result->num_rows): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nip'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['jam_masuk'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['jam_keluar'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['status'] ?? '-') ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" class="text-center text-muted">
                <?= $query_error ? "Gagal mengambil data." : "Tidak ada data." ?>
            </td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
