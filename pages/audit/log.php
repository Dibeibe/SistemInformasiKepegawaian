<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';

require_login();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    redirect('../dashboard_pegawai.php');
}

$page_title = "Audit Log";

/* --- data --- */
$sql = "SELECT l.*, u.username, l.waktu AS _ord_waktu
        FROM audit_log l
        LEFT JOIN users u ON l.user_id = u.id
        ORDER BY _ord_waktu DESC, l.id DESC";
$res = $conn->query($sql);
$err = $res === false ? $conn->error : null;

$rows = [];
if (!$err && $res) {
  while ($r = $res->fetch_assoc()) {
    $r['_aksi']  = $r['aksi'] ?? $r['action'] ?? $r['event'] ?? '';
    $r['_ket']   = $r['keterangan'] ?? $r['deskripsi'] ?? $r['detail'] ?? $r['message'] ?? '';
    $r['_waktu'] = $r['waktu'] ?? '';
    $r['_ip']    = $r['ip_address'] ?? $r['ip'] ?? '';
    $r['_user']  = $r['username'] ?? ($r['user'] ?? '');
    $rows[] = $r;
  }
  $res->free();
}

$aksiList = array_values(array_map('strval', array_keys(array_flip(array_column($rows, '_aksi')))));

include __DIR__ . '/../../includes/header.php';
?>
<style>
:root{
  --hunter:#355E3B; --hunter-dark:#2A462F; --accent:#6FB18A;
  --bg:#F5FAF5; --card:#FFFFFF; --text:#1F2937; --sub:#6B7280;
  --muted:#E5E7EB; --ring:rgba(53,94,59,.25); --shadow:0 6px 20px rgba(0,0,0,.08);
  --radius:14px;
}

/* TIDAK ada aturan untuk html/body/footer di halaman ini */
#sidebar, .sidebar, .left-sidebar, .app-sidebar, .sidenav{display:none!important;width:0!important}

.page{
  max-width:1200px; margin:90px auto 0; padding:24px 24px 32px; /* padding-bottom utk jarak dg footer */
  color:var(--text);
}
.title{margin:0 0 6px; font-size:26px; font-weight:700; color:var(--hunter-dark)}
.sub{margin:0 0 18px; color:var(--sub)}

.actionbar{
  position:sticky; top:72px; z-index:10;
  display:flex; flex-wrap:wrap; gap:10px; align-items:center;
  background:var(--card); border:1px solid var(--muted); border-radius:var(--radius);
  box-shadow:var(--shadow); padding:12px 16px;
}
.pill{
  border:1px solid var(--muted); border-radius:10px; padding:9px 14px; background:var(--card);
  text-decoration:none; color:var(--text); font-size:14px; font-weight:500; transition:.2s;
}
.pill:hover{border-color:var(--accent); box-shadow:0 0 0 4px var(--ring); color:var(--hunter-dark)}
.input{border:1px solid var(--muted); border-radius:10px; padding:9px 12px; background:var(--card)}
.input:focus{outline:none; box-shadow:0 0 0 4px var(--ring)}
#qSearch{flex:1; min-width:260px}

.card{background:var(--card); border:1px solid var(--muted); border-radius:var(--radius); box-shadow:var(--shadow); padding:16px; margin-top:16px}
.table-wrap{overflow:auto}
.table{width:100%; border-collapse:collapse; min-width:880px}
.table th,.table td{padding:12px; border-bottom:1px solid var(--muted); font-size:14px; text-align:left; vertical-align:top}
.table th{background:var(--bg); color:var(--hunter-dark); font-weight:700}
.table tbody tr:nth-child(odd){background:rgba(53,94,59,.03)}
.table tbody tr:hover{background:rgba(53,94,59,.06)}
.th-sort{cursor:pointer}
.th-sort .caret{margin-left:6px; opacity:.55}
.th-sort.active{color:var(--hunter-dark)}
.badge{display:inline-block; padding:6px 10px; border-radius:999px; font-size:12px; border:1px solid var(--muted); background:linear-gradient(180deg,#fff,#f9fafb)}
.mono{font-family:ui-monospace,Menlo,Consolas,monospace}
.alert{padding:12px 14px; border-radius:10px; margin-top:12px}
.alert.err{background:#fef2f2; border:1px solid #fecaca; color:#991b1b}
</style>

<div class="page">
  <h2 class="title"><?= htmlspecialchars($page_title) ?></h2>
  <p class="sub">Cari cepat, filter berdasarkan aksi & rentang waktu, dan urutkan kolom sesuai kebutuhan.</p>

  <div class="actionbar">
    <a class="pill" href="../dashboard_admin.php">Kembali ke Dashboard</a>
    <input id="qSearch" class="input" type="search" placeholder="Cari User / Aksi / Keterangan / IP…">
    <select id="fAksi" class="input">
      <option value="">Semua Aksi</option>
      <?php foreach ($aksiList as $a): ?>
        <option value="<?= htmlspecialchars($a) ?>"><?= htmlspecialchars($a) ?></option>
      <?php endforeach; ?>
    </select>
    <input id="from" class="input" type="datetime-local" title="Dari waktu">
    <input id="to"   class="input" type="datetime-local" title="Sampai waktu">
  </div>

  <?php if ($err): ?>
    <div class="alert err"><strong>Query Error:</strong> <?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="table-wrap">
      <table class="table" id="tblLog">
        <thead>
          <tr>
            <th class="th-sort" data-key="no" style="width:70px"># <span class="caret">▾</span></th>
            <th class="th-sort" data-key="user">User <span class="caret">▾</span></th>
            <th class="th-sort" data-key="aksi">Aksi <span class="caret">▾</span></th>
            <th class="th-sort" data-key="ket">Keterangan <span class="caret">▾</span></th>
            <th class="th-sort" data-key="waktu">Waktu <span class="caret">▾</span></th>
            <th class="th-sort" data-key="ip">IP <span class="caret">▾</span></th>
          </tr>
        </thead>
        <tbody id="tbBody">
          <?php if (!$err && count($rows)): $i=1; foreach ($rows as $r): ?>
            <tr data-aksi="<?= htmlspecialchars($r['_aksi']) ?>" data-waktu="<?= htmlspecialchars($r['_waktu']) ?>">
              <td class="mono"><?= $i++; ?></td>
              <td><?= htmlspecialchars($r['_user'] ?: '-') ?></td>
              <td><span class="badge"><?= htmlspecialchars($r['_aksi'] ?: '-') ?></span></td>
              <td><?= nl2br(htmlspecialchars($r['_ket'] ?: '-')) ?></td>
              <td class="mono"><?= htmlspecialchars($r['_waktu'] ?: '-') ?></td>
              <td class="mono"><?= htmlspecialchars($r['_ip'] ?: '-') ?></td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="6" style="text-align:center;color:var(--sub)">Belum ada data audit log.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
/* PAKAI footer bawaan. Kalau footer sudah otomatis diinject dari layout global,
   hapus baris include di bawah. */
include __DIR__ . '/../../includes/footer.php';
?>

<script>
const $  = (q, c=document) => c.querySelector(q);
const $$ = (q, c=document) => Array.from(c.querySelectorAll(q));

const tb = $("#tbBody");
const rows = $$("#tbBody tr").map(tr => {
  const td = tr.querySelectorAll("td");
  return {
    tr,
    no:    parseInt(td[0]?.textContent || "0", 10),
    user:  (td[1]?.textContent || "").toLowerCase(),
    aksi:  (tr.getAttribute("data-aksi") || "").toLowerCase(),
    aksiLabel: (td[2]?.innerText || "").toLowerCase(),
    ket:   (td[3]?.innerText || "").toLowerCase(),
    waktu: tr.getAttribute("data-waktu") || "",
    ip:    (td[5]?.textContent || "").toLowerCase()
  };
});

let state = { q:"", aksi:"", from:"", to:"", sortKey:"waktu", asc:false };

function inRange(iso, fromIso, toIso){
  if(!fromIso && !toIso) return true;
  const t = iso || "";
  return (!fromIso || t >= fromIso) && (!toIso || t <= toIso);
}

function apply(){
  let list = rows.filter(r =>
    (!state.q || r.user.includes(state.q) || r.aksiLabel.includes(state.q) || r.ket.includes(state.q) || r.ip.includes(state.q)) &&
    (!state.aksi || r.aksi === state.aksi) &&
    inRange(r.waktu, state.from, state.to)
  );

  list.sort((a,b)=>{
    const k = state.sortKey;
    if (k === 'no') return state.asc ? (a.no - b.no) : (b.no - a.no);
    if (k === 'waktu') return state.asc ? a.waktu.localeCompare(b.waktu) : b.waktu.localeCompare(a.waktu);
    const x = (a[k] || "").toString().toLowerCase();
    const y = (b[k] || "").toString().toLowerCase();
    return state.asc ? x.localeCompare(y) : y.localeCompare(x);
  });

  tb.innerHTML = "";
  if (!list.length){
    tb.innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--sub)">Data tidak ditemukan untuk filter saat ini.</td></tr>`;
    return;
  }
  list.forEach((r, idx) => { r.tr.querySelector('td').textContent = idx + 1; tb.appendChild(r.tr); });
}

$("#qSearch").addEventListener("input", e => { state.q = e.target.value.trim().toLowerCase(); apply(); });
$("#fAksi").addEventListener("change", e => { state.aksi = (e.target.value || "").toLowerCase(); apply(); });
$("#from").addEventListener("change", e => { state.from = e.target.value; apply(); });
$("#to").addEventListener("change",   e => { state.to   = e.target.value; apply(); });

$$(".th-sort").forEach(th => {
  th.addEventListener("click", () => {
    const k = th.getAttribute("data-key");
    if(state.sortKey === k){ state.asc = !state.asc; } else { state.sortKey = k; state.asc = (k === 'waktu') ? false : true; }
    $$(".th-sort").forEach(x => x.classList.remove("active"));
    th.classList.add("active");
    th.querySelector(".caret").textContent = state.asc ? "▾" : "▴";
    apply();
  });
});
$(".th-sort[data-key='"+state.sortKey+"']")?.classList.add("active");

// Shortcut: '/' fokus ke search
let lock=false;
window.addEventListener('keydown', (e)=>{
  if(e.key === '/' && !lock){ e.preventDefault(); $("#qSearch").focus(); lock=true; setTimeout(()=>lock=false, 200); }
});

apply();
</script>
