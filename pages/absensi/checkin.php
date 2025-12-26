<?php
require_once __DIR__.'/../../config/config.php';
require_once __DIR__.'/../../config/functions.php';

require_login(); // pegawai & admin boleh, tapi halaman ini untuk pegawai
if (($_SESSION['role'] ?? '') !== 'pegawai') { redirect('../dashboard_admin.php'); }

$pid = current_pegawai_id();
if (!$pid) { die('Akun belum terhubung ke data pegawai.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal   = date('Y-m-d');
    $jam_masuk = date('H:i:s');

    // cegah double check-in
    $stmt = $conn->prepare("SELECT id FROM absensi WHERE pegawai_id=? AND tanggal=?");
    $stmt->bind_param("is", $pid, $tanggal);
    $stmt->execute(); $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO absensi (pegawai_id, tanggal, jam_masuk, status) VALUES (?,?,?,'hadir')");
        $stmt->bind_param("iss", $pid, $tanggal, $jam_masuk);
        $stmt->execute();
        $msg = "Check-in berhasil jam $jam_masuk";
    } else {
        $msg = "Anda sudah check-in hari ini.";
    }
    redirect("checkin.php?msg=".urlencode($msg));
    exit;
}

include __DIR__.'/../../includes/header.php';
include __DIR__.'/../../includes/sidebar.php';
include __DIR__.'/../../includes/navbar.php';
?>
<div class="container">
  <h3>Check-In</h3>
  <?php if(isset($_GET['msg'])): ?><div class="alert alert-info"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
  <form method="post"><button class="btn btn-primary">Check-In Sekarang</button></form>
</div>
<?php include __DIR__.'/../../includes/footer.php'; ?>
