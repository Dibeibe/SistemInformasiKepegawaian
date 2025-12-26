<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';

require_login();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pegawai') {
  redirect(app_url('pages/dashboard_admin.php'));
}

$username = $_SESSION['username'] ?? 'Pengguna';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  redirect(app_url('pages/pegawai/pegawai.php'));
}

/* =========================
   AMBIL DETAIL PEGAWAI
========================= */
$sql = "SELECT p.*,
               j.nama_jabatan, j.gaji_pokok,
               u.nama_unit
        FROM pegawai p
        LEFT JOIN jabatan j ON j.id = p.jabatan_id
        LEFT JOIN unit u ON u.id = p.unit_id
        WHERE p.id = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
  die("Prepare gagal: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$data) {
  redirect(app_url('pages/pegawai/pegawai.php'));
}

/* =========================
   FOTO DARI DATABASE
   kolom: pegawai.foto (nama file)
   folder: assets/images/pegawai/
========================= */
$foto = $data['foto'] ?? '';
$fotoPath = '';

$uploadWebDir = app_url('assets/images/pegawai/');         // untuk <img src="">
$uploadFsDir  = __DIR__ . '/../../assets/images/pegawai/'; // untuk file_exists()

if ($foto !== '') {
  // Kalau di DB ternyata ada path lengkap, kita amankan ambil nama filenya doang
  $fotoFile = basename($foto);

  $candidateFs = $uploadFsDir . $fotoFile;
  if (file_exists($candidateFs)) {
    $fotoPath = $uploadWebDir . $fotoFile;
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Detail Pegawai</title>

<style>
:root{
  --grad-a:#667eea;
  --grad-b:#764ba2;

  --glass: rgba(255,255,255,0.15);
  --stroke: rgba(255,255,255,0.22);

  --text:#ffffff;
  --text-soft: rgba(255,255,255,0.88);
  --muted: rgba(255,255,255,0.70);

  --btn:#ffd369;
  --btn-hover:#ffbf00;

  --shadow: 0 15px 30px rgba(0,0,0,0.30);
  --shadow-soft: 0 12px 26px rgba(0,0,0,0.18);

  --radius: 18px;
}
*{box-sizing:border-box;}
html,body{height:100%;}
body{
  margin:0;
  font-family:"Segoe UI",system-ui,sans-serif;
  background: linear-gradient(135deg, var(--grad-a), var(--grad-b));
  color: var(--text);
}

/* NAVBAR */
.navbar{
  position: sticky;
  top: 0;
  z-index: 50;
  padding: 14px 16px;
  background: linear-gradient(135deg, var(--grad-a), var(--grad-b));
  box-shadow: var(--shadow);
}
.nav-inner{
  max-width: 1200px;
  margin: 0 auto;
  padding: 10px 14px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 12px;
  background: var(--glass);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  backdrop-filter: blur(12px);
}
.brand{
  font-weight: 900;
  letter-spacing: .02em;
  display:flex;
  align-items:center;
  gap:10px;
  color:#fff;
}
.badge{
  font-size: 12px;
  padding: 4px 10px;
  border-radius: 999px;
  background: rgba(255,255,255,0.20);
  border: 1px solid rgba(255,255,255,0.22);
  color: var(--text-soft);
  font-weight: 800;
}
.nav-right{
  display:flex;
  align-items:center;
  gap: 10px;
}
.user-chip{
  background: rgba(255,255,255,.22);
  color:#fff;
  padding: 8px 14px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 800;
  white-space: nowrap;
}
.btn-logout, .btn-back{
  background: var(--btn);
  color:#333;
  border:none;
  border-radius: 999px;
  padding: 8px 14px;
  font-weight: 900;
  text-decoration: none;
  transition: .25s ease;
  display:inline-block;
}
.btn-logout:hover, .btn-back:hover{
  background: var(--btn-hover);
  transform: translateY(-2px);
}

/* PAGE */
.page{
  max-width: 1200px;
  margin: 22px auto 40px;
  padding: 0 18px 40px;
  animation: fadeUp .6s ease both;
}
@keyframes fadeUp{
  from{opacity:0; transform:translateY(10px);}
  to{opacity:1; transform:none;}
}
.title{
  font-size: 26px;
  margin: 14px 0 6px;
  font-weight: 900;
}
.sub{
  color: var(--text-soft);
  margin: 0 0 18px;
  font-size: 13px;
}

.card{
  background: var(--glass);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  backdrop-filter: blur(12px);
  box-shadow: var(--shadow-soft);
  padding: 18px;
}

.grid{
  display:grid;
  grid-template-columns: 320px 1fr;
  gap: 14px;
}
@media(max-width: 860px){
  .grid{grid-template-columns: 1fr;}
  .user-chip{display:none;}
}

.photo{
  width:100%;
  border-radius: 16px;
  border: 1px solid rgba(255,255,255,0.22);
  background: rgba(255,255,255,0.10);
  overflow:hidden;
  aspect-ratio: 1 / 1;
  display:flex;
  align-items:center;
  justify-content:center;
}
.photo img{width:100%; height:100%; object-fit:cover;}
.photo .empty{color: rgba(255,255,255,0.75); font-weight:900;}

.row{
  display:flex;
  justify-content:space-between;
  gap: 12px;
  padding: 10px 0;
  border-bottom: 1px solid rgba(255,255,255,0.18);
}
.label{color: rgba(255,255,255,0.78); font-weight:900; font-size: 12px; text-transform: uppercase; letter-spacing:.04em;}
.val{color: rgba(255,255,255,0.96); font-weight:800; text-align:right;}
.pill{
  display:inline-block;
  padding: 4px 10px;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,0.22);
  background: rgba(255,255,255,0.12);
  font-weight: 900;
  font-size: 12px;
}
</style>
</head>

<body>
  <div class="navbar">
    <div class="nav-inner">
      <div class="brand">
        Sistem Kepegawaian <span class="badge">Detail</span>
      </div>

      <div class="nav-right">
          <a class="btn-back" href="/sistem_kepegawaian/pages/pegawai/pegawai.php">← Kembali</a>
        <span class="user-chip">👤 <?= htmlspecialchars($username) ?></span>
        <a class="btn-logout" href="/SISTEM_KEPEGAWAIAN/logout.php">Logout</a>
      </div>
    </div>
  </div>

  <div class="page">
    <h2 class="title">Detail Pegawai</h2>
    <p class="sub">Data lengkap pegawai (read-only).</p>

    <div class="grid">
      <div class="card">
        <div class="photo">
          <?php if ($fotoPath): ?>
            <img src="<?= htmlspecialchars($fotoPath) ?>" alt="Foto Pegawai">
          <?php else: ?>
            <div class="empty">Tidak ada foto</div>
          <?php endif; ?>
        </div>

        <div style="margin-top:12px;">
          <div style="font-weight:900; font-size:18px;"><?= htmlspecialchars($data['nama'] ?? '-') ?></div>
          <div style="margin-top:6px;">
            <span class="pill"><?= htmlspecialchars($data['nip'] ?? '-') ?></span>
          </div>
        </div>
      </div>

      <div class="card">
        <?php
          $items = [
            'Email' => $data['email'] ?? '-',
            'Telepon' => $data['telp'] ?? '-',
            'Alamat' => $data['alamat'] ?? '-',
            'Jabatan' => $data['nama_jabatan'] ?? '-',
            'Unit' => $data['nama_unit'] ?? '-',
            'Tanggal Masuk' => $data['tanggal_masuk'] ?? '-',
          ];
        ?>

        <?php foreach($items as $k => $v): ?>
          <div class="row">
            <div class="label"><?= htmlspecialchars($k) ?></div>
            <div class="val"><?= htmlspecialchars((string)$v) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</body>
</html>
