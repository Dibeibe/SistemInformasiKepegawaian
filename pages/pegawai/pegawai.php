<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';


require_login();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pegawai') {
  redirect(app_url('pages/dashboard_admin.php'));
}

$username = $_SESSION['username'] ?? 'Pengguna';

// Ambil daftar pegawai + join jabatan & unit
$sql = "SELECT p.id, p.nip, p.nama, p.email, p.telp, p.tanggal_masuk,
               j.nama_jabatan, u.nama_unit
        FROM pegawai p
        LEFT JOIN jabatan j ON j.id = p.jabatan_id
        LEFT JOIN unit u ON u.id = p.unit_id
        ORDER BY p.id DESC";

$result = mysqli_query($conn, $sql);

$rows = [];
if ($result) {
    while ($r = mysqli_fetch_assoc($result)) {
        $rows[] = $r;
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Daftar Pegawai</title>

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

/* CARD */
.card{
  background: var(--glass);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  backdrop-filter: blur(12px);
  box-shadow: var(--shadow-soft);
  padding: 18px;
}

/* TABLE */
.table-wrap{overflow:auto;}
table{
  width:100%;
  border-collapse: collapse;
  min-width: 920px;
}
th,td{
  padding: 12px 12px;
  border-bottom: 1px solid rgba(255,255,255,0.18);
  text-align:left;
  vertical-align: top;
}
th{
  font-size: 12px;
  color: rgba(255,255,255,0.9);
  letter-spacing: .04em;
  text-transform: uppercase;
  font-weight: 900;
}
td{
  font-size: 13px;
  color: rgba(255,255,255,0.92);
}
.muted{color: rgba(255,255,255,0.75); font-size: 12px;}
.pill{
  display:inline-block;
  padding: 4px 10px;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,0.22);
  background: rgba(255,255,255,0.12);
  font-weight: 800;
  font-size: 12px;
}
.btn-mini{
  background: rgba(255,255,255,0.22);
  color:#fff;
  border: 1px solid rgba(255,255,255,0.22);
  padding: 7px 10px;
  border-radius: 999px;
  text-decoration:none;
  font-weight: 900;
  transition: .2s ease;
  display:inline-block;
}
.btn-mini:hover{background: rgba(255,255,255,0.28); transform: translateY(-1px);}

.topbar{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap: 12px;
  margin-bottom: 12px;
}

@media(max-width: 640px){
  .title{font-size: 22px;}
  .user-chip{display:none;}
  .topbar{flex-direction:column; align-items:flex-start;}
}
</style>
</head>

<body>
  <div class="navbar">
    <div class="nav-inner">
      <div class="brand">
        Sistem Kepegawaian <span class="badge">Pegawai</span>
      </div>

      <div class="nav-right">
        <a class="btn-back" href="<?= app_url('pages/dashboard_pegawai.php') ?>">← Dashboard</a>
        <span class="user-chip">👤 <?= htmlspecialchars($username) ?></span>
        <a class="btn-logout" href="/SISTEM_KEPEGAWAIAN/logout.php">Logout</a>
      </div>
    </div>
  </div>

  <div class="page">
    <h2 class="title">Daftar Pegawai</h2>
    <p class="sub">Halaman ini bersifat <b>read-only</b> untuk role pegawai. Klik “Lihat Detail” untuk melihat data lengkap.</p>

    <div class="card">
      <div class="topbar">
        <div>
          <div style="font-weight:900;">Total Data: <?= number_format(count($rows)) ?></div>
          <div class="muted">Data diambil dari tabel pegawai, join jabatan & unit.</div>
        </div>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>NIP</th>
              <th>Nama</th>
              <th>Jabatan</th>
              <th>Unit</th>
              <th>Email</th>
              <th>Telp</th>
              <th>Tgl Masuk</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if(!$rows): ?>
              <tr><td colspan="9" class="muted">Belum ada data pegawai.</td></tr>
            <?php else: ?>
              <?php $no=1; foreach($rows as $r): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><span class="pill"><?= htmlspecialchars($r['nip']) ?></span></td>
                  <td><?= htmlspecialchars($r['nama']) ?></td>
                  <td><?= htmlspecialchars($r['nama_jabatan'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($r['nama_unit'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($r['email'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($r['telp'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($r['tanggal_masuk'] ?? '-') ?></td>
                  <td>
                    <a class="btn-mini" href="<?= app_url('pages/pegawai/detail_pegawai.php?id='.(int)$r['id']) ?>">
                      🔎 Lihat Detail
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</body>
</html>
