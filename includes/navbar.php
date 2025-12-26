<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
  <div class="container-fluid">
    <!-- (dihilangkan) brand/judul yang bikin dobel -->
    <div></div>

    <div>
      <span class="me-3">
        Halo, <?= htmlspecialchars($_SESSION['username'] ?? 'pengguna'); ?>
        (<?= htmlspecialchars($_SESSION['role'] ?? '-') ?>)
      </span>
      <a href="<?= app_url('logout.php') ?>" class="btn btn-sm btn-outline-danger">Logout</a>
    </div>
  </div>
</nav>

<div class="container-fluid mt-4">
