<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/functions.php';
require_login();

// Proteksi role
if (($_SESSION['role'] ?? '') !== 'admin') {
    redirect('dashboard_pegawai.php'); // atau redirect('../login.php');
}

// Helper escape
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// Ambil statistik (dibikin aman kalau query gagal)
$pegawai_count = 0;
$unit_count    = 0;
$jabatan_count = 0;
$cuti_count    = 0;

if ($res = $conn->query("SELECT COUNT(*) AS total FROM pegawai")) {
    $pegawai_count = (int)($res->fetch_assoc()['total'] ?? 0);
}
if ($res = $conn->query("SELECT COUNT(*) AS total FROM unit")) {
    $unit_count = (int)($res->fetch_assoc()['total'] ?? 0);
}
if ($res = $conn->query("SELECT COUNT(*) AS total FROM jabatan")) {
    $jabatan_count = (int)($res->fetch_assoc()['total'] ?? 0);
}
if ($res = $conn->query("SELECT COUNT(*) AS total FROM cuti WHERE status='menunggu'")) {
    $cuti_count = (int)($res->fetch_assoc()['total'] ?? 0);
}

$username = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Dashboard Admin | Sistem Kepegawaian</title>

<style>
/* ============================= */
/* === ADMIN DASH (LOGIN VIBE) === */
/* ============================= */

:root{
  --grad-a:#667eea;
  --grad-b:#764ba2;

  --glass: rgba(255,255,255,0.15);
  --glass-2: rgba(255,255,255,0.12);
  --stroke: rgba(255,255,255,0.22);

  --text:#ffffff;
  --text-soft: rgba(255,255,255,0.85);
  --muted: rgba(255,255,255,0.7);

  --btn:#ffd369;
  --btn-hover:#ffbf00;

  --shadow: 0 15px 30px rgba(0,0,0,0.30);
  --shadow-soft: 0 12px 26px rgba(0,0,0,0.18);

  --radius:16px;
  --radius-sm:12px;
}

*{ box-sizing:border-box; }
html,body{ margin:0; padding:0; }

body.admin-body{
  font-family:'Segoe UI', Arial, sans-serif;
  min-height:100vh;
  color:var(--text);
  background: linear-gradient(135deg, var(--grad-a), var(--grad-b));
  display:flex;
}

/* ===== LAYOUT ===== */
.admin-wrap{
  width:100%;
  display:grid;
  grid-template-columns: 260px 1fr;
  gap: 18px;
  padding: 18px;
}

/* ===== SIDEBAR ===== */
.admin-sidebar{
  background: var(--glass);
  border: 1px solid var(--stroke);
  backdrop-filter: blur(12px);
  border-radius: var(--radius);
  box-shadow: var(--shadow-soft);
  padding: 18px;
  animation: fadeUp .6s ease;
}

.brand{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 10px;
  padding-bottom: 14px;
  border-bottom: 1px solid rgba(255,255,255,0.18);
  margin-bottom: 14px;
}

.brand-title{
  font-weight: 800;
  font-size: 16px;
  line-height: 1.2;
}
.brand-sub{
  font-size: 12px;
  color: var(--muted);
}

.badge{
  font-size: 12px;
  background: rgba(255,255,255,0.18);
  border: 1px solid rgba(255,255,255,0.20);
  padding: 6px 10px;
  border-radius: 999px;
  white-space: nowrap;
}

.sidebar-section-title{
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--muted);
  margin: 14px 0 10px;
}

.menu a{
  display:flex;
  align-items:center;
  gap: 10px;
  padding: 12px 12px;
  border-radius: 12px;
  text-decoration:none;
  color: var(--text);
  border: 1px solid transparent;
  transition: .25s ease;
}

.menu a:hover{
  background: rgba(255,255,255,0.12);
  border-color: rgba(255,255,255,0.18);
  transform: translateX(4px);
}

.menu a.active{
  background: rgba(255,255,255,0.18);
  border-color: rgba(255,255,255,0.22);
  font-weight: 700;
}

.menu .ico{
  width: 28px;
  height: 28px;
  border-radius: 10px;
  background: rgba(255,255,255,0.14);
  display:flex;
  align-items:center;
  justify-content:center;
  border: 1px solid rgba(255,255,255,0.16);
}

/* ===== MAIN ===== */
.admin-main{
  display:flex;
  flex-direction:column;
  gap: 18px;
  animation: fadeUp .7s ease;
}

/* ===== TOP BAR (IN MAIN) ===== */
.topbar{
  background: var(--glass);
  border: 1px solid var(--stroke);
  backdrop-filter: blur(12px);
  border-radius: var(--radius);
  box-shadow: var(--shadow-soft);
  padding: 14px 16px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 12px;
}

.topbar-left h1{
  margin: 0;
  font-size: 18px;
  font-weight: 800;
}
.topbar-left p{
  margin: 4px 0 0;
  font-size: 13px;
  color: var(--text-soft);
}

.topbar-right{
  display:flex;
  align-items:center;
  gap: 10px;
}

.pill{
  background: rgba(255,255,255,0.18);
  border: 1px solid rgba(255,255,255,0.20);
  padding: 10px 12px;
  border-radius: 999px;
  font-size: 13px;
}

.btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap: 8px;
  padding: 10px 14px;
  border-radius: 999px;
  border: none;
  cursor: pointer;
  text-decoration:none;
  font-size: 14px;
  transition:.25s ease;
}

.btn-primary{
  background: var(--btn);
  color: #333;
  font-weight: 700;
}
.btn-primary:hover{
  background: var(--btn-hover);
  transform: translateY(-2px);
}

.btn-ghost{
  background: transparent;
  border: 1px solid rgba(255,255,255,0.35);
  color: var(--text);
}
.btn-ghost:hover{
  background: rgba(255,255,255,0.16);
  transform: translateY(-2px);
}

/* ===== CARDS ===== */
.grid-stats{
  display:grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 14px;
}

.card{
  background: var(--glass);
  border: 1px solid var(--stroke);
  backdrop-filter: blur(12px);
  border-radius: var(--radius);
  box-shadow: var(--shadow-soft);
  padding: 16px;
}

.stat{
  display:flex;
  align-items:center;
  gap: 12px;
  transition: .25s ease;
}
.stat:hover{
  transform: translateY(-3px);
}

.stat-ico{
  width: 46px;
  height: 46px;
  border-radius: 14px;
  background: rgba(255,255,255,0.14);
  border: 1px solid rgba(255,255,255,0.16);
  display:flex;
  align-items:center;
  justify-content:center;
  font-size: 20px;
}

.stat-meta{
  display:flex;
  flex-direction:column;
  gap: 2px;
}
.stat-title{
  font-weight: 700;
  font-size: 14px;
}
.stat-sub{
  font-size: 12px;
  color: var(--muted);
}

.stat-value{
  margin-left:auto;
  font-size: 30px;
  font-weight: 900;
  letter-spacing: .02em;
}

.bottom{
  display:grid;
  grid-template-columns: 2fr 1fr;
  gap: 14px;
}

.card h3{
  margin: 0 0 12px;
  font-size: 16px;
  font-weight: 800;
}

.actions{
  display:grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
}

.action{
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.18);
  border-radius: 14px;
  padding: 14px;
  text-decoration:none;
  color: var(--text);
  display:flex;
  align-items:center;
  justify-content:space-between;
  transition: .25s ease;
  font-weight: 700;
}
.action:hover{
  background: rgba(255,255,255,0.18);
  border-color: rgba(255,255,255,0.24);
  transform: translateY(-3px);
  box-shadow: var(--shadow-soft);
}
.action small{
  font-weight: 600;
  color: var(--muted);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 980px){
  body.admin-body{ display:block; }
  .admin-wrap{ grid-template-columns: 1fr; }
  .admin-sidebar{ position: relative; }
  .bottom{ grid-template-columns: 1fr; }
  .topbar{ flex-direction: column; align-items:flex-start; }
  .topbar-right{ width:100%; justify-content:flex-start; flex-wrap:wrap; }
}

/* ===== ANIMATION ===== */
@keyframes fadeUp{
  from{ opacity:0; transform: translateY(14px); }
  to{ opacity:1; transform: translateY(0); }
}
</style>
</head>

<body class="admin-body">

<div class="admin-wrap">

  <!-- SIDEBAR -->
  <aside class="admin-sidebar">
    <div class="brand">
      <div>
        <div class="brand-title">Sistem Kepegawaian</div>
        <div class="brand-sub">Panel Admin</div>
      </div>
      <span class="badge">Admin</span>
    </div>

    <div class="sidebar-section-title">Menu</div>
    <nav class="menu">
      <a class="active" href="pegawai/list.php">
        <span class="ico">👤</span>
        Manajemen Pegawai
      </a>
      <a href="master/unit.php">
        <span class="ico">🏢</span>
        Unit
      </a>
      <a href="../pages/master/jabatan.php">
        <span class="ico">📌</span>
        Jabatan
      </a>
      <a href="../logout.php">
        <span class="ico">🚪</span>
        Logout
      </a>
    </nav>

    <div class="sidebar-section-title">Akun</div>
    <div class="pill" style="display:inline-block; margin-top:6px;">
      <?= e($username) ?>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="admin-main">

    <section class="topbar">
      <div class="topbar-left">
        <h1>Dashboard Admin</h1>
        <p>Kelola data pegawai & master data dengan tampilan yang konsisten.</p>
      </div>
      <div class="topbar-right">
        <span class="pill">👋 Hai, <?= e($username) ?></span>
        <a class="btn btn-ghost" href="pegawai/list.php">Lihat Pegawai</a>
        <a class="btn btn-primary" href="pegawai/add.php">+ Tambah Pegawai</a>
      </div>
    </section>

    <section class="grid-stats">
      <div class="card stat">
        <div class="stat-ico">👤</div>
        <div class="stat-meta">
          <div class="stat-title">Total Pegawai</div>
          <div class="stat-sub">Data pegawai terdaftar</div>
        </div>
        <div class="stat-value"><?= $pegawai_count ?></div>
      </div>

      <div class="card stat">
        <div class="stat-ico">🏢</div>
        <div class="stat-meta">
          <div class="stat-title">Total Unit</div>
          <div class="stat-sub">Divisi / bagian</div>
        </div>
        <div class="stat-value"><?= $unit_count ?></div>
      </div>

      <div class="card stat">
        <div class="stat-ico">📌</div>
        <div class="stat-meta">
          <div class="stat-title">Total Jabatan</div>
          <div class="stat-sub">Posisi kerja</div>
        </div>
        <div class="stat-value"><?= $jabatan_count ?></div>
      </div>

      <div class="card stat">
        <div class="stat-ico">📝</div>
        <div class="stat-meta">
          <div class="stat-title">Cuti Menunggu</div>
          <div class="stat-sub">Perlu ditinjau admin</div>
        </div>
        <div class="stat-value"><?= $cuti_count ?></div>
      </div>
    </section>

    <section class="bottom">
      <div class="card">
        <h3>Aksi Cepat</h3>
        <div class="actions">
          <a class="action" href="pegawai/add.php">
            <span>Tambah Pegawai</span>
            <small>Input data baru</small>
          </a>
          <a class="action" href="pegawai/list.php">
            <span>Kelola Pegawai</span>
            <small>Edit / hapus</small>
          </a>
          <a class="action" href="master/unit.php">
            <span>Master Unit</span>
            <small>Tambah / ubah unit</small>
          </a>
          <a class="action" href="master/jabatan.php">
            <span>Master Jabatan</span>
            <small>Tambah / edit jabatan</small>
          </a>
        </div>
      </div>

      <div class="card">
        <h3>Pengumuman</h3>
        <p style="color: var(--text-soft); line-height: 1.7; margin: 0;">
          Mulai bulan depan, sistem absensi menggunakan fingerprint.
          Pastikan seluruh pegawai telah registrasi ke HRD.
        </p>
        <div style="height: 12px;"></div>
        <a class="btn btn-primary" href="absensi/list.php" style="width:100%;">Cek Data Absensi</a>
      </div>
    </section>

  </main>

</div>
</body>
</html>
