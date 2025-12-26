<?php
require_once __DIR__.'/../../config/config.php';
require_once __DIR__.'/../../config/functions.php';

require_login();
if (($_SESSION['role'] ?? '') !== 'pegawai') { redirect('../dashboard_admin.php'); }

$pid = current_pegawai_id();
if (!$pid) { die('Akun belum terhubung ke data pegawai.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal    = date('Y-m-d');
    $jam_keluar = date('H:i:s');

    $stmt = $conn->prepare("UPDATE absensi SET jam_keluar=? WHERE pegawai_id=? AND tanggal=? AND jam_keluar IS NULL");
    $stmt->bind_param("sis", $jam_keluar, $pid, $tanggal);
    $stmt->execute();

    $msg = $stmt->affected_rows > 0 ? "Check-out berhasil jam $jam_keluar" : "Belum check-in atau sudah check-out.";
    redirect("checkout.php?msg=".urlencode($msg));
    exit;
}

include __DIR__.'/../../includes/header.php';
include __DIR__.'/../../includes/sidebar.php';
include __DIR__.'/../../includes/navbar.php';
?>
<div class="container">
  <h3>Check-Out</h3>
  <?php if(isset($_GET['msg'])): ?><div class="alert alert-info"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
  <form method="post"><button class="btn btn-warning">Check-Out Sekarang</button></form>
</div>
<?php include __DIR__.'/../../includes/footer.php'; ?>
