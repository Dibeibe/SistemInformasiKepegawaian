<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/functions.php';

require_login();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pegawai') {
    redirect(app_url('pages/dashboard_admin.php'));
}

$username = $_SESSION['username'] ?? 'Pengguna';

/* ===== KPI (read-only) ===== */
function kpi_count($table) {
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        try {
            $stmt = $GLOBALS['pdo']->query("SELECT COUNT(*) FROM `$table`");
            return (int)$stmt->fetchColumn();
        } catch (Throwable $e) { return 0; }
    }
    if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
        try {
            $res = $GLOBALS['conn']->query("SELECT COUNT(*) AS jml FROM `$table`");
            if ($res && ($row = $res->fetch_assoc())) return (int)$row['jml'];
        } catch (Throwable $e) { return 0; }
        return 0;
    }
    return 0;
}

$totalPegawai = kpi_count('pegawai');
$totalUnit    = kpi_count('unit');
$totalJabatan = kpi_count('jabatan');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Dashboard Pegawai</title>

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

/* ===== NAVBAR (GLASS) ===== */
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

.btn-logout{
  background: var(--btn);
  color:#333;
  border:none;
  border-radius: 999px;
  padding: 8px 14px;
  font-weight: 900;
  text-decoration: none;
  transition: .25s ease;
}
.btn-logout:hover{
  background: var(--btn-hover);
  transform: translateY(-2px);
}

/* ===== PAGE ===== */
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

/* ===== GRID KPI ===== */
.grid{
  display:grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 14px;
  margin-top: 14px;
}

.card-link{
  text-decoration:none;
  color:inherit;
  display:block;
}

.card{
  background: var(--glass);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  backdrop-filter: blur(12px);
  box-shadow: var(--shadow-soft);
  padding: 18px;
  transition: .25s ease;
}
.card:hover{
  transform: translateY(-4px);
  background: rgba(255,255,255,0.18);
}

.kpi-wrap{display:flex; align-items:center; gap: 12px;}
.kpi-icon{
  width: 52px; height: 52px;
  border-radius: 14px;
  display:flex; align-items:center; justify-content:center;
  background: rgba(255,255,255,0.18);
  border: 1px solid rgba(255,255,255,0.22);
  font-size: 22px;
}
.kpi-title{color: var(--muted); font-size: 12px; margin-bottom: 2px; font-weight: 800;}
.kpi-value{font-size: 30px; font-weight: 900; margin: 0; line-height: 1;}
.kpi-desc{color: var(--text-soft); font-size: 12px; margin-top: 4px;}

/* ===== PANEL ===== */
.panel-title{
  margin:0 0 8px;
  font-weight: 900;
  font-size: 16px;
}
.panel-sub{
  margin:0;
  color: var(--text-soft);
  font-size: 13px;
  line-height: 1.55;
}

/* Responsive */
@media(max-width: 640px){
  .title{font-size: 22px;}
  .user-chip{display:none;}
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
        <span class="user-chip">👤 <?= htmlspecialchars($username) ?></span>
        <a class="btn-logout" href="../logout.php">Logout</a>
      </div>
    </div>
  </div>

  <div class="page">
    <h2 class="title">Halo, <?= htmlspecialchars($username) ?> 👋</h2>
    <p class="sub">Ringkasan data organisasi (read-only).</p>

    <div class="grid">
      <!-- Pegawai -->
      <a class="card-link" href="<?= app_url('pages/pegawai/pegawai.php?ro=1') ?>">
        <div class="card">
          <div class="kpi-wrap">
            <div class="kpi-icon">👥</div>
            <div>
              <div class="kpi-title">Total Pegawai</div>
              <p class="kpi-value"><?= number_format($totalPegawai) ?></p>
              <div class="kpi-desc">Aktif dalam sistem</div>
            </div>
          </div>
        </div>
      </a>

      <!-- Unit -->
      <a class="card-link" href="<?= app_url('pages/pegawai/unit.php?ro=1') ?>">
        <div class="card">
          <div class="kpi-wrap">
            <div class="kpi-icon">🏢</div>
            <div>
              <div class="kpi-title">Total Unit</div>
              <p class="kpi-value"><?= number_format($totalUnit) ?></p>
              <div class="kpi-desc">Departemen / Divisi</div>
            </div>
          </div>
        </div>
      </a>

      <!-- Jabatan -->
      <a class="card-link" href="<?= app_url('pages/pegawai/jabatan.php?ro=1') ?>">
        <div class="card">
          <div class="kpi-wrap">
            <div class="kpi-icon">🗂️</div>
            <div>
              <div class="kpi-title">Total Jabatan</div>
              <p class="kpi-value"><?= number_format($totalJabatan) ?></p>
              <div class="kpi-desc">Posisi dalam organisasi</div>
            </div>
          </div>
        </div>
      </a>
    </div>

    <div class="card" style="margin-top:14px;">
      <h3 class="panel-title">Pengumuman</h3>
      <p class="panel-sub">
        Mulai bulan depan, sistem absensi akan terintegrasi dengan fingerprint. Pastikan kamu sudah registrasi di HRD.
        <br><span style="color:rgba(255,255,255,0.78);">Hubungi admin apabila ada kendala akses.</span>
      </p>
    </div>
  </div>
</body>
</html>
