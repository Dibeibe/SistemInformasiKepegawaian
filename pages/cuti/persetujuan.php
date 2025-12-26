<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';

// Wajib login & admin
require_login();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    redirect('../../index.php');
}

/* ---------- UPDATE STATUS ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'set_status') {
    $id   = (int)($_POST['id'] ?? 0);
    $aksi = ($_POST['aksi'] ?? '') === 'approve' ? 'disetujui' : 'ditolak';
    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE cuti SET status=? WHERE id=?");
        $stmt->bind_param("si", $aksi, $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['flash_ok'] = "Pengajuan cuti #$id berhasil diperbarui menjadi $aksi.";
    }
    redirect("persetujuan.php");
}

/* ---------- Ambil data pengajuan cuti ---------- */
$sql = "SELECT c.id, c.pegawai_id, c.tanggal_mulai, c.tanggal_selesai, c.alasan, c.status,
               p.nip, p.nama
        FROM cuti c
        LEFT JOIN pegawai p ON c.pegawai_id = p.id
        ORDER BY c.tanggal_mulai DESC, c.id DESC";
$res = $conn->query($sql);
$query_error = $res === false ? $conn->error : null;
$rows = [];
if (!$query_error && $res) {
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $res->free();
}

$page_title = "Persetujuan Pengajuan Cuti";
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

.page {
  max-width:1200px;
  margin:90px auto 40px;
  padding:24px;
  color:var(--text);
  background:var(--bg);
  min-height:calc(100vh - 100px);
}

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

/* ===== Action bar ===== */
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

/* ===== Table ===== */
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

/* ===== Badge ===== */
.badge{
  padding:6px 10px;
  border-radius:999px;
  font-size:12px;
  font-weight:600;
}
.badge.ok{background:#dcfce7;color:#166534;}
.badge.err{background:#fee2e2;color:#991b1b;}
.badge.warn{background:#fef9c3;color:#92400e;}

/* ===== Button ===== */
.btn-sm{
  border:1px solid var(--muted);
  background:var(--surface);
  color:var(--text);
  padding:8px 10px;
  border-radius:10px;
  font-size:13px;
  text-decoration:none;
  transition:.18s;
  cursor:pointer;
}
.btn-sm.approve{color:#166534;font-weight:600;}
.btn-sm.reject{color:#b91c1c;font-weight:600;}
.btn-sm:hover{
  border-color:var(--accent);
  box-shadow:0 0 0 3px var(--ring);
}

/* ===== Alert ===== */
.alert{
  padding:12px 14px;
  border-radius:10px;
  margin-top:12px;
}
.alert.ok{background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46;}
.alert.err{background:#fef2f2; border:1px solid #fecaca; color:#991b1b;}

/* ===== Responsive ===== */
@media(max-width:800px){
  .actionbar{flex-direction:column;align-items:stretch;}
}
</style>

<div class="page">
  <h2><?= htmlspecialchars($page_title) ?></h2>
  <p class="sub">Kelola dan setujui pengajuan cuti pegawai berdasarkan rentang tanggal, nama, atau status.</p>

  <div class="actionbar">
    <a class="pill" href="../dashboard_admin.php">← Kembali ke Dashboard</a>
    <input id="qSearch" class="input" type="search" placeholder="Cari NIP / Nama...">
    <input id="dateFrom" class="input" type="date" title="Dari tanggal">
    <input id="dateTo" class="input" type="date" title="Sampai tanggal">
    <select id="fStatus" class="input">
      <option value="">Semua Status</option>
      <option value="menunggu">Menunggu</option>
      <option value="pending">Pending</option>
      <option value="disetujui">Disetujui</option>
      <option value="ditolak">Ditolak</option>
    </select>
  </div>

  <?php if (!empty($_SESSION['flash_ok'])): ?>
    <div class="alert ok"><?= htmlspecialchars($_SESSION['flash_ok']); unset($_SESSION['flash_ok']); ?></div>
  <?php endif; ?>
  <?php if ($query_error): ?>
    <div class="alert err"><strong>Query Error:</strong> <?= htmlspecialchars($query_error) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="table-wrap">
      <table class="table" id="tblCuti">
        <thead>
          <tr>
            <th>NIP</th>
            <th>Nama</th>
            <th>Tanggal Mulai</th>
            <th>Tanggal Selesai</th>
            <th>Alasan</th>
            <th>Status</th>
            <th style="min-width:160px">Aksi</th>
          </tr>
        </thead>
        <tbody id="tbBody">
          <?php if (!$query_error && count($rows)): ?>
            <?php foreach ($rows as $r): 
              $status = strtolower($r['status'] ?? '');
              $badgeClass = $status === 'disetujui' ? 'ok' : ($status === 'ditolak' ? 'err' : 'warn');
            ?>
              <tr data-date="<?= htmlspecialchars($r['tanggal_mulai'] ?? '') ?>" data-status="<?= htmlspecialchars($status) ?>">
                <td><?= htmlspecialchars($r['nip'] ?? '-') ?></td>
                <td><?= htmlspecialchars($r['nama'] ?? '-') ?></td>
                <td><?= htmlspecialchars($r['tanggal_mulai'] ?? '-') ?></td>
                <td><?= htmlspecialchars($r['tanggal_selesai'] ?? '-') ?></td>
                <td><?= htmlspecialchars($r['alasan'] ?? '-') ?></td>
                <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($r['status'] ?? '-') ?></span></td>
                <td>
                  <?php if ($status === 'pending' || $status === 'menunggu'): ?>
                    <form method="POST" style="display:inline">
                      <input type="hidden" name="action" value="set_status">
                      <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                      <input type="hidden" name="aksi" value="approve">
                      <button type="submit" class="btn-sm approve" onclick="return confirm('Setujui pengajuan cuti ini?')">Setujui</button>
                    </form>
                    <form method="POST" style="display:inline">
                      <input type="hidden" name="action" value="set_status">
                      <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                      <input type="hidden" name="aksi" value="reject">
                      <button type="submit" class="btn-sm reject" onclick="return confirm('Tolak pengajuan cuti ini?')">Tolak</button>
                    </form>
                  <?php else: ?>
                    <span class="badge">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7" style="text-align:center;color:var(--sub)">Tidak ada pengajuan cuti.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
const $  = (q, c=document) => c.querySelector(q);
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
    : `<tr><td colspan="7" style="text-align:center;color:var(--sub)">Data tidak ditemukan.</td></tr>`;
}

$("#qSearch").addEventListener("input", e => {state.q = e.target.value.toLowerCase(); filter();});
$("#fStatus").addEventListener("change", e => {state.status = e.target.value.toLowerCase(); filter();});
</script>
