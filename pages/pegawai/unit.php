<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';

require_login();
// Pegawai saja (admin biar balik ke dashboard admin)
if (($_SESSION['role'] ?? '') !== 'pegawai') {
  redirect(app_url('pages/dashboard_admin.php'));
}

/* --- Ambil data unit (dukung mysqli / PDO) --- */
function fetch_units(): array {
  $sql = "SELECT id, nama_unit FROM unit ORDER BY nama_unit ASC";

  if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
    $res = $GLOBALS['conn']->query($sql);
    if ($res === false) return [[], $GLOBALS['conn']->error];
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    $res->free();
    return [$rows, null];
  }

  if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
    try {
      $st = $GLOBALS['pdo']->query($sql);
      if ($st === false) return [[], 'Query gagal'];
      return [$st->fetchAll(PDO::FETCH_ASSOC), null];
    } catch (Throwable $e) {
      return [[], $e->getMessage()];
    }
  }

  return [[], 'Tidak ada koneksi database ($conn/$pdo).'];
}

list($units, $query_error) = fetch_units();
$page_title = "Data Unit";
?>
<?php include "../../includes/header.php"; ?>

<style>
/* ============================= */
/* === PEGAWAI - DATA UNIT (LOGIN DNA) === */
/* ============================= */
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

  --shadow-soft: 0 12px 26px rgba(0,0,0,0.18);
  --radius: 16px;

  /* ===== WARNA ITEM (HITAM) ===== */
  --item:#111827;        /* hitam */
  --item-strong:#000000; /* hitam pekat */
}

#sidebar,.sidebar,.left-sidebar,.app-sidebar{display:none!important;}
body{
  padding-left:0!important;
  background: linear-gradient(135deg, var(--grad-a), var(--grad-b)) !important;
  color: var(--text);
}

/* wrapper */
.page-wrap{
  max-width: 980px;
  margin: 90px auto 48px;
  padding: 0 18px;
  animation: fadeUp .6s ease both;
}
@keyframes fadeUp{
  from{opacity:0; transform: translateY(12px);}
  to{opacity:1; transform: translateY(0);}
}

.page-title{
  font-weight: 900;
  font-size: 24px;
  margin: 0 0 6px;
  letter-spacing: .02em;
}
.page-sub{
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

/* table card */
.card{
  background: var(--glass);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  backdrop-filter: blur(12px);
  box-shadow: var(--shadow-soft);
  margin-top: 14px;
  overflow: hidden;
}

.table{
  width: 100%;
  border-collapse: collapse;
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

/* ===== INI KUNCI: ISI TABEL JADI HITAM ===== */
.table tbody td{
  padding: 12px;
  font-size: 14px;
  border-bottom: 1px solid rgba(255,255,255,0.12);
  color: var(--item) !important; /* item/hitam */
  font-weight: 600;
}
.table tbody td.nama-unit{
  color: var(--item-strong) !important; /* lebih pekat untuk nama */
  font-weight: 700;
}
.table tbody tr:nth-child(odd){
  background: rgba(255,255,255,0.06);
}
.table tbody tr:hover{
  background: rgba(0,0,0,0.05); /* hover tetap keliatan walau item hitam */
}

.th-sort{ cursor:pointer; user-select:none; }
.th-sort .caret{ margin-left:6px; opacity:.85; }

/* empty + error */
.empty{
  padding: 22px;
  text-align:center;
  color: var(--text-soft);
}
.err{
  padding: 14px 16px;
  text-align:center;
  color: #fff;
  border-top: 1px solid rgba(239,68,68,0.45);
  background: rgba(239,68,68,0.12);
}

/* responsive */
@media (max-width: 680px){
  .page-wrap{ margin-top: 82px; }
}
</style>

<div class="page-wrap">
  <h1 class="page-title">Data Unit</h1>
  <p class="page-sub">Daftar unit di organisasi (read-only).</p>

  <!-- Action bar -->
  <div class="actionbar">
    <a class="pill" href="<?= app_url('pages/dashboard_pegawai.php') ?>">← Kembali</a>
    <input id="qSearch" type="search" class="input" placeholder="Cari nama unit... (klik Nama Unit untuk sort)">
  </div>

  <?php if ($query_error): ?>
    <div class="card"><div class="err">Gagal memuat data: <?= htmlspecialchars($query_error) ?></div></div>
  <?php endif; ?>

  <!-- Tabel -->
  <div class="card">
    <table class="table" id="tblUnit">
      <thead>
        <tr>
          <th style="width:70px">No</th>
          <th class="th-sort" data-key="nama_unit">Nama Unit <span class="caret">▾</span></th>
        </tr>
      </thead>
      <tbody id="tbBody">
        <?php if(count($units)): $no=1; foreach($units as $u): ?>
          <tr class="row-data">
            <td><?= $no++; ?></td>
            <td class="nama-unit"><?= htmlspecialchars($u['nama_unit']) ?></td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="2" class="empty">Belum ada data unit.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include "../../includes/footer.php"; ?>

<script>
/* util */
const $  = (q, c=document)=>c.querySelector(q);
const $$ = (q, c=document)=>Array.from(c.querySelectorAll(q));

const body = $("#tbBody");
const rows = $$("#tbBody tr.row-data").map(tr => ({
  tr,
  name: (tr.querySelector(".nama-unit")?.textContent || "").toLowerCase()
}));

let state = { q:"", asc:true };

function render(list){
  body.innerHTML = "";
  if(!list.length){
    const tr = document.createElement("tr");
    tr.innerHTML = `<td colspan="2" class="empty">Data tidak ditemukan untuk filter saat ini.</td>`;
    body.appendChild(tr);
    return;
  }
  list.forEach((r,i)=>{
    r.tr.children[0].textContent = i+1;
    body.appendChild(r.tr);
  });
}

/* search */
$("#qSearch")?.addEventListener("input", e=>{
  state.q = e.target.value.trim().toLowerCase();
  const filtered = rows.filter(r => !state.q || r.name.includes(state.q));
  const sorted = filtered.sort((a,b)=> state.asc ? a.name.localeCompare(b.name) : b.name.localeCompare(a.name));
  render(sorted);
});

/* sort */
const th = $(".th-sort[data-key='nama_unit']");
th?.addEventListener("click", ()=>{
  state.asc = !state.asc;
  th.querySelector(".caret").textContent = state.asc ? "▾" : "▴";
  const filtered = rows.filter(r => !state.q || r.name.includes(state.q));
  const sorted = filtered.sort((a,b)=> state.asc ? a.name.localeCompare(b.name) : b.name.localeCompare(a.name));
  render(sorted);
});

/* initial render */
render(rows);
</script>
