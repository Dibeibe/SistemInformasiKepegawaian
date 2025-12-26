<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';

require_login();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  redirect(app_url('pages/dashboard_pegawai.php'));
}

$username = $_SESSION['username'] ?? 'admin';

/* =========================
   ADD / EDIT / DELETE
========================= */
$err = '';
$ok  = '';

$editId  = (int)($_GET['edit'] ?? 0);
$adding  = isset($_GET['add']);
$editing = $editId > 0;

$form = ['nama_jabatan' => ''];

if ($editing) {
  $st = mysqli_prepare($conn, "SELECT id, nama_jabatan FROM jabatan WHERE id=? LIMIT 1");
  if (!$st) die("Prepare gagal: " . mysqli_error($conn));

  mysqli_stmt_bind_param($st, "i", $editId);
  mysqli_stmt_execute($st);
  $rs  = mysqli_stmt_get_result($st);
  $row = mysqli_fetch_assoc($rs);
  mysqli_stmt_close($st);

  if (!$row) redirect(app_url('pages/master/jabatan.php'));
  $form['nama_jabatan'] = $row['nama_jabatan'] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // DELETE
  if (isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    if ($delId > 0) {
      $st = mysqli_prepare($conn, "DELETE FROM jabatan WHERE id=?");
      if (!$st) die("Prepare gagal: " . mysqli_error($conn));

      mysqli_stmt_bind_param($st, "i", $delId);
      mysqli_stmt_execute($st);
      mysqli_stmt_close($st);

      redirect(app_url('pages/master/jabatan.php?ok=deleted'));
    }
  }

  // SAVE (ADD/EDIT)
  if (isset($_POST['save'])) {
    $nama = trim($_POST['nama_jabatan'] ?? '');

    if ($nama === '') {
      $err = 'Nama jabatan wajib diisi.';
    } else {
      if ($editing) {
        $st = mysqli_prepare($conn, "UPDATE jabatan SET nama_jabatan=? WHERE id=?");
        if (!$st) die("Prepare gagal: " . mysqli_error($conn));

        mysqli_stmt_bind_param($st, "si", $nama, $editId);
        mysqli_stmt_execute($st);
        mysqli_stmt_close($st);

        redirect(app_url('pages/master/jabatan.php?ok=updated'));
      } else {
        $st = mysqli_prepare($conn, "INSERT INTO jabatan (nama_jabatan) VALUES (?)");
        if (!$st) die("Prepare gagal: " . mysqli_error($conn));

        mysqli_stmt_bind_param($st, "s", $nama);
        mysqli_stmt_execute($st);
        mysqli_stmt_close($st);

        redirect(app_url('pages/master/jabatan.php?ok=created'));
      }
    }

    $form['nama_jabatan'] = $nama;
  }
}

if (isset($_GET['ok'])) {
  if ($_GET['ok'] === 'created') $ok = 'Jabatan berhasil ditambahkan.';
  if ($_GET['ok'] === 'updated') $ok = 'Jabatan berhasil diperbarui.';
  if ($_GET['ok'] === 'deleted') $ok = 'Jabatan berhasil dihapus.';
}

/* =========================
   LIST + SEARCH
========================= */
$q = trim($_GET['q'] ?? '');
$rows = [];

if ($q !== '') {
  $like = '%' . $q . '%';
  $st = mysqli_prepare($conn, "SELECT id, nama_jabatan FROM jabatan WHERE nama_jabatan LIKE ? ORDER BY nama_jabatan ASC");
  if (!$st) die("Prepare gagal: " . mysqli_error($conn));

  mysqli_stmt_bind_param($st, "s", $like);
  mysqli_stmt_execute($st);
  $rs = mysqli_stmt_get_result($st);

  while ($r = mysqli_fetch_assoc($rs)) $rows[] = $r;
  mysqli_stmt_close($st);
} else {
  $rs = mysqli_query($conn, "SELECT id, nama_jabatan FROM jabatan ORDER BY nama_jabatan ASC");
  if ($rs) while ($r = mysqli_fetch_assoc($rs)) $rows[] = $r;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Master Data – Jabatan</title>

<style>
/* =========================
   CSS DARI KAMU (dibenerin :root)
========================= */
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

  /* NEW: item/black accents for action buttons */
  --ink: #0b0f19;
  --ink-soft: rgba(0,0,0,.55);
}

*{ box-sizing:border-box; }

/* Matikan sidebar lokal dari template lama jika ada */
#sidebar, .sidebar, .left-sidebar, .app-sidebar {display:none!important;}
body {padding-left:0!important;}

/* Biar page ini punya background sama login */
body{
  margin:0;
  font-family:"Segoe UI",system-ui,sans-serif;
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

.page-head{
  display:flex;
  align-items:flex-end;
  justify-content:space-between;
  gap: 14px;
  margin-bottom: 14px;
}

.page-title{
  margin:0;
  font-size: 24px;
  font-weight: 900;
  letter-spacing: .02em;
}
.page-sub{
  margin: 6px 0 0;
  color: var(--text-soft);
  font-size: 13px;
  line-height: 1.5;
}

/* Actionbar */
.actionbar{
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
.pill.primary:hover{
  background: var(--btn-hover);
}

/* Input */
.input{
  flex: 1;
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
.input:focus, .pill:focus, .btn-sm:focus{
  box-shadow: 0 0 0 3px rgba(255, 211, 105, 0.35);
  border-color: rgba(255,255,255,0.55);
}

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

.form-row label{
  display:block;
  margin-bottom: 6px;
  font-size: 13px;
  color: var(--text-soft);
}
.form-row .input{
  width: 100%;
  max-width: 480px;
}

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

/* Table */
.table-wrap{
  overflow:auto;
  border-radius: 14px;
  border: 1px solid rgba(255,255,255,0.18);
}

.table{
  width:100%;
  border-collapse: collapse;
  min-width: 640px;
}

.table th, .table td{
  padding: 12px;
  border-bottom: 1px solid rgba(255,255,255,0.16);
  font-size: 14px;
  text-align:left;
}

.table th{
  background: rgba(255,255,255,0.10);
  color: var(--text);
  font-weight: 900;
}

.table tbody tr:nth-child(odd){
  background: rgba(255,255,255,0.06);
}
.table tbody tr:hover{
  background: rgba(255,255,255,0.10);
}

/* Buttons */
.btn-sm{
  display:inline-flex;
  align-items:center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: 999px;
  font-size: 13px;
  text-decoration:none;
  border: 1px solid rgba(255,255,255,0.35);
  background: transparent;
  color: var(--text);
  transition: .2s ease;
  cursor:pointer;
}
.btn-sm:hover{
  background: rgba(255,255,255,0.16);
  transform: translateY(-2px);
}

/* EDIT/HAPUS: ITEM (HITAM) */
.btn-edit, .btn-del{
  border-color: rgba(0,0,0,.55) !important;
  color: var(--ink) !important;
  background: rgba(255,255,255,0.78) !important;
}
.btn-edit:hover, .btn-del:hover{
  background: rgba(255,255,255,0.92) !important;
  border-color: rgba(0,0,0,.75) !important;
  box-shadow: 0 10px 18px rgba(0,0,0,0.18);
  transform: translateY(-2px);
}

/* Empty */
.empty{
  border: 1px dashed rgba(255,255,255,0.35);
  border-radius: 14px;
  padding: 26px;
  text-align:center;
  background: rgba(255,255,255,0.08);
  color: var(--text-soft);
}
.empty .title{
  font-weight: 900;
  color: var(--text);
  margin-bottom: 6px;
}

/* Responsive */
@media(max-width: 980px){
  .page{ margin-top: 80px; }
  .page-head{ flex-direction: column; align-items:flex-start; }
}

/* Animation */
@keyframes fadeUp{
  from{opacity:0; transform: translateY(14px);}
  to{opacity:1; transform: translateY(0);}
}
</style>
</head>

<body>
  <div class="page">

    <div class="page-head">
      <div>
        <h1 class="page-title">Master Data – Jabatan</h1>
        <p class="page-sub">Kelola jabatan organisasi: tambah, ubah, hapus, dan cari data dengan cepat.</p>
      </div>
    </div>

    <!-- ACTIONBAR -->
    <div class="actionbar">
      <a class="pill" href="<?= app_url('pages/dashboard_admin.php') ?>">← Kembali</a>
      <a class="pill primary" href="<?= app_url('pages/master/jabatan.php?add=1') ?>">+ Tambah / Edit Jabatan</a>

      <form method="get" action="" style="flex:1; display:flex; gap:10px;">
        <input class="input" type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari nama jabatan... (tekan / untuk fokus)">
      </form>

      <a class="pill" href="<?= app_url('logout.php') ?>">Logout</a>
    </div>

    <?php if($err): ?><div class="alert err"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <?php if($ok): ?><div class="alert ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

    <!-- FORM -->
    <?php if($adding || $editing): ?>
      <div class="card">
        <h3><?= $editing ? 'Edit Jabatan' : 'Tambah Jabatan' ?></h3>

        <form method="post" action="<?= app_url('pages/master/jabatan.php' . ($editing ? ('?edit='.$editId) : '?add=1')) ?>">
          <div class="form-row">
            <label>Nama Jabatan</label>
            <input class="input" type="text" name="nama_jabatan"
                   value="<?= htmlspecialchars($form['nama_jabatan']) ?>"
                   placeholder="contoh: STAFF, CEO, Manager" required>
          </div>

          <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
            <button class="pill primary" type="submit" name="save" value="1">💾 Simpan</button>
            <a class="pill" href="<?= app_url('pages/master/jabatan.php') ?>">Batal</a>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <!-- TABLE -->
    <div class="card">
      <h3>Daftar Jabatan</h3>

      <?php if(!$rows): ?>
        <div class="empty">
          <div class="title">Belum ada data jabatan</div>
          <div>Tekan tombol <b>Tambah / Edit Jabatan</b> untuk menambahkan data baru.</div>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th style="width:70px;">No</th>
                <th>Nama Jabatan</th>
                <th style="width:220px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $no=1; foreach($rows as $r): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= htmlspecialchars($r['nama_jabatan']) ?></td>
                  <td>
                    <a class="btn-sm btn-edit" href="<?= app_url('pages/master/jabatan.php?edit='.(int)$r['id']) ?>">✏️ Edit</a>

                    <form method="post" action="<?= app_url('pages/master/jabatan.php') ?>" style="display:inline-block;"
                          onsubmit="return confirm('Hapus jabatan ini?');">
                      <input type="hidden" name="delete_id" value="<?= (int)$r['id'] ?>">
                      <button class="btn-sm btn-del" type="submit">🗑️ Hapus</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  </div>

<script>
// shortcut "/" fokus ke search
document.addEventListener('keydown', function(e){
  if(e.key === '/' && !['INPUT','TEXTAREA'].includes(document.activeElement.tagName)){
    e.preventDefault();
    const el = document.querySelector('input[name="q"]');
    if(el) el.focus();
  }
});
</script>
</body>
</html>
