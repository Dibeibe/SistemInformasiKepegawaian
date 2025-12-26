<?php
require_once __DIR__.'/../../config/config.php';
require_once __DIR__.'/../../config/functions.php';
require_login();

if (($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403);
  exit('Read-only: Aksi ini khusus admin.');
}

// ===== Helpers =====
function sanitize_file_name($name) {
    return preg_replace('/[^A-Za-z0-9_\.\-]/', '_', $name);
}
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$flash_err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip            = trim($_POST['nip'] ?? '');
    $nama           = trim($_POST['nama'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $telp           = trim($_POST['telp'] ?? '');
    $alamat         = trim($_POST['alamat'] ?? '');
    $unit_id        = (int)($_POST['unit_id'] ?? 0);
    $jabatan_id     = (int)($_POST['jabatan_id'] ?? 0);
    $tanggal_masuk  = trim($_POST['tanggal_masuk'] ?? '');

    if ($nama === '' || $email === '' || !$unit_id || !$jabatan_id) {
        $flash_err = "Nama, Email, Unit, dan Jabatan wajib diisi.";
    } else {
        // --- Upload foto (opsional)
        $foto = null;
        if (!empty($_FILES['foto']['name']) && is_uploaded_file($_FILES['foto']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];
            if (in_array($ext, $allowed)) {
                $safe = sanitize_file_name(pathinfo($_FILES['foto']['name'], PATHINFO_FILENAME));
                $foto = time() . "_" . $safe . "." . $ext;
                $dest = "../../assets/images/pegawai/" . $foto;
                @mkdir(dirname($dest), 0777, true);
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $dest)) {
                    $flash_err = "Gagal menyimpan file foto.";
                }
            } else {
                $flash_err = "Format foto tidak valid (gunakan JPG/PNG/WebP).";
            }
        }

        if (!$flash_err) {
            // ======================
            // 1) Buat akun user baru
            // ======================
            $username = strtolower(str_replace(' ', '', $nama)); // username dari nama
            $password = strtolower($username) . "123";           // password default
            $role     = 'pegawai';

            $stmtUser = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            if (!$stmtUser) {
                $flash_err = "Gagal prepare user: " . $conn->error;
            } else {
                $stmtUser->bind_param("sss", $username, $password, $role);
                if ($stmtUser->execute()) {
                    $user_id_new = $stmtUser->insert_id; // ambil id user yang baru
                } else {
                    $flash_err = "Gagal menyimpan user: " . $stmtUser->error;
                }
                $stmtUser->close();
            }

            // ======================
            // 2) Simpan ke tabel pegawai
            // ======================
            if (!$flash_err) {
                $sql = "INSERT INTO pegawai 
                        (user_id, nip, nama, jabatan_id, unit_id, alamat, telp, email, foto, tanggal_masuk)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    $flash_err = "Gagal prepare pegawai: " . $conn->error;
                } else {
                    $stmt->bind_param(
                        "issiisssss",
                        $user_id_new,
                        $nip,
                        $nama,
                        $jabatan_id,
                        $unit_id,
                        $alamat,
                        $telp,
                        $email,
                        $foto,
                        $tanggal_masuk
                    );
                    if (!$stmt->execute()) {
                        $flash_err = "Gagal menyimpan data pegawai: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }

            if (!$flash_err) {
                redirect("list.php");
            }
        }
    }
}

// Ambil data master
$units    = $conn->query("SELECT id AS unit_id, nama_unit FROM unit ORDER BY nama_unit ASC");
$jabatans = $conn->query("SELECT id AS jabatan_id, nama_jabatan FROM jabatan ORDER BY nama_jabatan ASC");

$page_title = "Tambah Pegawai";
include "../../includes/header.php";
?>

<style>
/* ============================= */
/* === TAMBAH PEGAWAI (LOGIN DNA) === */
/* ============================= */
:root{
  --grad-a:#667eea;
  --grad-b:#764ba2;

  --glass: rgba(255,255,255,0.15);
  --stroke: rgba(255,255,255,0.22);

  --text:#ffffff;
  --text-soft: rgba(255,255,255,0.88);

  --btn:#ffd369;
  --btn-hover:#ffbf00;

  --shadow-soft: 0 12px 26px rgba(0,0,0,0.18);
  --radius:16px;
}

#sidebar,.sidebar,.left-sidebar,.app-sidebar{display:none!important;}
body{
  padding-left:0!important;
  background: linear-gradient(135deg, var(--grad-a), var(--grad-b)) !important;
  color: var(--text);
}

/* Wrapper */
.page{
  max-width: 1100px;
  margin: 90px auto 40px;
  padding: 18px;
  animation: fadeUp .6s ease;
}
.pg-title{
  margin:0 0 6px;
  font-weight: 900;
  font-size: 24px;
  letter-spacing: .02em;
}
.pg-sub{
  margin:0 0 16px;
  color: var(--text-soft);
  font-size: 13px;
  line-height: 1.5;
}

/* Actionbar */
.actionbar{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  align-items:center;

  background: var(--glass);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  backdrop-filter: blur(12px);
  box-shadow: var(--shadow-soft);
  padding: 14px;
  position: sticky;
  top: 72px;
  z-index: 10;
}
.pill{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:10px 14px;
  border-radius:999px;
  border:1px solid rgba(255,255,255,0.35);
  background: transparent;
  color: var(--text);
  font-size: 14px;
  font-weight: 800;
  text-decoration:none;
  transition:.25s ease;
}
.pill:hover{
  background: rgba(255,255,255,0.16);
  transform: translateY(-2px);
}
.pill.primary{
  border:none;
  background: var(--btn);
  color:#333;
}
.pill.primary:hover{ background: var(--btn-hover); }

/* Card */
.card{
  background: var(--glass);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  backdrop-filter: blur(12px);
  box-shadow: var(--shadow-soft);
  padding: 18px;
  margin-top: 14px;
}

/* Alert */
.alert-err{
  background: rgba(255,255,255,0.14);
  border: 1px solid rgba(239,68,68,0.45);
  border-radius: 14px;
  padding: 12px 14px;
  margin-top: 12px;
  color: #fff;
}

/* Form */
.form-grid{
  display:grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
.form-grid .full{ grid-column: 1 / -1; }

.label{
  display:block;
  font-size: 13px;
  color: var(--text-soft);
  margin-bottom: 6px;
}

.input{
  width:100%;
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.35);
  background: rgba(255,255,255,0.14);
  color: var(--text);
  outline: none;
  transition: .2s ease;
}
.input::placeholder{ color: rgba(255,255,255,0.70); }
.input:focus{
  box-shadow: 0 0 0 3px rgba(255,211,105,0.35);
  border-color: rgba(255,255,255,0.55);
}
textarea.input{ min-height: 92px; resize: vertical; }

.drop{
  display:flex;
  align-items:center;
  gap: 14px;
  padding: 12px;
  border: 1px dashed rgba(255,255,255,0.35);
  border-radius: 12px;
  background: rgba(255,255,255,0.10);
}

.preview{
  width:64px;height:64px;
  border-radius: 14px;
  object-fit: cover;
  border:1px solid rgba(255,255,255,0.25);
  background: rgba(255,255,255,0.18);
}

.actions{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  margin-top: 14px;
}

/* Responsive */
@media(max-width: 900px){
  .form-grid{ grid-template-columns: 1fr; }
}

/* Anim */
@keyframes fadeUp{
  from{opacity:0; transform: translateY(14px);}
  to{opacity:1; transform: translateY(0);}
}
</style>

<div class="page">
  <h2 class="pg-title">Tambah Pegawai</h2>
  <p class="pg-sub">Lengkapi data di bawah ini untuk menambah pegawai baru.</p>

  <div class="actionbar">
    <a class="pill" href="list.php">← Kembali ke Daftar Pegawai</a>
    <a class="pill" href="../dashboard_admin.php">🏠 Dashboard</a>
  </div>

  <?php if ($flash_err): ?>
    <div class="alert-err"><?= e($flash_err) ?></div>
  <?php endif; ?>

  <div class="card">
    <form method="POST" enctype="multipart/form-data" id="frmPegawai" novalidate>
      <div class="form-grid">
        <div>
          <label class="label">NIP (opsional)</label>
          <input class="input" name="nip" type="text" placeholder="Contoh: 1987xxxx">
        </div>

        <div>
          <label class="label">Nama *</label>
          <input class="input" name="nama" required placeholder="Nama lengkap">
        </div>

        <div>
          <label class="label">Email *</label>
          <input class="input" name="email" type="email" required placeholder="nama@email.com">
        </div>

        <div>
          <label class="label">Telepon</label>
          <input class="input" name="telp" type="text" placeholder="08xxxx">
        </div>

        <div class="full">
          <label class="label">Alamat</label>
          <textarea class="input" name="alamat" placeholder="Alamat lengkap (opsional)"></textarea>
        </div>

        <div>
          <label class="label">Unit *</label>
          <select name="unit_id" class="input" required>
            <option value="">— Pilih Unit —</option>
            <?php while($u = $units->fetch_assoc()): ?>
              <option value="<?= (int)$u['unit_id']; ?>"><?= e($u['nama_unit']); ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div>
          <label class="label">Jabatan *</label>
          <select name="jabatan_id" class="input" required>
            <option value="">— Pilih Jabatan —</option>
            <?php while($j = $jabatans->fetch_assoc()): ?>
              <option value="<?= (int)$j['jabatan_id']; ?>"><?= e($j['nama_jabatan']); ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div>
          <label class="label">Tanggal Masuk</label>
          <input class="input" type="date" name="tanggal_masuk">
        </div>

        <div class="full">
          <label class="label">Foto (JPG/PNG/WebP)</label>
          <div class="drop">
            <img id="imgPrev" class="preview" src="" alt="" style="display:none">
            <input class="input" type="file" name="foto" id="foto" accept="image/*">
          </div>
        </div>
      </div>

      <div class="actions">
        <button type="submit" class="pill primary">💾 Simpan</button>
        <a href="list.php" class="pill">Batal</a>
      </div>
    </form>
  </div>
</div>

<?php include "../../includes/footer.php"; ?>

<script>
const foto = document.getElementById('foto');
const imgPrev = document.getElementById('imgPrev');

if (foto) {
  foto.addEventListener('change', e => {
    const f = e.target.files?.[0];
    if (!f) { imgPrev.style.display='none'; return; }

    const ok = ['image/jpeg','image/png','image/webp'].includes(f.type);
    if (!ok) {
      alert('Format foto harus JPG/PNG/WebP');
      e.target.value='';
      imgPrev.style.display='none';
      return;
    }

    imgPrev.src = URL.createObjectURL(f);
    imgPrev.style.display = 'block';
  });
}
</script>
