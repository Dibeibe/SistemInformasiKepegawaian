<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';

require_login();
if (($_SESSION['role'] ?? '') !== 'admin') { redirect('../../index.php'); }

$page_title = "Daftar Penggajian";

/* -------------------- DELETE (via POST) -------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM gaji WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['flash_ok'] = "Data penggajian berhasil dihapus.";
    }
    redirect("list.php");
}

/* Helpers */
function rupiah($v) {
    if ($v === null || $v === '') return '-';
    return 'Rp ' . number_format((float)$v, 0, ',', '.');
}
$namaBulan = [1=>"Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];

/* -------------------- DATA -------------------- */
$sql = "SELECT g.id, g.bulan, g.tahun, g.gaji_pokok, g.potongan, g.total,
               p.nip, p.nama
        FROM gaji g
        LEFT JOIN pegawai p ON g.pegawai_id = p.id
        ORDER BY COALESCE(g.tahun,0) DESC, COALESCE(g.bulan,0) DESC, p.nama ASC";
$result = $conn->query($sql);
$query_error = $result === false ? $conn->error : null;

$rows = [];
if (!$query_error && $result) {
    while ($r = $result->fetch_assoc()) {
        $gaji_pokok = (float)($r['gaji_pokok'] ?? 0);
        $potongan   = (float)($r['potongan']   ?? 0);
        $total      = (float)($r['total']      ?? ($gaji_pokok - $potongan)); // fallback
        $tunjangan  = $total - $gaji_pokok + $potongan; if ($tunjangan < 0) $tunjangan = 0;

        $r['gaji_pokok'] = $gaji_pokok;
        $r['tunjangan']  = $tunjangan;
        $r['potongan']   = $potongan;
        $r['total']      = $total;
        $rows[] = $r;
    }
    $result->free();
}

/* Tahun unik untuk filter */
$tahunSet = [];
foreach ($rows as $r) if (!empty($r['tahun'])) $tahunSet[(int)$r['tahun']] = true;
$daftarTahun = array_keys($tahunSet);
rsort($daftarTahun);

/* Header global (navbar & footer sudah include di sini) */
include __DIR__ . '/../../includes/header.php';
?>

<style>
:root{
  --hunter:#355E3B; --hunter-dark:#2A462F; --accent:#6FB18A;
  --bg:#F5FAF5; --card:#FFFFFF; --text:#1F2937; --sub:#6B7280;
  --muted:#E5E7EB; --ring:rgba(53,94,59,.25);
  --shadow:0 6px 20px rgba(0,0,0,.08); --radius:14px;
}

/* Matikan sidebar lama */
#sidebar,.sidebar,.left-sidebar,.app-sidebar,.sidenav{display:none!important;width:0!important}
.content,#content,.main,.main-content,.container-page{margin-left:0!important}
body{padding-left:0!important}

/* ===== Layout ===== */
.page{max-width:1200px;margin:90px auto 40px;padding:24px;color:var(--text)}
.title{margin:0 0 6px;font-size:26px;font-weight:700;color:var(--hunter-dark)}
.sub{margin:0 0 18px;color:var(--sub)}

/* ===== Actionbar ===== */
.actionbar{
  position:sticky; top:72px; z-index:10;
  display:flex; flex-wrap:wrap; gap:10px; align-items:center;
  background:var(--card); border:1px solid var(--muted);
  border-radius:var(--radius); padding:12px 16px; box-shadow:var(--shadow);
}
.pill{
  border:1px solid var(--muted); border-radius:10px; padding:9px 14px;
  background:var(--card); color:var(--text); text-decoration:none;
  font-size:14px; font-weight:500; transition:.2s;
}
.pill:hover{border-color:var(--accent);box-shadow:0 0 0 4px var(--ring);color:var(--hunter-dark)}
.pill.primary{background:var(--hunter);color:#fff;border:none}
.pill.primary:hover{background:var(--hunter-dark)}
.pill.ghost{background:transparent}
.input{
  border:1px solid var(--muted); border-radius:10px; padding:9px 12px;
  background:var(--card); transition:.2s;
}
.input:focus,.pill:focus,.btn-sm:focus{outline:none;box-shadow:0 0 0 3px var(--ring)}
#qSearch{flex:1;min-width:220px}

/* ===== Card & Table ===== */
.card{background:var(--card);border:1px solid var(--muted);border-radius:var(--radius);box-shadow:var(--shadow);padding:16px;margin-top:16px}
.table-wrap{overflow:auto}
.table{width:100%;border-collapse:separate;border-spacing:0;min-width:980px}
.table thead th{
  position:sticky; top:0; z-index:2; white-space:nowrap;
  background:linear-gradient(180deg,var(--card),rgba(255,255,255,.92));
  border-bottom:1px solid var(--muted); color:var(--hunter-dark);
  font-weight:700; font-size:13px; text-align:left; padding:12px;
}
.table tbody td{padding:12px;border-bottom:1px solid var(--muted);font-size:14px;vertical-align:middle}
.table tbody tr:nth-child(odd){background:rgba(53,94,59,.03)}
.table tbody tr:hover{background:rgba(53,94,59,.06)}
/* alignment kolom */
.table th:nth-child(3), .table td:nth-child(3){text-align:left; min-width:110px;} /* Bulan */
.table th:nth-child(4), .table td:nth-child(4){text-align:right; width:90px;}     /* Tahun */
.table th:nth-child(5), .table td:nth-child(5),
.table th:nth-child(6), .table td:nth-child(6),
.table th:nth-child(7), .table td:nth-child(7),
.table th:nth-child(8), .table td:nth-child(8){
  text-align:right; font-variant-numeric: tabular-nums; white-space:nowrap;
}
.table th:nth-child(9), .table td:nth-child(9){text-align:right; min-width:220px}

/* ===== Actions ===== */
.cell-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}
.btn-sm{
  border:1px solid var(--muted); background:var(--card); color:var(--text);
  padding:6px 10px; border-radius:10px; font-size:13px; text-decoration:none; transition:.18s;
}
.btn-sm:hover{border-color:var(--accent);box-shadow:0 0 0 3px var(--ring)}
.btn-info{color:#1d4ed8} .btn-edit{color:#b45309} .btn-del{color:#b91c1c}

/* ===== Alerts ===== */
.alert{padding:12px 14px;border-radius:10px;margin-top:12px}
.alert.ok{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
.alert.err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}

/* ===== Pagination ===== */
.pager{
  display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:12px
}
.pager .info{color:var(--sub);font-size:13px}
.pager .group{display:flex;gap:8px;align-items:center}
.select{border:1px solid var(--muted);border-radius:10px;padding:6px 10px;background:var(--card)}
</style>

<div class="page">
  <h2 class="title"><?= htmlspecialchars($page_title) ?></h2>
  <p class="sub">Cari cepat NIP/Nama, filter Bulan & Tahun, klik judul kolom untuk mengurutkan. (Tekan <kbd>/</kbd> untuk fokus ke pencarian)</p>

  <!-- ACTION BAR -->
  <div class="actionbar">
    <a class="pill ghost" href="../dashboard_admin.php">Kembali</a>
    <a class="pill primary" href="input.php">Input Penggajian</a>

    <input id="qSearch" class="input" type="search" placeholder="Cari NIP / Nama…">

    <select id="fBulan" class="input" title="Filter Bulan">
      <option value="">Semua Bulan</option>
      <?php for ($b=1;$b<=12;$b++): ?>
        <option value="<?= $b ?>"><?= $namaBulan[$b] ?></option>
      <?php endfor; ?>
    </select>

    <select id="fTahun" class="input" title="Filter Tahun">
      <option value="">Semua Tahun</option>
      <?php foreach ($daftarTahun as $t): ?>
        <option value="<?= (int)$t ?>"><?= (int)$t ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <!-- FLASH -->
  <?php if (!empty($_SESSION['flash_ok'])): ?>
    <div class="alert ok"><?= htmlspecialchars($_SESSION['flash_ok']); unset($_SESSION['flash_ok']); ?></div>
  <?php endif; ?>
  <?php if ($query_error): ?>
    <div class="alert err"><strong>Query Error:</strong> <?= htmlspecialchars($query_error) ?></div>
  <?php endif; ?>

  <!-- TABLE -->
  <div class="card">
    <div class="table-wrap">
      <table class="table" id="tblGaji">
        <thead>
          <tr>
            <th class="th-sort" data-key="nip">NIP <span class="caret">▾</span></th>
            <th class="th-sort" data-key="nama">Nama <span class="caret">▾</span></th>
            <th class="th-sort" data-key="bulan">Bulan <span class="caret">▾</span></th>
            <th class="th-sort" data-key="tahun">Tahun <span class="caret">▾</span></th>
            <th class="th-sort" data-key="gaji_pokok">Gaji Pokok <span class="caret">▾</span></th>
            <th class="th-sort" data-key="tunjangan">Tunjangan <span class="caret">▾</span></th>
            <th class="th-sort" data-key="potongan">Potongan <span class="caret">▾</span></th>
            <th class="th-sort" data-key="total">Total <span class="caret">▾</span></th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="tbBody">
          <?php if (!$query_error && count($rows)): ?>
            <?php foreach ($rows as $r): 
              $bulanAngka = (int)($r['bulan'] ?? 0);
              $bulanLabel = $bulanAngka>=1 && $bulanAngka<=12 ? $namaBulan[$bulanAngka] : ($r['bulan'] ?? '-');
              $tahunVal   = $r['tahun'] ?? '';
            ?>
              <tr data-bulan="<?= htmlspecialchars($bulanAngka ?: '') ?>" data-tahun="<?= htmlspecialchars($tahunVal) ?>">
                <td><?= htmlspecialchars($r['nip'] ?? '-') ?></td>
                <td><?= htmlspecialchars($r['nama'] ?? '-') ?></td>
                <td><?= htmlspecialchars($bulanLabel) ?></td>
                <td class="num"><?= htmlspecialchars($tahunVal ?: '-') ?></td>
                <td class="num"><?= rupiah($r['gaji_pokok']) ?></td>
                <td class="num"><?= rupiah($r['tunjangan']) ?></td>
                <td class="num"><?= rupiah($r['potongan']) ?></td>
                <td class="num"><?= rupiah($r['total']) ?></td>
                <td class="cell-actions">
                  <?php if (!empty($r['id'])): ?>
                    <a class="btn-sm btn-info" href="detail.php?id=<?= urlencode($r['id']) ?>">Detail</a>
                    <a class="btn-sm btn-edit" href="input.php?id=<?= urlencode($r['id']) ?>">Edit</a>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Hapus data gaji ini?')">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                      <button type="submit" class="btn-sm btn-del">Hapus</button>
                    </form>
                  <?php else: ?>
                    <span class="btn-sm" style="opacity:.6;cursor:default">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" style="padding:28px;">
                <div style="border:1px dashed var(--muted);border-radius:12px;padding:26px;text-align:center;background:#fff;color:var(--sub)">
                  <div style="font-weight:700;color:var(--hunter-dark);margin-bottom:6px">Belum ada data penggajian</div>
                  <div style="margin-bottom:14px;">Mulai dengan menambahkan data penggajian.</div>
                  <a class="pill primary" href="input.php">Input Penggajian</a>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- PAGINATION -->
    <div class="pager" id="pager" style="display:none">
      <div class="info" id="pagerInfo"></div>
      <div class="group">
        <label for="pageSize" class="sub">Baris/hal:</label>
        <select id="pageSize" class="select">
          <option>10</option><option selected>20</option><option>30</option><option>50</option>
        </select>
        <button id="prevBtn" class="pill">Sebelumnya</button>
        <button id="nextBtn" class="pill">Berikutnya</button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
// ===== Helpers =====
const $  = (q, c=document) => c.querySelector(q);
const $$ = (q, c=document) => Array.from(c.querySelectorAll(q));
const tb = $("#tbBody");

const rows = $$("#tbBody tr").map(tr => {
  const td = tr.querySelectorAll("td");
  return {
    tr,
    nip:        (td[0]?.textContent || "").toLowerCase(),
    nama:       (td[1]?.textContent || "").toLowerCase(),
    bulan:      (td[2]?.textContent || ""),
    tahun:      (td[3]?.textContent || ""),
    gaji_pokok: (td[4]?.textContent || ""),
    tunjangan:  (td[5]?.textContent || ""),
    potongan:   (td[6]?.textContent || ""),
    total:      (td[7]?.textContent || ""),
    rawBulan:   (tr.getAttribute("data-bulan") || ""),
    rawTahun:   (tr.getAttribute("data-tahun") || "")
  };
});

let state = { q:"", bulan:"", tahun:"", sortKey:"tahun", asc:false };

// Pagination state
let page = 1;
let pageSize = 20;
let currentList = [];

function numVal(s){ const n=(s||"").replace(/[^\d\-]/g,''); return n?parseInt(n,10):0; }

function sortList(list){
  const k = state.sortKey;
  return list.sort((a,b)=>{
    let x, y;
    if (['gaji_pokok','tunjangan','potongan','total'].includes(k)){
      x = numVal(a[k]); y = numVal(b[k]); return state.asc ? (x-y) : (y-x);
    } else if (k === 'tahun') {
      x = parseInt(a[k]) || 0; y = parseInt(b[k]) || 0; return state.asc ? (x-y) : (y-x);
    } else if (k === 'bulan') {
      x = parseInt(a.rawBulan) || 0; y = parseInt(b.rawBulan) || 0; return state.asc ? (x-y) : (y-x);
    } else {
      x = (a[k]||"").toString().toLowerCase(); y = (b[k]||"").toString().toLowerCase();
      return state.asc ? x.localeCompare(y) : y.localeCompare(x);
    }
  });
}

function apply(){
  let list = rows.filter(r =>
    (!state.q || r.nip.includes(state.q) || r.nama.includes(state.q)) &&
    (!state.bulan || r.rawBulan === state.bulan) &&
    (!state.tahun || r.rawTahun === state.tahun)
  );

  currentList = sortList(list);
  page = 1; render();
}

function render(){
  tb.innerHTML = "";
  const total = currentList.length;
  const start = (page-1)*pageSize;
  const end   = Math.min(start + pageSize, total);

  if (total === 0){
    const tr = document.createElement("tr");
    tr.innerHTML = `<td colspan="9" style="text-align:center;color:var(--sub)">Data tidak ditemukan untuk filter saat ini.</td>`;
    tb.appendChild(tr);
    $("#pager").style.display = "none";
    return;
  }

  for (let i=start; i<end; i++) tb.appendChild(currentList[i].tr);

  // pager info
  $("#pager").style.display = "flex";
  $("#pagerInfo").textContent = `Menampilkan ${start+1}-${end} dari ${total} data`;
  $("#prevBtn").disabled = page === 1;
  $("#nextBtn").disabled = end >= total;
}

$("#qSearch").addEventListener("input", e => { state.q = e.target.value.trim().toLowerCase(); apply(); });
$("#fBulan").addEventListener("change", e => { state.bulan = e.target.value; apply(); });
$("#fTahun").addEventListener("change", e => { state.tahun = e.target.value; apply(); });

$$(".th-sort").forEach(th => {
  th.addEventListener("click", () => {
    const k = th.getAttribute("data-key");
    if(state.sortKey === k){ state.asc = !state.asc; }
    else { state.sortKey = k; state.asc = ['tahun','total','gaji_pokok','tunjangan','potongan','bulan'].includes(k) ? false : true; }
    $$(".th-sort").forEach(x => x.classList.remove("active"));
    th.classList.add("active");
    th.querySelector(".caret").textContent = state.asc ? "▾" : "▴";
    // re-sort currentList & render tanpa reset filter
    currentList = sortList(currentList);
    page = 1; render();
  });
});

// Pagination events
$("#pageSize").addEventListener("change", e => { pageSize = parseInt(e.target.value,10)||20; page=1; render(); });
$("#prevBtn").addEventListener("click", ()=>{ if(page>1){ page--; render(); } });
$("#nextBtn").addEventListener("click", ()=>{ const maxPage = Math.ceil(currentList.length/pageSize); if(page<maxPage){ page++; render(); } });

// Shortcut: "/" fokus ke pencarian
let lock=false;
window.addEventListener('keydown', (e)=>{
  if(e.key==='/' && !lock){
    e.preventDefault();
    $("#qSearch").focus();
    lock=true; setTimeout(()=>lock=false,200);
  }
});

// Initial render
$(".th-sort[data-key='"+state.sortKey+"']")?.classList.add("active");
apply();
</script>
