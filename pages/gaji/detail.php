<?php
require_once __DIR__.'/../../config/config.php';
require_once __DIR__.'/../../config/functions.php';

require_login();
if (($_SESSION['role'] ?? '') !== 'pegawai') { redirect('../dashboard_admin.php'); }

$pid = current_pegawai_id();
if (!$pid) { die('Akun belum terhubung ke data pegawai.'); }

$sql = "SELECT g.*, p.nip, p.nama 
        FROM gaji g 
        LEFT JOIN pegawai p ON p.id = g.pegawai_id
        WHERE g.pegawai_id = $pid
        ORDER BY g.id DESC LIMIT 1";
$g = $conn->query($sql)->fetch_assoc();

include __DIR__.'/../../includes/header.php';
include __DIR__.'/../../includes/sidebar.php';
include __DIR__.'/../../includes/navbar.php';

function rp($v){ return 'Rp '.number_format((float)$v,0,',','.'); }
?>
<div class="container">
  <h3>Slip Gaji Terbaru</h3>
  <?php if(!$g): ?>
    <div class="alert alert-warning">Belum ada data gaji.</div>
  <?php else: ?>
    <table class="table">
      <tr><th>NIP</th><td><?= htmlspecialchars($g['nip']) ?></td></tr>
      <tr><th>Nama</th><td><?= htmlspecialchars($g['nama']) ?></td></tr>
      <tr><th>Periode</th><td><?= htmlspecialchars($g['bulan'].'/'.$g['tahun']) ?></td></tr>
      <tr><th>Gaji Pokok</th><td><?= rp($g['gaji_pokok']) ?></td></tr>
      <tr><th>Tunjangan</th><td><?= rp($g['tunjangan']) ?></td></tr>
      <tr><th>Potongan</th><td><?= rp($g['potongan']) ?></td></tr>
      <tr><th>Total</th><td><b><?= rp($g['total']) ?></b></td></tr>
    </table>
  <?php endif; ?>
</div>
<?php include __DIR__.'/../../includes/footer.php'; ?>
