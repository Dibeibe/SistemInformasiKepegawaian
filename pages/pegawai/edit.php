<?php
require_once "../../config/config.php";
require_once "../../config/functions.php";

require_login();
if (($_SESSION['role'] ?? '') !== 'admin') { redirect('../../index.php'); }

const FOTO_DIR = __DIR__ . "/../../assets/images/pegawai/";
const FOTO_URL = "../../assets/images/pegawai/";
const DEFAULT_FOTO = "noimage.png";

/* ---------- Ambil ID & Data Pegawai ---------- */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  $_SESSION['flash_error'] = "ID pegawai tidak valid.";
  redirect("list.php");
}

$stmt = $conn->prepare("SELECT id, nip, nama, email, telp, unit_id, jabatan_id, foto, alamat, tanggal_masuk FROM pegawai WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$peg = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$peg) {
  $_SESSION['flash_error'] = "Data pegawai tidak ditemukan.";
  redirect("list.php");
}

/* ---------- Data referensi Unit & Jabatan ---------- */
$units = [];
$jabs  = [];

$res = $conn->query("SELECT id, nama_unit FROM unit ORDER BY nama_unit ASC");
if ($res) { $units = $res->fetch_all(MYSQLI_ASSOC); $res->free(); }

$res = $conn->query("SELECT id, nama_jabatan FROM jabatan ORDER BY nama_jabatan ASC");
if ($res) { $jabs = $res->fetch_all(MYSQLI_ASSOC); $res->free(); }

/* ---------- Helper upload foto ---------- */
function handle_upload_foto(?string $old): array {
  if (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
    return [true, $old]; // tidak ganti foto
  }

  $f = $_FILES['foto'];
  if ($f['error'] !== UPLOAD_ERR_OK) {
    return [false, "Upload foto gagal (kode {$f['error']})."];
  }

  // Validasi ukuran (max ~3MB)
  if ($f['size'] > 3 * 1024 * 1024) {
    return [false, "Ukuran foto maksimal 3 MB."];
  }

  // Validasi ekstensi
  $extAllowed = ['jpg','jpeg','png','webp'];
  $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $extAllowed, true)) {
    return [false, "Ekstensi foto harus jpg, jpeg, png, atau webp."];
  }

  // Generate nama file unik
  $newName = "pg_" . bin2hex(random_bytes(6)) . "." . $ext;
  if (!is_dir(FOTO_DIR)) { @mkdir(FOTO_DIR, 0777, true); }
  $dest = FOTO_DIR . $newName;

  if (!move_uploaded_file($f['tmp_name'], $dest)) {
    return [false, "Gagal menyimpan foto ke server."];
  }

  // Hapus foto lama jika bukan default
  if (!empty($old) && $old !== DEFAULT_FOTO) {
    $oldPath = FOTO_DIR . $old;
    if (is_file($oldPath)) @unlink($oldPath);
  }

  return [true, $newName];
}

/* ---------- Submit Update ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
  $nip     = trim($_POST['nip'] ?? '');
  $nama    = trim($_POST['nama'] ?? '');
  $email   = trim($_POST['email'] ?? '');
  $telp    = trim($_POST['telp'] ?? '');
  $unit_id = (int)($_POST['unit_id'] ?? 0);
  $jab_id  = (int)($_POST['jabatan_id'] ?? 0);
  $alamat  = trim($_POST['alamat'] ?? '');
  $tgl     = trim($_POST['tanggal_masuk'] ?? '');
  $hapus_foto = isset($_POST['hapus_foto']) && $_POST['hapus_foto'] === '1';

  if ($nama === '') {
    $_SESSION['flash_error'] = "Nama wajib diisi.";
    redirect("edit.php?id=".$id);
  }
  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash_error'] = "Format email tidak valid.";
    redirect("edit.php?id=".$id);
  }

  $fotoNow = $peg['foto'] ?: DEFAULT_FOTO;
  if ($hapus_foto) {
    if ($fotoNow !== DEFAULT_FOTO && is_file(FOTO_DIR . $fotoNow)) @unlink(FOTO_DIR . $fotoNow);
    $fotoNow = DEFAULT_FOTO;
  } else {
    [$okUpload, $fotoResult] = handle_upload_foto($fotoNow);
    if (!$okUpload) {
      $_SESSION['flash_error'] = $fotoResult;
      redirect("edit.php?id=".$id);
    }
    $fotoNow = $fotoResult;
  }

  $stmt = $conn->prepare("UPDATE pegawai 
                          SET nip=?, nama=?, email=?, telp=?, unit_id=?, jabatan_id=?, alamat=?, tanggal_masuk=?, foto=?
                          WHERE id=?");
  $stmt->bind_param("ssssiisssi", $nip, $nama, $email, $telp, $unit_id, $jab_id, $alamat, $tgl, $fotoNow, $id);

  if ($stmt->execute()) {
    $_SESSION['flash_ok'] = "Data pegawai berhasil diperbarui.";
  } else {
    $_SESSION['flash_error'] = "Gagal memperbarui data: ".$conn->error;
  }
  $stmt->close();
  redirect("list.php");
}

$page_title = "Edit Pegawai";
include "../../includes/header.php";
?>

<style>
/* ===== TEMA UNGU GLASS (sama kayak master unit/jabatan) ===== */
:root{
  --grad-a:#667eea;
  --grad-b:#764ba2;

  --glass: rgba(255,255,255,0.15);
  --glass-2: rgba(255,255,255,0.12);
  --stroke: rgba(255,255,255,0.22);

  --text:#ffffff;
  --text-soft: rgba(255,255,255,0.88);
  --muted: rgba(255,255,255,0.70);

  --btn:#ffd369;
  --btn-hover:#ffbf00;

  --shadow: 0 15px 30px rgba(0,0,0,0.30);
  --shadow-soft: 0 12px 26px rgba(0,0,0,0.18);

  --radius:16px;
  --radius-sm:12px;

  --ink: #0b0f19;
}

*{ box-sizing:border-box; }

/* Matikan sidebar lokal lama */
#sidebar, .sidebar, .left-sidebar, .app-sidebar {display:none!important;}
body {padding-left:0!important;}

/* Background */
body{
  background: linear-gradient(135deg, var(--grad-a), var(--grad-b)) !important;
  color: var(--text);
}

/* Wrapper */
.page{
  max-width: 1050px;
  margin: 90px auto 40px;
  padding: 18px;
  animation: fadeUp .6s ease;
}
.page h2{
  margin:0;
  font-size: 24px;
  font-weight: 900;
  letter-spacing: .02em;
}
.page p.sub{
  margin: 6px 0 0;
  color: var(--text-soft);
  font-size: 13px;
  line-height: 1.5;
}

/* Actionbar */
.actionbar{
  margin-top: 14px;
  display:flex;
  flex-wrap:wrap;
  align-items:center;
  gap: 10px;
  padding: 14px;
  border-radius: var(--radius);
  background: var(--glass);
  border: 1px solid var(--stroke);
  backdrop-filter: blur(12px);
  box-shadow: var(--shadow-soft);
  position: sticky;
  top: 72px;
  z-index: 10;
}

.pill{
  display:inline-flex;
  align-items:center;
  gap: 8px;
  border-radius: 999px;
  padding: 10px 14px;
  text-decoration:none;
  font-size: 14px;
  border: 1px solid rgba(255,255,255,0.35);
  background: transparent;
  color: var(--text);
  transition: .25s ease;
  cursor:pointer;
}
.pill:hover{
  background: rgba(255,255,255,0.16);
  transform: translateY(-2px);
}
.pill.primary{
  border:none;
  background: var(--btn);
  color:#333;
  font-weight: 800;
}
.pill.primary:hover{ background: var(--btn-hover); }

/* Card */
.card{
  margin-top: 14px;
  padding: 18px;
  border-radius: var(--radius);
  background: var(--glass);
  border: 1px solid var(--stroke);
  backdrop-filter: blur(12px);
  box-shadow: var(--shadow-soft);
}
.card h3{
  margin: 0 0 12px;
  font-size: 16px;
  font-weight: 900;
}

/* Form */
.grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:900px){ .grid{grid-template-columns:1fr} }

.form-row{display:flex;flex-direction:column;gap:6px}
label{
  font-weight: 900;
  font-size: 12px;
  color: rgba(255,255,255,0.85);
  letter-spacing:.03em;
  text-transform: uppercase;
}

.input, .select, textarea{
  width:100%;
  min-width: 220px;
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.35);
  background: rgba(255,255,255,0.14);
  color: var(--text);
  outline: none;
  transition: .2s ease;
}
.input::placeholder{ color: rgba(255,255,255,0.70); }
.input:focus, .select:focus, textarea:focus{
  box-shadow: 0 0 0 3px rgba(255, 211, 105, 0.35);
  border-color: rgba(255,255,255,0.55);
}

.help{color: var(--text-soft); font-size: 12px}

/* Alerts */
.alert{
  margin-top: 12px;
  padding: 12px 14px;
  border-radius: 14px;
  border: 1px solid rgba(255,255,255,0.22);
  background: rgba(255,255,255,0.14);
  backdrop-filter: blur(12px);
  box-shadow: var(--shadow-soft);
  font-size: 14px;
}
.alert.ok{ border-color: rgba(16,185,129,0.40); }
.alert.err{ border-color: rgba(239,68,68,0.40); }

/* Foto */
.avatar{
  width:96px;height:96px;border-radius: 14px;object-fit:cover;
  border: 1px solid rgba(255,255,255,0.22);
  background: rgba(255,255,255,0.10);
}

/* Btn row */
.btns{display:flex;gap:10px;align-items:center;margin-top:14px;flex-wrap:wrap}

/* Animation */
@keyframes fadeUp{
  from{opacity:0; transform: translateY(14px);}
  to{opacity:1; transform: translateY(0);}
}
</style>

<div class="page">
  <h2>Edit Pegawai</h2>
  <p class="sub">Perbarui data pegawai dengan aman. Pastikan data yang diinput sudah benar.</p>

  <div class="actionbar">
    <a class="pill" href="list.php">← Kembali ke Daftar Pegawai</a>
    <a class="pill" href="../dashboard_admin.php">Dashboard</a>
    <a class="pill" href="<?= app_url('logout.php') ?>">Logout</a>
  </div>

  <?php if (!empty($_SESSION['flash_ok'])): ?>
    <div class="alert ok"><?= htmlspecialchars($_SESSION['flash_ok']); unset($_SESSION['flash_ok']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert err"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
  <?php endif; ?>

  <form class="card" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save">

    <div class="grid">
      <div class="form-row">
        <label for="nip">NIP</label>
        <input class="input" id="nip" name="nip" type="text" value="<?= htmlspecialchars($peg['nip'] ?? '') ?>">
      </div>

      <div class="form-row">
        <label for="nama">Nama</label>
        <input class="input" id="nama" name="nama" type="text" required value="<?= htmlspecialchars($peg['nama'] ?? '') ?>">
      </div>

      <div class="form-row">
        <label for="email">Email</label>
        <input class="input" id="email" name="email" type="email" value="<?= htmlspecialchars($peg['email'] ?? '') ?>">
        <div class="help">Kosongkan jika tidak ada email.</div>
      </div>

      <div class="form-row">
        <label for="telp">Telepon</label>
        <input class="input" id="telp" name="telp" type="text" value="<?= htmlspecialchars($peg['telp'] ?? '') ?>">
      </div>

      <div class="form-row">
        <label for="unit_id">Unit</label>
        <select class="select" id="unit_id" name="unit_id" required>
          <option value="">-- Pilih Unit --</option>
          <?php foreach ($units as $u): ?>
            <option value="<?= (int)$u['id'] ?>" <?= ((int)$peg['unit_id']===(int)$u['id']?'selected':'') ?>>
              <?= htmlspecialchars($u['nama_unit']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-row">
        <label for="jabatan_id">Jabatan</label>
        <select class="select" id="jabatan_id" name="jabatan_id" required>
          <option value="">-- Pilih Jabatan --</option>
          <?php foreach ($jabs as $j): ?>
            <option value="<?= (int)$j['id'] ?>" <?= ((int)$peg['jabatan_id']===(int)$j['id']?'selected':'') ?>>
              <?= htmlspecialchars($j['nama_jabatan']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-row">
        <label for="tanggal_masuk">Tanggal Masuk</label>
        <input class="input" id="tanggal_masuk" name="tanggal_masuk" type="date" value="<?= htmlspecialchars($peg['tanggal_masuk'] ?? '') ?>">
      </div>

      <div class="form-row">
        <label for="alamat">Alamat</label>
        <textarea id="alamat" name="alamat" rows="3" class="input"><?= htmlspecialchars($peg['alamat'] ?? '') ?></textarea>
      </div>
    </div>

    <div class="form-row" style="margin-top:16px;">
      <label>Foto</label>
      <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
        <img class="avatar" src="<?= FOTO_URL . htmlspecialchars($peg['foto'] ?: DEFAULT_FOTO) ?>" alt="foto">
        <div style="min-width:240px;max-width:360px;flex:1">
          <input class="input" type="file" name="foto" accept=".jpg,.jpeg,.png,.webp">
          <div class="help">Maks 3MB. jpg/jpeg/png/webp.</div>

          <label style="display:flex;align-items:center;gap:8px;margin-top:8px; font-weight:800; text-transform:none; letter-spacing:0;">
            <input type="checkbox" name="hapus_foto" value="1">
            Hapus foto (pakai default)
          </label>
        </div>
      </div>
    </div>

    <div class="btns">
      <button type="submit" class="pill primary">Simpan Perubahan</button>
      <a class="pill" href="list.php">Batal</a>
    </div>
  </form>
</div>

<?php include "../../includes/footer.php"; ?>
