<?php
require_once __DIR__.'/../../config/config.php';
require_once __DIR__.'/../../config/functions.php';

require_login();
if (($_SESSION['role'] ?? '') !== 'pegawai') { redirect('../dashboard_admin.php'); }

$pid = current_pegawai_id();
if (!$pid) { die('Akun belum terhubung ke data pegawai.'); }

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=absensi_saya_'.date('Ymd_His').'.csv');

$out = fopen('php://output','w');
fputcsv($out, ['Tanggal','Jam Masuk','Jam Keluar','Status']);

$rs = $conn->query("SELECT tanggal, jam_masuk, jam_keluar, status FROM absensi WHERE pegawai_id=$pid ORDER BY tanggal DESC");
while ($r = $rs->fetch_row()) { fputcsv($out, $r); }
fclose($out); exit;
