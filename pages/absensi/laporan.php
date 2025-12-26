<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';

// Wajib login & admin
require_login();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    redirect('../../index.php');
}

$page_title = "Laporan Absensi";

// Tetap include header hunter-green, tapi TANPA navbar tambahan di file ini
include __DIR__ . '/../../includes/header.php';
?>

<style>
:root{
  --hunter:#355E3B;
  --hunter-dark:#2a462f;
  --accent:#6FB18A;
  --bg:#F4F7F5;
  --surface:#FFFFFF;
  --text:#1F2937;
  --sub:#6B7280;
  --muted:#E5E7EB;
  --ring:rgba(53,94,59,.25);
  --shadow:0 8px 24px rgba(0,0,0,.08);
  --radius:14px;
}

/* Layout utama */
.page {
  max-width:1200px;
  margin:90px auto 40px;
  padding:24px;
  color:var(--text);
  background:var(--bg);
  min-height:calc(100vh - 100px);
}

/* Judul & sub */
.page h2{
  font-size:28px;
  font-weight:800;
  color:var(--hunter-dark);
  margin-bottom:6px;
}
.page p.sub{
  color:var(--sub);
  font-size:15px;
  margin-bottom:20px;
}

/* Action bar */
.actionbar{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
  align-items:center;
  background:var(--surface);
  border:1px solid var(--muted);
  border-radius:var(--radius);
  padding:14px;
  box-shadow:var(--shadow);
  position:sticky;
  top:80px;
  z-index:10;
}
.pill{
  border:1px solid var(--muted);
  border-radius:999px;
  background:var(--surface);
  color:var(--text);
  padding:10px 14px;
  text-decoration:none;
  font-size:14px;
  font-weight:500;
  display:inline-flex;
  align-items:center;
  gap:8px;
  transition:.2s;
}
.pill:hover{
  border-color:var(--accent);
  box-shadow:0 0 0 4px var(--ring);
  color:var(--hunter-dark);
}
.pill.primary{
  background:var(--hunter);
  color:#fff;
  border:none;
}
.pill.primary:hover{
  background:var(--hunter-dark);
}

.input{
  border:1px solid var(--muted);
  border-radius:10px;
  background:var(--surface);
  padding:9px 12px;
  font-size:14px;
  outline:none;
}
.input:focus{
  border-color:var(--accent);
  box-shadow:0 0 0 3px var(--ring);
}

/* Tabel */
.card{
  margin-top:20px;
  background:var(--surface);
  border:1px solid var(--muted);
  border-radius:var(--radius);
  padding:18px;
  box-shadow:var(--shadow);
}
.table-wrap{overflow:auto}
.table{
  width:100%;
  border-collapse:collapse;
  min-width:800px;
}
.table th, .table td{
  text-align:left;
  padding:12px;
  font-size:14px;
  border-bottom:1px solid var(--muted);
}
.table th{
  background:var(--bg);
  color:var(--hunter-dark);
  font-weight:700;
}
.table tr:hover{
  background:rgba(53,94,59,0.05);
}

.badge{
  padding:6px 10px;
  border-radius:999px;
  font-size:12px;
  background:var(--accent);
  color:#fff;
}

.alert{
  background:#fdeaea;
  border:1px solid #f5c2c2;
  color:#7a1a1a;
  padding:10px 14px;
  border-radius:8px;
  margin-top:12px;
}

/* Responsif */
@media(max-width:800px){
  .actionbar{flex-direction:column;align-items:stretch;}
}
</style>

<div class="page">
  <h2><?= htmlspecialchars($page_title) ?></h2>
  <p class="sub">Lihat, cari, dan ekspor data absensi pegawai dengan cepat.</p>

  <div class="actionbar">
    <a class="pill" href="../dashboard_admin.php">← Kembali ke Dashboard</a>
    <input id="qSearch" class="input" type="search" placeholder="Cari NIP / Nama...">
    <input id="dateFrom" class="input" type="date" title="Dari tanggal">
    <input id="dateTo" class="input" type="date" title="Sampai tanggal">
    <select id="fStatus" class="input">
      <option value="">Semua Status</option>
      <option>Hadir</option>
      <option>Terlambat</option>
      <option>Izin</option>
      <option>Sakit</option>
      <option>Alpha</option>
    </select>
    <a class="pill primary" href="../laporan/export.php?type=absensi">Export Excel</a>
  </div>

  <?php
  $sql = "SELECT a.id, p.nip, p.nama, a.tanggal, a.jam_masuk, a.jam_keluar, a.status
          FROM absensi a
          LEFT JOIN pegawai p ON a.pegawai_id = p.id
          ORDER BY a.tanggal DESC, a.jam_masuk DESC";
  $result = $conn->query($sql);
  $query_error = $result === false ? $conn->error : null;
  $rows = [];
  if (!$query_error && $result) {
      $rows = $result->fetch_all(MYSQLI_ASSOC);
      $result->free();
  }
  ?>

  <?php if ($query_error): ?>
    <div class="alert"><strong>Error:</strong> <?= htmlspecialchars($query_error) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="table-wrap">
      <table class="table" id="tblAbsensi">
        <thead>
          <tr>
            <th>NIP</th>
            <th>Nama</th>
            <th>Tanggal</th>
            <th>Jam Masuk</th>
            <th>Jam Keluar</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="tbBody">
          <?php if (!$query_error && count($rows)): ?>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['nip'] ?? '-') ?></td>
                <td><?= htmlspecialchars($r['nama'] ?? '-') ?></td>
                <td><?= htmlspecialchars($r['tanggal'] ?? '-') ?></td>
                <td><?= htmlspecialchars($r['jam_masuk'] ?? '-') ?></td>
                <td><?= htmlspecialchars($r['jam_keluar'] ?? '-') ?></td>
                <td><span class="badge"><?= htmlspecialchars($r['status'] ?? '-') ?></span></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" style="text-align:center;color:var(--sub)">Tidak ada data absensi.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
const $ = (q, c=document) => c.querySelector(q);
const $$ = (q, c=document) => Array.from(c.querySelectorAll(q));

const tb = $("#tbBody");
const rows = $$("#tbBody tr").map(tr => ({
  tr,
  nip: tr.children[0].innerText.toLowerCase(),
  nama: tr.children[1].innerText.toLowerCase(),
  tanggal: tr.children[2].innerText,
  status: tr.children[5].innerText.toLowerCase()
}));

let state = {q:"", from:"", to:"", status:""};

function filter(){
  const list = rows.filter(r =>
    (!state.q || r.nip.includes(state.q) || r.nama.includes(state.q)) &&
    (!state.status || r.status === state.status)
  );
  tb.innerHTML = list.length
    ? list.map(r => r.tr.outerHTML).join("")
    : `<tr><td colspan="6" style="text-align:center;color:var(--sub)">Data tidak ditemukan.</td></tr>`;
}

$("#qSearch").addEventListener("input", e => {state.q = e.target.value.toLowerCase(); filter();});
$("#fStatus").addEventListener("change", e => {state.status = e.target.value.toLowerCase(); filter();});
</script>
