<?php
require_once "../../config/config.php";
require_once "../../config/functions.php";

require_login();
if (($_SESSION['role'] ?? '') !== 'admin') {
    redirect('../../index.php');
}

/* ---------- DATA ---------- */
$sql = "SELECT p.id, p.nip, p.nama, p.email, p.telp, 
               COALESCE(u.nama_unit, '-') AS unit, 
               COALESCE(j.nama_jabatan, '-') AS jabatan,
               p.foto
        FROM pegawai p
        LEFT JOIN unit u ON u.id = p.unit_id
        LEFT JOIN jabatan j ON j.id = p.jabatan_id
        ORDER BY p.nama ASC";

$result = $conn->query($sql);
$pegawai = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$result && $result->free();

function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$page_title = "Master Data – Pegawai";
include "../../includes/header.php";
?>

<style>
/* ============================= */
/* === MASTER PEGAWAI (LOGIN DNA) === */
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

  --radius:16px;
}

/* RESET LINK */
a{ text-decoration:none !important; color:inherit; }

/* hide sidebar */
#sidebar,.sidebar,.left-sidebar,.app-sidebar{display:none!important}
body{
  padding-left:0!important;
  background: linear-gradient(135deg, var(--grad-a), var(--grad-b)) !important;
  color:var(--text);
  font-family:'Segoe UI',Arial,sans-serif;
}

/* ===== PAGE ===== */
.page{
  max-width:1180px;
  margin:90px auto 40px;
  padding:18px;
  animation: fadeUp .6s ease;
}
.page h2{
  font-size:24px;
  font-weight:900;
  letter-spacing:.02em;
  margin:0 0 6px;
}
.page p.sub{
  color:var(--text-soft);
  margin:0 0 16px;
  font-size:13px;
  line-height:1.5;
}

/* ===== ACTION BAR ===== */
.actionbar{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
  align-items:center;

  background:var(--glass);
  border:1px solid var(--stroke);
  backdrop-filter: blur(12px);
  box-shadow:var(--shadow-soft);
  border-radius:var(--radius);

  padding:14px 14px;
  position:sticky;
  top:72px;
  z-index:10;
}

.pill{
  display:inline-flex;
  align-items:center;
  gap:8px;

  padding:10px 14px;
  border-radius:999px;
  border:1px solid rgba(255,255,255,0.35);
  background:transparent;
  color:var(--text);
  font-size:14px;
  font-weight:700;
  transition:.25s ease;
}
.pill:hover{
  background: rgba(255,255,255,0.16);
  transform: translateY(-2px);
}
.pill.primary{
  border:none;
  background:var(--btn);
  color:#333;
  font-weight:900;
}
.pill.primary:hover{ background:var(--btn-hover); }

/* search */
.input{
  flex:1;
  min-width:220px;
  padding:10px 12px;
  border-radius:12px;
  border:1px solid rgba(255,255,255,0.35);
  background: rgba(255,255,255,0.14);
  color: var(--text);
  outline:none;
  transition:.2s ease;
}
.input::placeholder{ color: rgba(255,255,255,0.70); }
.input:focus{
  box-shadow:0 0 0 3px rgba(255,211,105,.35);
  border-color: rgba(255,255,255,0.55);
}

/* ===== CARD & TABLE ===== */
.card{
  background:var(--glass);
  border:1px solid var(--stroke);
  backdrop-filter: blur(12px);
  box-shadow:var(--shadow-soft);
  border-radius:var(--radius);
  padding:18px;
  margin-top:14px;
}

.table-wrap{overflow:auto;border-radius:14px;border:1px solid rgba(255,255,255,0.18)}
.table{
  width:100%;
  border-collapse:collapse;
  min-width:980px;
}
.table th,.table td{
  padding:14px 12px;
  border-bottom:1px solid rgba(255,255,255,0.16);
  font-size:14px;
}
.table th{
  background: rgba(255,255,255,0.10);
  color: var(--text);
  font-weight:900;
  white-space: nowrap;
}
.table tbody tr:nth-child(odd){ background: rgba(255,255,255,0.06); }
.table tbody tr:hover{ background: rgba(255,255,255,0.10); }

.table img.avatar{
  width:44px;height:44px;border-radius:14px;
  object-fit:cover;
  border:1px solid rgba(255,255,255,0.25);
  background: rgba(255,255,255,0.20);
}

.table td:last-child, .table th:last-child{ text-align:right; white-space: nowrap; }

/* ===== BUTTONS (AKSI) ===== */
.btn-sm{
  display:inline-flex;
  align-items:center;
  gap:6px;

  padding:8px 12px;
  border-radius:999px;
  font-size:13px;
  font-weight:800;

  border:1px solid rgba(255,255,255,0.35);
  background:transparent;
  color:var(--text);
  transition:.2s ease;
}
.btn-sm:hover{
  background: rgba(255,255,255,0.16);
  transform: translateY(-2px);
}

.btn-edit{
  background: rgba(255,255,255,0.78);
  border-color: rgba(0,0,0,0.55);
  color:#0b0f19;
}
.btn-edit:hover{
  background: rgba(255,255,255,0.92);
  border-color: rgba(0,0,0,0.75);
}

.btn-del{
  background: rgba(255,255,255,0.78);
  border-color: rgba(0,0,0,0.55);
  color:#0b0f19;
}
.btn-del:hover{
  background: rgba(255,255,255,0.92);
  border-color: rgba(0,0,0,0.75);
}

/* ===== ALERT ===== */
.alert{
  padding:12px 14px;
  border-radius:14px;
  margin-top:12px;
  font-size:14px;

  background: rgba(255,255,255,0.14);
  border: 1px solid rgba(255,255,255,0.22);
  backdrop-filter: blur(12px);
  box-shadow: var(--shadow-soft);
}
.alert.ok{ border-color: rgba(16,185,129,0.40); }
.alert.err{ border-color: rgba(239,68,68,0.40); }

/* ===== EMPTY ===== */
.empty{
  border:1px dashed rgba(255,255,255,0.35);
  border-radius:16px;
  padding:28px;
  text-align:center;
  background: rgba(255,255,255,0.08);
  color: var(--text-soft);
}
.empty .title{
  font-weight:900;
  color:#fff;
  margin-bottom:6px;
}

/* Responsive */
@media(max-width: 980px){
  .page{ margin-top: 80px; }
}

/* Animation */
@keyframes fadeUp{
  from{opacity:0; transform: translateY(14px);}
  to{opacity:1; transform: translateY(0);}
}
</style>

<div class="page">
  <h2>Master Data – Pegawai</h2>
  <p class="sub">Kelola data pegawai dengan cepat dan mudah.</p>

  <div class="actionbar">
    <a class="pill" href="../dashboard_admin.php">← Kembali</a>
    <a class="pill primary" href="add.php">+ Tambah Pegawai</a>
    <input id="qSearch" class="input" type="search" placeholder="Cari nama... (sorting pakai klik Nama)">
  </div>

  <?php if (!empty($_SESSION['flash_ok'])): ?>
    <div class="alert ok"><?= e($_SESSION['flash_ok']); unset($_SESSION['flash_ok']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert err"><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="table-wrap">
      <table class="table" id="tblPegawai">
        <thead>
          <tr>
            <th style="width:70px;">No</th>
            <th style="width:90px;">Foto</th>
            <th id="thNama" style="cursor:pointer">Nama ▾</th>
            <th>Email</th>
            <th>Telepon</th>
            <th>Unit</th>
            <th>Jabatan</th>
            <th style="width:220px;">Aksi</th>
          </tr>
        </thead>
        <tbody id="tbBody">
          <?php if (count($pegawai)): $no=1; foreach ($pegawai as $p): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td>
              <img class="avatar"
                   src="../../assets/images/pegawai/<?= e($p['foto'] ?: 'noimage.png') ?>"
                   alt="foto">
            </td>
            <td class="nama"><?= e($p['nama']) ?></td>
            <td><?= e($p['email']) ?></td>
            <td><?= e($p['telp']) ?></td>
            <td><?= e($p['unit']) ?></td>
            <td><?= e($p['jabatan']) ?></td>
            <td>
              <a class="btn-sm btn-edit" href="edit.php?id=<?= (int)$p['id'] ?>">✏️ Edit</a>
              <a class="btn-sm btn-del" href="delete.php?id=<?= (int)$p['id'] ?>"
                 onclick="return confirm('Yakin hapus data ini?')">🗑️ Hapus</a>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="8" style="padding:26px;">
              <div class="empty">
                <div class="title">Belum ada data pegawai</div>
                <div style="margin-bottom:14px;">Mulai dengan menambahkan pegawai baru.</div>
                <a class="pill primary" href="add.php">+ Tambah Pegawai</a>
              </div>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include "../../includes/footer.php"; ?>

<script>
const $ = (q,c=document)=>c.querySelector(q);
const $$ = (q,c=document)=>Array.from(c.querySelectorAll(q));

const rows = $$('#tbBody tr').filter(tr=>!tr.querySelector('.empty')).map(tr=>({
  tr,
  name:(tr.querySelector('.nama')?.textContent||'').toLowerCase()
}));

let asc = true;

function render(list){
  const tbody = $('#tbBody');
  tbody.innerHTML = '';
  if(!list.length){
    tbody.innerHTML = `<tr><td colspan="8" style="padding:26px;">
      <div class="empty"><div class="title">Data tidak ditemukan</div></div>
    </td></tr>`;
    return;
  }
  list.forEach((r,i)=>{
    r.tr.querySelector('td').textContent = i+1;
    tbody.appendChild(r.tr);
  });
}

$('#qSearch')?.addEventListener('input', e=>{
  const q = e.target.value.trim().toLowerCase();
  render(rows.filter(r => !q || r.name.includes(q)));
});

$('#thNama')?.addEventListener('click', ()=>{
  asc = !asc;
  $('#thNama').textContent = `Nama ${asc ? '▾' : '▴'}`;
  render([...rows].sort((a,b)=> asc ? a.name.localeCompare(b.name) : b.name.localeCompare(a.name)));
});
</script>
