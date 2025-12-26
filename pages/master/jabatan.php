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

  --shadow: 0 15px 30px rgba(0,0,0,0.30);
  --shadow-soft: 0 12px 26px rgba(0,0,0,0.18);

  --radius: 18px;
}

*{box-sizing:border-box;}
html,body{height:100%;}
body{
  margin:0;
  font-family:"Segoe UI",system-ui,sans-serif;
  background: linear-gradient(135deg, var(--grad-a), var(--grad-b));
  color: var(--text);
}

/* NAVBAR */
.navbar{
  position: sticky;
  top: 0;
  z-index: 50;
  padding: 14px 16px;
  background: linear-gradient(135deg, var(--grad-a), var(--grad-b));
  box-shadow: var(--shadow);
}
.nav-inner{
  max-width: 1200px;
  margin: 0 auto;
  padding: 10px 14px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 12px;
  background: var(--glass);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  backdrop-filter: blur(12px);
}
.brand{
  font-weight: 900;
  letter-spacing: .02em;
  display:flex;
  align-items:center;
  gap:10px;
  color:#fff;
}
.nav-right{
  display:flex;
  align-items:center;
  gap: 10px;
  flex-wrap: wrap;
  justify-content:flex-end;
}
.user-chip{
  background: rgba(255,255,255,.22);
  color:#fff;
  padding: 8px 14px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 800;
  white-space: nowrap;
}
.btn-pill{
  background: rgba(255,255,255,0.20);
  border: 1px solid rgba(255,255,255,0.22);
  color: rgba(255,255,255,0.92);
  padding: 8px 14px;
  border-radius: 999px;
  text-decoration:none;
  font-weight: 900;
  transition: .2s ease;
}
.btn-pill:hover{background: rgba(255,255,255,0.26); transform: translateY(-1px);}
.btn-logout{
  background: var(--btn);
  color:#333;
  border:none;
  border-radius: 999px;
  padding: 8px 14px;
  font-weight: 900;
  text-decoration: none;
  transition: .25s ease;
}
.btn-logout:hover{background: var(--btn-hover); transform: translateY(-2px);}

/* PAGE */
.page{
  max-width: 1200px;
  margin: 22px auto 40px;
  padding: 0 18px 40px;
  animation: fadeUp .6s ease both;
}
@keyframes fadeUp{from{opacity:0; transform:translateY(10px);}to{opacity:1; transform:none;}}
.h1{font-size: 30px; margin: 40px 0 6px; font-weight: 900; text-align:center;}
.sub{color: var(--text-soft); margin: 0 0 18px; font-size: 13px; text-align:center;}

/* CARD */
.card{
  background: var(--glass);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  backdrop-filter: blur(12px);
  box-shadow: var(--shadow-soft);
  padding: 18px;
}

/* TOOLBAR */
.toolbar{
  display:flex;
  gap:12px;
  flex-wrap:wrap;
  align-items:center;
}
.btn{
  border: none;
  border-radius: 999px;
  padding: 10px 14px;
  font-weight: 900;
  cursor:pointer;
  transition: .2s ease;
  text-decoration:none;
  display:inline-flex;
  align-items:center;
  gap:8px;
}
.btn.secondary{
  background: rgba(255,255,255,0.20);
  border: 1px solid rgba(255,255,255,0.22);
  color: rgba(255,255,255,0.92);
}
.btn.secondary:hover{background: rgba(255,255,255,0.26); transform: translateY(-1px);}
.btn.primary{
  background: var(--btn);
  color:#333;
}
.btn.primary:hover{background: var(--btn-hover); transform: translateY(-1px);}

.search{
  flex:1;
  min-width: 280px;
}
.search input{
  width:100%;
  border-radius: 999px;
  padding: 11px 14px;
  border: 1px solid rgba(255,255,255,0.22);
  background: rgba(255,255,255,0.18);
  color:#fff;
  outline:none;
}
.search input::placeholder{color: rgba(255,255,255,0.70);}

.alert{
  margin-top: 12px;
  padding: 10px 12px;
  border-radius: 14px;
  border: 1px solid rgba(255,255,255,0.22);
  background: rgba(255,255,255,0.14);
  color: rgba(255,255,255,0.92);
  font-weight: 800;
}
.alert.err{background: rgba(255,0,0,0.10); border-color: rgba(255,255,255,0.18);}
.alert.ok{background: rgba(0,255,130,0.10); border-color: rgba(255,255,255,0.18);}

/* TABLE */
.table-wrap{margin-top: 14px; overflow:auto;}
table{
  width:100%;
  border-collapse: collapse;
  min-width: 720px;
  background: rgba(255,255,255,0.86);
  border-radius: 14px;
  overflow:hidden;
}
thead th{
  background: rgba(70, 82, 170, 0.22);
  color: #fff;
  text-align:left;
  padding: 12px 14px;
  font-size: 13px;
  font-weight: 900;
}
tbody td{
  padding: 12px 14px;
  border-bottom: 1px solid rgba(0,0,0,0.08);
  color:#111827;
  font-size: 14px;
}
tbody tr:last-child td{border-bottom:none;}

.actions{
  display:flex;
  gap:10px;
  justify-content:flex-end;
}
.btn-sm{
  padding: 8px 12px;
  border-radius: 999px;
  border: 1px solid rgba(0,0,0,0.20);
  background:#fff;
  cursor:pointer;
  font-weight: 900;
}
.btn-sm:hover{transform: translateY(-1px);}

/* MODAL FORM */
.modal{
  margin-top: 12px;
  display: <?= ($editing || $adding) ? 'block' : 'none' ?>;
}
.label{
  font-weight: 900;
  font-size: 12px;
  color: rgba(255,255,255,0.85);
  margin: 10px 0 6px;
  display:block;
  letter-spacing: .03em;
  text-transform: uppercase;
}
.input{
  width:100%;
  border-radius: 14px;
  padding: 12px 12px;
  border: 1px solid rgba(255,255,255,0.22);
  background: rgba(255,255,255,0.18);
  color:#fff;
  outline:none;
}
.form-actions{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  margin-top: 12px;
}
@media(max-width: 720px){
  .h1{font-size: 24px;}
}
</style>
</head>

<body>
  <div class="navbar">
    <div class="nav-inner">
      <div class="brand">Sistem Kepegawaian</div>
      <div class="nav-right">
        <a class="btn-pill" href="<?= app_url('pages/dashboard_admin.php') ?>">Dashboard</a>
        <span class="user-chip"><?= htmlspecialchars($username) ?> (admin)</span>
        <a class="btn-logout" href="<?= app_url('logout.php') ?>">Logout</a>
      </div>
    </div>
  </div>

  <div class="page">
    <h1 class="h1">Master Data – Jabatan</h1>
    <p class="sub">Kelola jabatan organisasi: tambah, ubah, hapus, dan cari data dengan cepat.</p>

    <div class="card">
      <div class="toolbar">
        <a class="btn secondary" href="<?= app_url('pages/dashboard_admin.php') ?>">← Kembali</a>
        <a class="btn primary" href="<?= app_url('pages/master/jabatan.php?add=1') ?>">+ Tambah / Edit Jabatan</a>

        <form class="search" method="get" action="">
          <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari nama jabatan... (tekan / untuk fokus)">
        </form>
      </div>

      <?php if($err): ?><div class="alert err"><?= htmlspecialchars($err) ?></div><?php endif; ?>
      <?php if($ok): ?><div class="alert ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

      <!-- FORM (Tambah/Edit) -->
      <div class="modal">
        <div style="margin-top:12px; font-weight:900; font-size:16px;">
          <?= $editing ? 'Edit Jabatan' : 'Tambah Jabatan' ?>
        </div>

        <form method="post" action="<?= app_url('pages/master/jabatan.php' . ($editing ? ('?edit='.$editId) : '?add=1')) ?>">
          <label class="label">Nama Jabatan</label>
          <input class="input" type="text" name="nama_jabatan"
                 value="<?= htmlspecialchars($form['nama_jabatan']) ?>"
                 placeholder="contoh: STAFF, CEO, Manager" required>

          <div class="form-actions">
            <button class="btn primary" type="submit" name="save" value="1">💾 Simpan</button>
            <a class="btn secondary" href="<?= app_url('pages/master/jabatan.php') ?>">Batal</a>
          </div>
        </form>
      </div>

      <!-- TABEL -->
      <div class="table-wrap">
        <div style="font-weight:900; margin: 12px 0 10px; color: rgba(255,255,255,0.92);">Daftar Jabatan</div>

        <table>
          <thead>
            <tr>
              <th style="width:70px;">No</th>
              <th>Nama Jabatan</th>
              <th style="width:210px; text-align:right;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if(!$rows): ?>
              <tr><td colspan="3">Data jabatan belum ada.</td></tr>
            <?php else: ?>
              <?php $no=1; foreach($rows as $r): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= htmlspecialchars($r['nama_jabatan']) ?></td>
                  <td>
                    <div class="actions">
                      <a class="btn-sm" href="<?= app_url('pages/master/jabatan.php?edit='.(int)$r['id']) ?>">✏️ Edit</a>

                      <form method="post" action="<?= app_url('pages/master/jabatan.php') ?>" onsubmit="return confirm('Hapus jabatan ini?');">
                        <input type="hidden" name="delete_id" value="<?= (int)$r['id'] ?>">
                        <button class="btn-sm" type="submit">🗑️ Hapus</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

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
