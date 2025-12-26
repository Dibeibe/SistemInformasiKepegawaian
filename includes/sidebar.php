<!-- ===== Sidebar Drawer (kanan) ===== -->
<style>
  :root{
    --hunter:#355E3B;        /* hunter green */
    --hunter-600:#2A462F;
    --bangla:#006A4E;        /* Bangladesh green */
    --bangla-700:#03523F;
    --card:#ffffff;
    --text:#1F2A1E;
    --sub:#6B7280;
    --muted:#E5EFE8;
    --ring:rgba(0,106,78,.28);
    --shadow:0 18px 40px rgba(0,0,0,.22);
  }

  /* Drawer */
  .sidebar-drawer{
    position: fixed; inset: 0 0 0 auto;         /* anchor kanan */
    width: 320px; max-width: 88vw; height: 100vh;
    background: linear-gradient(180deg, var(--hunter-600), var(--bangla));
    color: #fff; border-left: 1px solid rgba(255,255,255,.12);
    transform: translateX(100%); opacity: .98;
    transition: transform .32s cubic-bezier(.22,.61,.36,1), box-shadow .25s;
    z-index: 1040; display:flex; flex-direction:column;
    box-shadow: none;
  }
  .sidebar-drawer.open{ transform: translateX(0); box-shadow: var(--shadow); }

  .sb-head{
    display:flex; align-items:center; justify-content:space-between;
    padding:16px 18px; border-bottom:1px solid rgba(255,255,255,.12);
  }
  .sb-title{ margin:0; font-size:18px; font-weight:700; letter-spacing:.3px }
  .sb-close{
    background:transparent; border:1px solid rgba(255,255,255,.18);
    color:#fff; width:36px; height:36px; border-radius:10px; display:grid; place-items:center;
    transition:.18s;
  }
  .sb-close:hover{ background:rgba(255,255,255,.08) }

  .sb-body{ padding:12px 10px 18px; overflow:auto; }
  .sb-nav{ list-style:none; margin:0; padding:0; display:grid; gap:6px }
  .sb-link{
    display:flex; align-items:center; gap:10px;
    padding:10px 12px; border-radius:10px; color:#E8F5EE; text-decoration:none;
    border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.03);
    transition: transform .08s ease, background .18s ease, border-color .18s ease;
  }
  .sb-link:hover{ background:rgba(255,255,255,.08); border-color:rgba(255,255,255,.18); transform: translateX(-2px); }
  .sb-link--danger{ color:#FFD1D1; border-color:rgba(255,99,99,.2); }
  .sb-section{ margin:10px 6px 6px; color:#CFEADF; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; }

  /* Overlay */
  .sb-overlay{
    position: fixed; inset:0; background:rgba(0,0,0,.35); backdrop-filter: blur(1px);
    opacity:0; visibility:hidden; transition:opacity .2s; z-index:1035;
  }
  .sb-overlay.open{ opacity:1; visibility:visible; }

  /* Floating toggle (kalau navbar tidak punya tombol) */
  .sb-toggle{
    position: fixed; right:16px; bottom:18px; z-index:1036;
    background: linear-gradient(180deg, var(--bangla), var(--hunter));
    color:#fff; border:none; border-radius:14px; width:52px; height:52px;
    display:grid; place-items:center; box-shadow:0 10px 24px rgba(0,106,78,.35);
    transition: transform .08s ease, box-shadow .2s ease, background .2s ease;
  }
  .sb-toggle:hover{ transform: translateY(-1px); box-shadow:0 14px 30px rgba(0,106,78,.45) }
  .sb-toggle svg{ width:22px; height:22px }
  @media (min-width: 1024px){
    /* optional: munculkan tombol juga di desktop */
  }
</style>

<!-- Overlay -->
<div id="sbOverlay" class="sb-overlay" aria-hidden="true"></div>

<!-- Drawer -->
<aside id="sidebar" class="sidebar-drawer" aria-hidden="true" aria-label="Menu samping">
  <div class="sb-head">
    <h5 class="sb-title">Menu</h5>
    <button class="sb-close" id="sbClose" aria-label="Tutup menu">
      <!-- X icon -->
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>

  <div class="sb-body">
    <div class="sb-section">Navigasi</div>
    <ul class="sb-nav">
      <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <li><a class="sb-link" href="<?= app_url('pages/dashboard_admin.php') ?>">Dashboard</a></li>
        <li><a class="sb-link" href="<?= app_url('pages/pegawai/list.php') ?>">Pegawai</a></li>
        <li><a class="sb-link" href="<?= app_url('pages/master/unit.php') ?>">Unit</a></li>
        <li><a class="sb-link" href="<?= app_url('pages/master/jabatan.php') ?>">Jabatan</a></li>
        <li><a class="sb-link" href="<?= app_url('pages/absensi/laporan.php') ?>">Absensi</a></li>
        <li><a class="sb-link" href="<?= app_url('pages/cuti/persetujuan.php') ?>">Cuti</a></li>
        <li><a class="sb-link" href="<?= app_url('pages/gaji/list.php') ?>">Gaji</a></li>
        <li><a class="sb-link" href="<?= app_url('pages/laporan/pegawai.php') ?>">Laporan</a></li>
        <li><a class="sb-link" href="<?= app_url('pages/audit/log.php') ?>">Audit Log</a></li>
      <?php else: ?>
        <li><a class="sb-link" href="<?= app_url('pages/dashboard_pegawai.php') ?>">Dashboard</a></li>
        <li><a class="sb-link" href="<?= app_url('pages/absensi/checkin.php') ?>">Check In</a></li>
        <li><a class="sb-link" href="<?= app_url('pages/absensi/checkout.php') ?>">Check Out</a></li>
        <li><a class="sb-link" href="<?= app_url('pages/cuti/pengajuan.php') ?>">Pengajuan Cuti</a></li>
        <li><a class="sb-link" href="<?= app_url('pages/cuti/riwayat.php') ?>">Riwayat Cuti</a></li>
        <li><a class="sb-link" href="<?= app_url('pages/gaji/detail.php') ?>">Slip Gaji</a></li>
      <?php endif; ?>
      <li><a class="sb-link sb-link--danger" href="<?= app_url('logout.php') ?>">Logout</a></li>
    </ul>
  </div>
</aside>

<!-- Floating Toggle (pakai ini kalau header global belum punya tombol) -->
<button class="sb-toggle" id="sbToggle" aria-label="Buka menu">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
  </svg>
</button>

<!-- ===== Page Content ===== -->
<div id="page-content-wrapper" class="w-100"><!-- konten halaman kamu --></div>

<script>
  // Toggle logic
  const drawer  = document.getElementById('sidebar');
  const overlay = document.getElementById('sbOverlay');
  const btnOpen = document.getElementById('sbToggle');
  const btnClose= document.getElementById('sbClose');

  const open = ()=>{ drawer.classList.add('open'); overlay.classList.add('open'); drawer.setAttribute('aria-hidden','false'); overlay.setAttribute('aria-hidden','false'); };
  const close= ()=>{ drawer.classList.remove('open'); overlay.classList.remove('open'); drawer.setAttribute('aria-hidden','true'); overlay.setAttribute('aria-hidden','true'); };

  btnOpen?.addEventListener('click', open);
  btnClose?.addEventListener('click', close);
  overlay?.addEventListener('click', close);
  window.addEventListener('keydown', e=>{ if(e.key === 'Escape') close(); });

  // Tutup otomatis setelah klik link (biar UX mulus di mobile)
  drawer.querySelectorAll('a.sb-link').forEach(a => a.addEventListener('click', close));
</script>
