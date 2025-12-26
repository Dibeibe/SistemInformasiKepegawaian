<?php
require_once __DIR__.'/../../config/config.php';
require_once __DIR__.'/../../config/functions.php';

require_login();
if (($_SESSION['role'] ?? '') !== 'pegawai') { redirect('../dashboard_admin.php'); }

/* Ambil pegawai_id dari session */
$pegawai_id = (int)($_SESSION['pegawai_id'] ?? ($_SESSION['user_id'] ?? 0));
if (!$pegawai_id) die('User tidak valid.');

/* Flash helper */
function flash($key){
  if(!empty($_SESSION[$key])){ $m = $_SESSION[$key]; unset($_SESSION[$key]); return $m; }
  return null;
}

/* Submit */
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $mulai   = trim($_POST['tanggal_mulai'] ?? '');
    $selesai = trim($_POST['tanggal_selesai'] ?? '');
    $alasan  = trim($_POST['alasan'] ?? '');

    if ($mulai === '' || $selesai === '' || $alasan === '') {
        $_SESSION['flash_err'] = "Mohon lengkapi semua kolom.";
        redirect("pengajuan.php");
    }
    if ($selesai < $mulai) {
        $_SESSION['flash_err'] = "Tanggal selesai tidak boleh sebelum tanggal mulai.";
        redirect("pengajuan.php");
    }
    if (mb_strlen($alasan) > 1000) {
        $_SESSION['flash_err'] = "Alasan terlalu panjang (maks 1000 karakter).";
        redirect("pengajuan.php");
    }

    $stmt = $conn->prepare("INSERT INTO cuti (pegawai_id, tanggal_mulai, tanggal_selesai, alasan, status) VALUES (?,?,?,?, 'menunggu')");
    if(!$stmt){
        $_SESSION['flash_err'] = "Gagal menyiapkan query.";
        redirect("pengajuan.php");
    }
    $stmt->bind_param("isss", $pegawai_id, $mulai, $selesai, $alasan);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        $_SESSION['flash_ok'] = "Pengajuan cuti terkirim, status: menunggu persetujuan.";
        redirect("riwayat.php");
    } else {
        $_SESSION['flash_err'] = "Pengajuan gagal dikirim. Coba lagi.";
        redirect("pengajuan.php");
    }
}

$page_title = "Pengajuan Cuti";
include __DIR__.'/../../includes/header.php';   // <head> global + style base
include __DIR__.'/../../includes/sidebar.php';  // sidebar global (tetap terkoneksi)
include __DIR__.'/../../includes/navbar.php';   // navbar global
?>

<style>
/* ================= THEME TOKENS (hunter + bangladesh + white) ================= */
:root{
  --pg-hunter:#355E3B;
  --pg-hunter-600:#2A462F;
  --pg-bangla:#006A4E;
  --pg-bangla-700:#03523F;

  --pg-bg:#F5FAF5;
  --pg-surface:#FFFFFF;
  --pg-text:#1F2A1E;
  --pg-sub:#5E6F5A;
  --pg-muted:#E5EFE8;
  --pg-ring:rgba(0,106,78,.28);
  --pg-shadow:0 10px 28px rgba(0,0,0,.08);
  --pg-radius:14px;
}

/* Biarkan sidebar global jalan. Kita hanya rapikan halaman konten. */
html,body{background:var(--pg-bg); color:var(--pg-text);}

/* ================= PAGE LAYOUT ================= */
.pg-page{
  max-width:960px;
  margin:92px auto 44px;   /* beri jarak dari navbar global */
  padding:0 20px;
  animation:pg-fadeUp .45s ease-out both;
}
@keyframes pg-fadeUp{from{opacity:0; transform:translateY(8px)}to{opacity:1; transform:translateY(0)}}

.pg-head{margin-bottom:16px}
.pg-title{
  margin:0 0 6px; font-weight:800; letter-spacing:.2px;
  color:var(--pg-hunter-600); font-size:26px;
}
.pg-sub{margin:0; color:var(--pg-sub)}

/* dekor garis tipis bawah navbar biar napas */
.pg-sep{
  height:1px; background:linear-gradient(90deg, transparent, var(--pg-muted), transparent);
  margin:0 0 18px;
}

/* ================= CARD & FORM ================= */
.pg-card{
  background:var(--pg-surface);
  border:1px solid var(--pg-muted);
  border-radius:var(--pg-radius);
  box-shadow:var(--pg-shadow);
  padding:22px;
  transition:box-shadow .2s, transform .2s;
}
.pg-card:hover{box-shadow:0 14px 36px rgba(0,0,0,.10); transform:translateY(-1px)}

/* Grid responsif */
.pg-grid{
  display:grid; gap:16px; grid-template-columns:1fr 1fr;
}
.pg-grid .pg-full{grid-column:1 / -1}
@media (max-width:720px){ .pg-grid{grid-template-columns:1fr} }

/* Input/field */
.pg-label{font-weight:600; color:var(--pg-text); margin-bottom:6px; display:block}
.pg-input, .pg-textarea{
  width:100%;
  border:1px solid var(--pg-muted);
  border-radius:12px;
  padding:11px 12px;
  outline:none; background:var(--pg-surface);
  transition:border-color .15s, box-shadow .15s;
  font-size:14px;
}
.pg-input:focus, .pg-textarea:focus{
  border-color:var(--pg-bangla);
  box-shadow:0 0 0 4px var(--pg-ring);
}
.pg-textarea{min-height:120px; resize:vertical}
.pg-hint{color:var(--pg-sub); font-size:12px}

/* Alerts */
.pg-alert{
  padding:12px 14px; border-radius:12px; margin:12px 0 18px; font-size:14px;
  border:1px solid transparent;
}
.pg-alert.ok{background:#ecfdf5; border-color:#A7F3D0; color:#065F46}
.pg-alert.err{background:#fef2f2; border-color:#FECACA; color:#991B1B}

/* Buttons */
.pg-btn{
  appearance:none; border:none; cursor:pointer; text-decoration:none;
  display:inline-flex; align-items:center; justify-content:center; gap:8px;
  padding:11px 16px; border-radius:12px; font-weight:600; font-size:14px;
  transition:transform .1s ease, box-shadow .2s ease, background .2s ease;
}
.pg-btn:active{transform:translateY(1px)}
.pg-btn.primary{
  color:#fff; background:linear-gradient(180deg, var(--pg-bangla), var(--pg-hunter));
  box-shadow:0 6px 18px rgba(0,106,78,.25);
}
.pg-btn.primary:hover{background:linear-gradient(180deg, var(--pg-bangla-700), var(--pg-hunter-600))}
.pg-btn.secondary{
  color:var(--pg-text); border:1px solid var(--pg-muted); background:var(--pg-surface);
}
.pg-actions{display:flex; gap:10px; flex-wrap:wrap; margin-top:4px}

/* Sticky submit (mobile) */
@media (max-width:640px){
  .pg-sticky-submit{position:sticky; bottom:10px; z-index:5; background:transparent; padding-top:6px}
  .pg-sticky-submit .pg-btn.primary{width:100%; border-radius:14px}
}

/* Micro-animation untuk tombol submit saat loading */
.pg-loading{opacity:.85; pointer-events:none}
</style>

<div class="pg-page">
  <div class="pg-head">
    <h3 class="pg-title">Ajukan Cuti</h3>
    <p class="pg-sub">Isi formulir berikut untuk mengajukan cuti. Pastikan tanggal valid dan alasan jelas.</p>
  </div>

  <div class="pg-sep"></div>

  <?php if ($m = flash('flash_ok')): ?>
    <div class="pg-alert ok"><?= htmlspecialchars($m) ?></div>
  <?php endif; ?>
  <?php if ($m = flash('flash_err')): ?>
    <div class="pg-alert err"><?= htmlspecialchars($m) ?></div>
  <?php endif; ?>

  <div class="pg-card">
    <form method="post" id="formCuti" novalidate>
      <div class="pg-grid">
        <div>
          <label class="pg-label" for="tglMulai">Tanggal Mulai</label>
          <input class="pg-input" type="date" name="tanggal_mulai" id="tglMulai" required>
        </div>
        <div>
          <label class="pg-label" for="tglSelesai">Tanggal Selesai</label>
          <input class="pg-input" type="date" name="tanggal_selesai" id="tglSelesai" required>
        </div>

        <div class="pg-full">
          <label class="pg-label" for="alasan">Alasan</label>
          <textarea class="pg-textarea" name="alasan" id="alasan" maxlength="1000" placeholder="Tuliskan alasan pengajuan cuti..." required></textarea>
          <div class="pg-hint"><span id="pgCount">0</span>/1000 karakter</div>
        </div>
      </div>

      <div class="pg-actions pg-sticky-submit">
        <button type="submit" class="pg-btn primary" id="btnSubmit">Kirim Pengajuan</button>
        <a href="riwayat.php" class="pg-btn secondary">Lihat Riwayat</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__.'/../../includes/footer.php'; ?>

<script>
// ------ Min date = hari ini ------
(function setMin(){
  const today = new Date();
  const yyyy = today.getFullYear();
  const mm = String(today.getMonth()+1).padStart(2,'0');
  const dd = String(today.getDate()).padStart(2,'0');
  const iso = `${yyyy}-${mm}-${dd}`;
  document.getElementById('tglMulai').min = iso;
  document.getElementById('tglSelesai').min = iso;
})();

// ------ Character counter ------
const alasan = document.getElementById('alasan');
const pgCount = document.getElementById('pgCount');
function updateCount(){ pgCount.textContent = alasan.value.length; }
alasan.addEventListener('input', updateCount);
updateCount();

// ------ Validasi ringan + highlight invalid ------
const mulai   = document.getElementById('tglMulai');
const selesai = document.getElementById('tglSelesai');
const form    = document.getElementById('formCuti');
const btn     = document.getElementById('btnSubmit');

function markInvalid(el){ el.style.borderColor='#EF4444'; el.style.boxShadow='0 0 0 3px rgba(239,68,68,.25)'; }
function clearInvalid(el){ el.style.borderColor=''; el.style.boxShadow=''; }
[mulai, selesai, alasan].forEach(el => el.addEventListener('input', ()=>clearInvalid(el)));

form.addEventListener('submit', e=>{
  let ok=true;
  if(!mulai.value){ markInvalid(mulai); ok=false; }
  if(!selesai.value){ markInvalid(selesai); ok=false; }
  if(!alasan.value.trim()){ markInvalid(alasan); ok=false; }
  if(ok && selesai.value < mulai.value){ markInvalid(selesai); alert('Tanggal selesai tidak boleh sebelum tanggal mulai.'); ok=false; }

  if(!ok){ e.preventDefault(); return; }

  // micro-interaction submit
  btn.classList.add('pg-loading');
});

// Fokus awal
window.requestAnimationFrame(()=> mulai.focus());
</script>
