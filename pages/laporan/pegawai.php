<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';

// wajib login & admin
require_login();
if (($_SESSION['role'] ?? '') !== 'admin') { redirect('../../index.php'); }

$page_title = "Laporan Pegawai";

/* --- Data pegawai + unit + jabatan --- */
$sql = "SELECT 
            p.id, p.nip, p.nama, p.tanggal_masuk,
            u.nama_unit,
            j.nama_jabatan
        FROM pegawai p
        LEFT JOIN unit u    ON u.id = p.unit_id
        LEFT JOIN jabatan j ON j.id = p.jabatan_id
        ORDER BY p.nama ASC";
$result = $conn->query($sql);
$query_error = $result === false ? $conn->error : null;

$rows = [];
if (!$query_error && $result) {
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}

/* --- Kumpulan filter unik --- */
$setUnit = $setJab = [];
foreach ($rows as $r) {
    if (!empty($r['nama_unit']))    $setUnit[$r['nama_unit']] = true;
    if (!empty($r['nama_jabatan'])) $setJab[$r['nama_jabatan']] = true;
}
$units    = array_keys($setUnit);    sort($units, SORT_NATURAL|SORT_FLAG_CASE);
$jabatans = array_keys($setJab);     sort($jabatans, SORT_NATURAL|SORT_FLAG_CASE);

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

/* Matikan sidebar lokal jika ada */
#sidebar, .sidebar, .left-sidebar, .app-sidebar, .sidenav{display:none!important;width:0!important}
.content, #content, .main, .main-content, .container-page{margin-left:0!important}
body{padding-left:0!important}

/* Layout */
.page{max-width:1200px;margin:90px auto 40px;padding:24px;color:var(--text)}
.title{margin:0 0 6px;font-size:26px;font-weight:700;color:var(--hunter-dark)}
.sub{margin:0 0 18px;color:var(--sub)}

/* Action bar (prompt yang sama) */
.actionbar{
  position:sticky;top:72px;z-index:10;
  display:flex;flex-wrap:wrap;gap:10px;align-items:center;
  background:var(--card);border:1px solid var(--muted);border-radius:var(--radius);
  box-shadow:var(--shadow);padding:12px 16px;
}
.pill{
  border:1px solid var(--muted);border-radius:10px;padding:9px 14px;
  background:var(--card);color:var(--text);text-decoration:none;font-size:14px;font-weight:500;transition:.2s;
}
.pill:hover{border-color:var(--accent);box-shadow:0 0 0 4px var(--ring);color:var(--hunter-dark)}
.pill.primary{background:var(--hunter);color:#fff;border:none}
.pill.primary:hover{background:var(--hunter-dark)}
.input{
  border:1px solid var(--muted);border-radius:10px;padding:9px 12px;background:var(--card);transition:.15s;
}
.input:focus{outline:none;box-shadow:0 0 0 4px var(--ring);border-color:var(--accent)}
#qSearch{flex:1;min-width:220px}

/* Card & Table */
.card{background:var(--card);border:1px solid var(--muted);border-radius:var(--radius);box-shadow:var(--shadow);padding:16px;margin-top:16px}
.table-wrap{overflow:auto}
.table{width:100%;border-collapse:collapse;min-width:760px}
.table th,.table td{padding:12px;border-bottom:1px solid var(--muted);font-size:14px;text-align:left}
.table th{background:var(--bg);color:var(--hunter-dark);font-weight:700}
.table tbody tr:nth-child(odd){background:rgba(53,94,59,.03)}
.table tbody tr:hover{background:rgba(53,94,59,.06)}
.th-sort{cursor:pointer;user-select:none}
.th-sort .caret{margin-left:6px;opacity:.55}
.th-sort.active{color:var(--hunter-dark)}
.th-sort.active .caret{opacity:1}

/* Badge */
.badge{
  display:inline-block;padding:6px 10px;border-radius:999px;font-size:12px;
  border:1px solid var(--muted);background:linear-gradient(180deg,#fff,#f9fafb);
  color:var(--text);
}

/* Responsive */
@media(max-width:780px){
  .table{min-width:640px}
}
</style>

<div class="page">
  <h2 class="title"><?= htmlspecialchars($page_title) ?></h2>
  <p class="sub">Cari cepat, filter berdasarkan Unit/Jabatan & rentang tanggal masuk, lalu urutkan kolom sesuai kebutuhan.</p>

  <!-- Action Bar -->
  <div class="actionbar">
    <a class="pill" href="../dashboard_admin.php">Kembali ke Dashboard</a>
    <a class="pill primary" href="export.php?type=pegawai">Export Excel</a>

    <input id="qSearch" class="input" type="search" placeholder="Cari NIP / Nama…">
    <input id="dateFrom" class="input" type="date" title="Tanggal masuk dari">
    <input id="dateTo"   class="input" type="date" title="Tanggal masuk sampai">

    <select id="fUnit" class="input" title="Filter Unit">
      <option value="">Semua Unit</option>
      <?php foreach ($units as $u): ?>
        <option value="<?= htmlspecialchars($u) ?>"><?= htmlspecialchars($u) ?></option>
      <?php endforeach; ?>
    </select>

    <select id="fJabatan" class="input" title="Filter Jabatan">
      <option value="">Semua Jabatan</option>
      <?php foreach ($jabatans as $j): ?>
        <option value="<?= htmlspecialchars($j) ?>"><?= htmlspecialchars($j) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <?php if ($query_error): ?>
    <div class="alert err" style="padding:12px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:10px;margin-top:12px">
      <strong>Query Error:</strong> <?= htmlspecialchars($query_error) ?>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="table-wrap">
      <table class="table" id="tblPegawai">
        <thead>
          <tr>
            <th class="th-sort" data-key="nip">NIP <span class="caret">▾</span></th>
            <th class="th-sort" data-key="nama">Nama <span class="caret">▾</span></th>
            <th class="th-sort" data-key="nama_unit">Unit <span class="caret">▾</span></th>
            <th class="th-sort" data-key="nama_jabatan">Jabatan <span class="caret">▾</span></th>
            <th class="th-sort" data-key="tanggal_masuk">Tanggal Masuk <span class="caret">▾</span></th>
          </tr>
        </thead>
        <tbody id="tbBody">
          <?php if (!$query_error && count($rows)): ?>
            <?php foreach ($rows as $r): ?>
              <tr data-unit="<?= htmlspecialchars($r['nama_unit'] ?? '') ?>"
                  data-jabatan="<?= htmlspecialchars($r['nama_jabatan'] ?? '') ?>"
                  data-date="<?= htmlspecialchars($r['tanggal_masuk'] ?? '') ?>">
                <td><?= htmlspecialchars($r['nip'] ?? '-') ?></td>
                <td><?= htmlspecialchars($r['nama'] ?? '-') ?></td>
                <td><span class="badge"><?= htmlspecialchars($r['nama_unit'] ?? '-') ?></span></td>
                <td><span class="badge"><?= htmlspecialchars($r['nama_jabatan'] ?? '-') ?></span></td>
                <td><?= htmlspecialchars($r['tanggal_masuk'] ?? '-') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5" style="text-align:center;color:var(--sub)">Tidak ada data pegawai.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
// Helpers
const $  = (q, c=document) => c.querySelector(q);
const $$ = (q, c=document) => Array.from(c.querySelectorAll(q));

const tb = $("#tbBody");
const rows = $$("#tbBody tr").map(tr => {
  const td = tr.querySelectorAll("td");
  return {
    tr,
    nip:           (td[0]?.textContent || "").toLowerCase(),
    nama:          (td[1]?.textContent || "").toLowerCase(),
    nama_unit:     (td[2]?.innerText   || "").toLowerCase(),
    nama_jabatan:  (td[3]?.innerText   || "").toLowerCase(),
    tanggal_masuk: (td[4]?.textContent || ""),
    unit:          tr.getAttribute("data-unit")?.toLowerCase() || "",
    jabatan:       tr.getAttribute("data-jabatan")?.toLowerCase() || "",
    rawDate:       tr.getAttribute("data-date") || ""
  };
});

let state = { q:"", unit:"", jab:"", from:"", to:"", sortKey:"nama", asc:true };

function withinRange(dateStr, fromStr, toStr){
  if(!fromStr && !toStr) return true;
  const d = dateStr || "";
  return (!fromStr || d >= fromStr) && (!toStr || d <= toStr);
}

function apply(){
  let list = rows.filter(r =>
    (!state.q || r.nip.includes(state.q) || r.nama.includes(state.q)) &&
    (!state.unit || r.unit === state.unit) &&
    (!state.jab  || r.jabatan === state.jab) &&
    withinRange(r.rawDate, state.from, state.to)
  );

  list.sort((a,b)=>{
    const k = state.sortKey;
    const x = (a[k] || "").toString().toLowerCase();
    const y = (b[k] || "").toString().toLowerCase();
    return state.asc ? x.localeCompare(y) : y.localeCompare(x);
  });

  tb.innerHTML = "";
  if (list.length === 0){
    const tr = document.createElement("tr");
    tr.innerHTML = `<td colspan="5" style="text-align:center;color:var(--sub)">Data tidak ditemukan untuk filter saat ini.</td>`;
    tb.appendChild(tr);
    return;
  }
  list.forEach(r => tb.appendChild(r.tr));
}

// Events
$("#qSearch").addEventListener("input", e => { state.q   = e.target.value.trim().toLowerCase(); apply(); });
$("#fUnit").addEventListener("change",   e => { state.unit = (e.target.value || "").toLowerCase(); apply(); });
$("#fJabatan").addEventListener("change",e => { state.jab  = (e.target.value || "").toLowerCase(); apply(); });
$("#dateFrom").addEventListener("change",e => { state.from = e.target.value; apply(); });
$("#dateTo").addEventListener("change",  e => { state.to   = e.target.value; apply(); });

// Sort klik header
$$(".th-sort").forEach(th => {
  th.addEventListener("click", () => {
    const k = th.getAttribute("data-key");
    if(state.sortKey === k){ state.asc = !state.asc; } else { state.sortKey = k; state.asc = true; }
    $$(".th-sort").forEach(x => x.classList.remove("active"));
    th.classList.add("active");
    th.querySelector(".caret").textContent = state.asc ? "▾" : "▴";
    apply();
  });
});
$(".th-sort[data-key='"+state.sortKey+"']")?.classList.add("active");

// Keyboard quick-find: tekan "/"
let lock=false;
window.addEventListener('keydown', (e)=>{
  if(e.key === '/' && !lock){
    e.preventDefault(); $("#qSearch").focus();
    lock=true; setTimeout(()=>lock=false, 200);
  }
});

apply();
</script>
