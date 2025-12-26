<?php
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/functions.php";

require_login();
// Halaman ini hanya untuk pegawai (read-only)
if (($_SESSION['role'] ?? '') !== 'pegawai') {
  redirect(app_url('pages/dashboard_admin.php'));
}

$page_title = "Data Jabatan — Pegawai";
$username   = htmlspecialchars($_SESSION['username'] ?? 'pegawai', ENT_QUOTES, 'UTF-8');

/* ---------- DATA (read-only) ---------- */
$rows = [];
$query_error = null;

$sql = "SELECT id, nama_jabatan FROM jabatan ORDER BY nama_jabatan ASC";

/* Gunakan koneksi yang tersedia (mysqli / PDO) */
if (isset($conn) && $conn instanceof mysqli) {
  $res = $conn->query($sql);
  if ($res === false) {
    $query_error = $conn->error;
  } else {
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $res->free();
  }
} elseif (isset($pdo) && $pdo instanceof PDO) {
  try {
    $st = $pdo->query($sql);
    $rows = $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
  } catch (Throwable $e) {
    $query_error = $e->getMessage();
  }
} else {
  $query_error = "Tidak menemukan koneksi database (\$conn atau \$pdo).";
}

include __DIR__ . "/../../includes/header.php";
?>

<style>
/* ============================= */
/* === PEGAWAI - DATA JABATAN (LOGIN DNA) === */
/* ============================= */
:root{
  --grad-a:#667eea;
  --grad-b:#764ba2;

  --glass: rgba(255,255,255,0.15);
  --stroke: rgba(255,255,255,0.22);

  --text:#ffffff;
  --text-soft: rgba(255,255,255,0.88);

  --btn:#ffd369;
  --btn-hover:#ffbf00;

  --shadow-soft: 0 12px 26px rgba(0,0,0,0.18);
  --radius: 16px;

  /* ===== WARNA ITEM (HITAM) ===== */
  --item:#111827;
  --item-strong:#000000;
}

/* Hilangkan sidebar bila ada */
#sidebar,.sidebar,.left-sidebar,.app-sidebar{display:none!important;}

/* Background login */
body{
  padding-left:0!important;
  background: linear-gradient(135deg, var(--grad-a), var(--grad-b)) !important;
  color: var(--text);
}

/* wrapper */
.page{
  max-width: 980px;
  margin: 90px auto 48px;
  padding: 0 18px;
  animation: fadeUp .6s ease both;
}
@keyframes fadeUp{
  from{opacity:0; transform: translateY(12px);}
  to{opacity:1; transform: translateY(0);}
}

.page h2{
  font-weight: 900;
  font-size: 24px;
  margin: 0 0 6px;
  letter-spacing: .02em;
}
.page p.sub{
  margin: 0 0 14px;
  color: var(--text-soft);
  font-size: 13px;
  line-height: 1.5;
}

/* action bar */
.actionbar{
  background: var(--glass);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  backdrop-filter: blur(12px);
  box-shadow: var(--shadow-soft);
  padding: 14px;

  display:flex;
  gap:10px;
  align-items:center;
  flex-wrap: wrap;

  position: sticky;
  top: 72px;
  z-index: 10;
}

.pill{
  display:inline-flex;
  align-items:center;
  gap:8px;

  border: 1px solid rgba(255,255,255,0.35);
  background: transparent;
  padding: 10px 14px;
  border-radius: 999px;

  text-decoration: none;
  color: var(--text);
  font-size: 14px;
  font-weight: 800;
  transition:.25s ease;
}
.pill:hover{
  background: rgba(255,255,255,0.16);
  transform: translateY(-2px);
}

.input{
  flex: 1;
  min-width: 220px;
  border: 1px solid rgba(255,255,255,0.35);
  background: rgba(255,255,255,0.14);
  padding: 10px 12px;
  border-radius: 12px;
  outline: none;
  color: var(--text);
  transition: .2s ease;
}
.input::placeholder{ color: rgba(255,255,255,0.70); }
.input:focus{
  box-shadow: 0 0 0 3px rgba(255,211,105,0.35);
  border-color: rgba(255,255,255,0.55);
}

/* alert error */
.alert{
  margin-top: 12px;
  padding: 14px 16px;
  border-radius: var(--radius);
  border: 1px solid rgba(239,68,68,0.45);
  background: rgba(239,68,68,0.12);
  color: #fff;
  backdrop-filter: blur(12px);
  box-shadow: var(--shadow-soft);
}

/* table card */
.card{
  background: var(--glass);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  backdrop-filter: blur(12px);
  box-shadow: var(--shadow-soft);
  margin-top: 14px;
  overflow: hidden;
  padding: 0;
}

.table-wrap{ overflow:auto; }
.table{
  width: 100%;
  border-collapse: collapse;
  min-width: 520px;
}

.table thead th{
  background: rgba(255,255,255,0.10);
  text-align: left;
  padding: 12px;
  font-size: 14px;
  color: var(--text);
  border-bottom: 1px solid rgba(255,255,255,0.16);
  font-weight: 900;
}

/* ===== INI KUNCI: ISI TABEL (jabatan) JADI ITEM/HITAM ===== */
.table tbody td{
  padding: 12px;
  font-size: 14px;
  border-bottom: 1px solid rgba(255,255,255,0.12);
  color: var(--item) !important;
  font-weight: 600;
}
.table tbody td.nama-jabatan{
  color: var(--item-strong) !important;
  font-weight: 700;
}

.table tbody tr:nth-child(odd){
  background: rgba(255,255,255,0.06);
}
.table tbody tr:hover{
  background: rgba(0,0,0,0.05);
}

.th-sort{ cursor:pointer; user-select:none; }
.th-sort .caret{ margin-left:6px; opacity:.85; }

/* empty */
.empty{
  padding: 28px;
  text-align:center;
  color: var(--text-soft);
}
.empty .title{
  font-weight: 900;
  color: var(--text);
  margin-bottom: 6px;
}

/* responsive */
@media (max-width: 680px){
  .page{ margin-top: 82px; }
}
</style>

<div class="page">
  <h2>Data Jabatan</h2>
  <p class="sub">Daftar jabatan di organisasi (read-only).</p>

  <div class="actionbar">
    <a class="pill" href="<?= app_url('pages/dashboard_pegawai.php') ?>">← Kembali</a>
    <input id="qSearch" class="input" type="search" placeholder="Cari nama jabatan… (klik Nama Jabatan untuk sort)">
  </div>

  <?php if (!empty($query_error)): ?>
    <div class="alert">Gagal mengambil data: <?= htmlspecialchars($query_error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="table-wrap">
      <table class="table" id="tblJabatan">
        <thead>
          <tr>
            <th style="width:70px">No</th>
            <th id="thNama" class="th-sort">Nama Jabatan <span class="caret">▾</span></th>
          </tr>
        </thead>
        <tbody id="tbBody">
          <?php if (count($rows)): $no=1; foreach ($rows as $r): ?>
            <tr class="row-data">
              <td><?= $no++; ?></td>
              <td class="nama-jabatan"><?= htmlspecialchars($r['nama_jabatan'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; else: ?>
            <tr>
              <td colspan="2">
                <div class="empty">
                  <div class="title">Belum ada data jabatan</div>
                  <div>Silakan hubungi admin bila data belum tersedia.</div>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . "/../../includes/footer.php"; ?>

<script>
const $  = (q,c=document)=>c.querySelector(q);
const $$ = (q,c=document)=>Array.from(c.querySelectorAll(q));

const tbody = $('#tbBody');

const rows = $$('#tbBody tr.row-data').map(tr => ({
  tr,
  name: (tr.querySelector('.nama-jabatan')?.textContent || '').toLowerCase()
}));

let state = { q:'', asc:true };

function render(list){
  tbody.innerHTML = '';
  if(!list.length){
    tbody.innerHTML = `
      <tr>
        <td colspan="2">
          <div class="empty">
            <div class="title">Data tidak ditemukan</div>
            <div>Ubah kata kunci pencarian.</div>
          </div>
        </td>
      </tr>`;
    return;
  }
  list.forEach((r,i)=>{
    r.tr.children[0].textContent = i+1;
    tbody.appendChild(r.tr);
  });
}

$('#qSearch')?.addEventListener('input', e => {
  state.q = e.target.value.trim().toLowerCase();
  const filtered = rows.filter(r => !state.q || r.name.includes(state.q));
  const sorted = filtered.sort((a,b)=> state.asc ? a.name.localeCompare(b.name) : b.name.localeCompare(a.name));
  render(sorted);
});

$('#thNama')?.addEventListener('click', () => {
  state.asc = !state.asc;
  $('#thNama .caret').textContent = state.asc ? '▾' : '▴';
  const filtered = rows.filter(r => !state.q || r.name.includes(state.q));
  const sorted = filtered.sort((a,b)=> state.asc ? a.name.localeCompare(b.name) : b.name.localeCompare(a.name));
  render(sorted);
});

window.addEventListener('keydown', e => {
  if (e.key === '/' && document.activeElement.tagName !== 'INPUT') {
    e.preventDefault();
    $('#qSearch')?.focus();
  }
});

// initial
render(rows);
</script>
