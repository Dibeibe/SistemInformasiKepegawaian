<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';

// === Login + Admin Only ===
require_login();
if (($_SESSION['role'] ?? '') !== 'admin') { redirect('../../index.php'); }

$page_title = "Input Gaji Pegawai";

/* --------- Ambil data pegawai (untuk dropdown) --------- */
$pegawai = $conn->query("SELECT id, nama FROM pegawai ORDER BY nama ASC");

/* --------- Edit mode (optional) --------- */
$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editData = null;
if ($editId > 0) {
    $st = $conn->prepare("SELECT id, pegawai_id, bulan, tahun, gaji_pokok, potongan, total FROM gaji WHERE id=?");
    $st->bind_param("i", $editId);
    $st->execute();
    $editData = $st->get_result()->fetch_assoc() ?: null;
    $st->close();
    if ($editData) { $page_title = "Edit Gaji Pegawai"; }
}

/* --------- Flash --------- */
$flash_err = null;
$flash_ok  = null;

/* --------- Submit --------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $editId      = (int)($_POST['id'] ?? 0);
    $pegawai_id  = (int)($_POST['pegawai_id'] ?? 0);
    $bulan       = (int)($_POST['bulan'] ?? 0);
    $tahun       = (int)($_POST['tahun'] ?? 0);
    $gaji_pokok  = (float)($_POST['gaji_pokok'] ?? 0);
    $potongan    = (float)($_POST['potongan'] ?? 0);
    $total       = $gaji_pokok - $potongan;
    $tgl_input   = date('Y-m-d');

    if ($pegawai_id && $bulan && $tahun) {
        if ($editId > 0) {
            $stmt = $conn->prepare("UPDATE gaji SET pegawai_id=?, bulan=?, tahun=?, gaji_pokok=?, potongan=?, total=? WHERE id=?");
            // i i i d d d i
            $stmt->bind_param("iiidddi", $pegawai_id, $bulan, $tahun, $gaji_pokok, $potongan, $total, $editId);
            if ($stmt->execute()) {
                $_SESSION['flash_ok'] = "Data gaji #$editId berhasil diperbarui.";
                $stmt->close();
                redirect("list.php");
            } else {
                $flash_err = "Gagal memperbarui data: " . $conn->error;
                $stmt->close();
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO gaji (pegawai_id, bulan, tahun, gaji_pokok, potongan, total, tanggal_input)
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
            // i i i d d d s
            $stmt->bind_param("iiiddds", $pegawai_id, $bulan, $tahun, $gaji_pokok, $potongan, $total, $tgl_input);
            if ($stmt->execute()) {
                $flash_ok = "Data gaji berhasil disimpan.";
            } else {
                $flash_err = "Gagal menyimpan data: " . $conn->error;
            }
            $stmt->close();
        }
    } else {
        $flash_err = "Pegawai, Bulan, dan Tahun wajib diisi.";
    }
}

/* --------- Header global (navbar & footer sudah dari sini) --------- */
include __DIR__ . '/../../includes/header.php';
?>

<style>
/* Matikan sidebar layout lama jika ada */
#sidebar, .sidebar, .left-sidebar, .app-sidebar, .sidenav{display:none!important;width:0!important}
.content, #content, .main, .main-content, .container-page{margin-left:0!important}
body{padding-left:0!important}

/* Theme konsisten (Hunter Green) */
:root{
  --hunter:#355E3B; --hunter-dark:#2A462F; --accent:#6FB18A;
  --bg:#F5FAF5; --card:#FFFFFF; --text:#1F2937; --sub:#6B7280;
  --muted:#E5E7EB; --ring:rgba(53,94,59,.25);
  --shadow:0 6px 20px rgba(0,0,0,.08); --radius:14px;
}

/* Layout */
.page{max-width:900px;margin:90px auto 40px;padding:24px;color:var(--text)}
.title{margin:0 0 6px;font-size:26px;font-weight:700;color:var(--hunter-dark)}
.sub{margin:0 0 18px;color:var(--sub)}

/* Action bar */
.actionbar{
  display:flex;flex-wrap:wrap;gap:10px;align-items:center;
  background:var(--card);border:1px solid var(--muted);border-radius:var(--radius);
  box-shadow:var(--shadow);padding:12px 16px;position:sticky;top:72px;z-index:10;
}
.pill{
  border:1px solid var(--muted);border-radius:10px;padding:9px 14px;
  background:var(--card);color:var(--text);text-decoration:none;font-size:14px;font-weight:500;transition:.2s;
}
.pill:hover{border-color:var(--accent);box-shadow:0 0 0 4px var(--ring);color:var(--hunter-dark)}
.pill.primary{background:var(--hunter);color:#fff;border:none}
.pill.primary:hover{background:var(--hunter-dark)}

/* Card & form */
.card{background:var(--card);border:1px solid var(--muted);border-radius:var(--radius);box-shadow:var(--shadow);padding:20px;margin-top:16px}
.label{display:block;font-size:13px;color:var(--sub);margin-bottom:6px}
.input{border:1px solid var(--muted);background:var(--card);color:var(--text);padding:10px 12px;border-radius:10px;outline:none;transition:.15s;width:100%}
.input:focus{border-color:var(--ring);box-shadow:0 0 0 4px var(--ring)}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-grid .full{grid-column:1 / -1}
.helper{font-size:12px;color:var(--sub);margin-top:6px}

/* Buttons */
.btn{padding:10px 14px;border:1px solid var(--muted);border-radius:10px;text-decoration:none;background:var(--card);transition:.18s}
.btn:hover{border-color:var(--accent);box-shadow:0 0 0 3px var(--ring)}
.btn.save{background:var(--hunter);color:#fff;border:none}
.btn.save:hover{background:var(--hunter-dark)}

/* Alerts */
.alert{padding:12px 14px;border-radius:10px;margin-top:12px}
.alert.ok{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
.alert.err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}

/* Responsive */
@media(max-width:720px){ .form-grid{grid-template-columns:1fr} }
</style>

<div class="page">
  <h2 class="title"><?= htmlspecialchars($page_title) ?></h2>
  <p class="sub">Isi data gaji dengan lengkap. Total akan dihitung otomatis (Gaji Pokok − Potongan).</p>

  <div class="actionbar">
    <a class="pill" href="list.php">Kembali ke Daftar Gaji</a>
    <a class="pill" href="../dashboard_admin.php">Kembali ke Dashboard</a>
    <?php if($editData): ?>
      <span class="pill" style="cursor:default;opacity:.85">Mode: Edit #<?= (int)$editData['id'] ?></span>
    <?php endif; ?>
  </div>

  <?php if ($flash_ok): ?><div class="alert ok"><?= htmlspecialchars($flash_ok) ?></div><?php endif; ?>
  <?php if ($flash_err): ?><div class="alert err"><?= htmlspecialchars($flash_err) ?></div><?php endif; ?>

  <div class="card">
    <form method="POST" id="frmGaji" novalidate>
      <?php if($editData): ?><input type="hidden" name="id" value="<?= (int)$editData['id'] ?>"><?php endif; ?>
      <div class="form-grid">
        <div>
          <label class="label">Pegawai *</label>
          <select name="pegawai_id" class="input" required>
            <option value="">— Pilih Pegawai —</option>
            <?php while($p = $pegawai->fetch_assoc()): ?>
              <option value="<?= (int)$p['id'] ?>"
                <?= $editData && (int)$editData['pegawai_id'] === (int)$p['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['nama']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div>
          <label class="label">Bulan *</label>
          <select name="bulan" class="input" required>
            <option value="">— Pilih Bulan —</option>
            <?php
              $bulanList = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
              for ($i=1;$i<=12;$i++):
                $sel = ($editData && (int)$editData['bulan']===$i) ? 'selected' : '';
                echo "<option value=\"$i\" $sel>{$bulanList[$i-1]}</option>";
              endfor;
            ?>
          </select>
        </div>

        <div>
          <label class="label">Tahun *</label>
          <input class="input" type="number" name="tahun"
                 value="<?= htmlspecialchars($editData['tahun'] ?? date('Y')) ?>" min="2000" max="<?= date('Y')+5 ?>" required>
        </div>

        <div>
          <label class="label">Gaji Pokok *</label>
          <input class="input" type="number" name="gaji_pokok" placeholder="Masukkan nominal"
                 value="<?= htmlspecialchars($editData['gaji_pokok'] ?? '') ?>" min="0" step="1" required>
          <div class="helper">Masukkan angka tanpa tanda titik/koma.</div>
        </div>

        <div>
          <label class="label">Potongan</label>
          <input class="input" type="number" name="potongan"
                 value="<?= htmlspecialchars($editData['potongan'] ?? '0') ?>" min="0" step="1">
        </div>

        <div class="full">
          <label class="label">Total (otomatis)</label>
          <input class="input" id="totalGaji" type="number" readonly style="background:#f3f4f6"
                 value="<?= htmlspecialchars(($editData['gaji_pokok'] ?? 0) - ($editData['potongan'] ?? 0)) ?>">
        </div>
      </div>

      <div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap">
        <button type="submit" class="btn save"><?= $editData ? 'Perbarui' : 'Simpan' ?></button>
        <a href="list.php" class="btn">Batal</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
// Kalkulasi total otomatis
const gaji = document.querySelector('[name="gaji_pokok"]');
const pot  = document.querySelector('[name="potongan"]');
const total = document.getElementById('totalGaji');
function calcTotal(){
  const gp = parseFloat(gaji.value||0);
  const pt = parseFloat(pot.value||0);
  total.value = (gp - pt).toFixed(0);
}
gaji.addEventListener('input', calcTotal);
pot.addEventListener('input', calcTotal);
calcTotal();

// Validasi sederhana sebelum submit
document.getElementById('frmGaji').addEventListener('submit', e=>{
  const f = e.target;
  if (!f.pegawai_id.value || !f.bulan.value || !f.tahun.value){
    e.preventDefault();
    alert('Pegawai, Bulan, dan Tahun wajib diisi.');
  }
});
</script>
