<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';

require_login();
if (($_SESSION['role'] ?? '') !== 'pegawai') {
    redirect('../dashboard_admin.php');
    exit;
}

// helper: cari kolom yang tersedia
function cuti_has_col(mysqli $conn, string $col): bool {
    $col = $conn->real_escape_string($col);
    $res = $conn->query("SHOW COLUMNS FROM cuti LIKE '{$col}'");
    return $res && $res->num_rows > 0;
}

// tentukan kolom filter yang valid
$whereCol = null;
if (cuti_has_col($conn, 'user_id')) {
    $whereCol = 'user_id';
    $idValue  = (int)($_SESSION['user_id'] ?? 0);
} elseif (cuti_has_col($conn, 'pegawai_id')) {
    $whereCol = 'pegawai_id';
    // pakai helper untuk ambil id pegawai dari sesi
    if (!function_exists('current_pegawai_id')) {
        // fallback ringan kalau helper belum tersedia
        $pid = 0;
        if (isset($_SESSION['pegawai_id'])) { $pid = (int)$_SESSION['pegawai_id']; }
    } else {
        $pid = (int) current_pegawai_id();
    }
    $idValue = $pid;
}

if (!$whereCol || !$idValue) {
    die('Tidak bisa menampilkan riwayat: akun belum terhubung ke data pegawai atau kolom referensi tidak ditemukan.');
}

// ambil data riwayat
$stmt = $conn->prepare("SELECT id, tanggal_mulai, tanggal_selesai, alasan, status 
                        FROM cuti 
                        WHERE {$whereCol} = ?
                        ORDER BY id DESC");
$stmt->bind_param("i", $idValue);
$stmt->execute();
$result = $stmt->get_result();

$page_title = "Riwayat Cuti";
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<div class="container">
    <h3><?= htmlspecialchars($page_title) ?></h3>
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-info"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Selesai</th>
                <th>Alasan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows): ?>
            <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['tanggal_mulai']) ?></td>
                    <td><?= htmlspecialchars($row['tanggal_selesai']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['alasan'])) ?></td>
                    <td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" class="text-center text-muted">Belum ada pengajuan cuti.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
