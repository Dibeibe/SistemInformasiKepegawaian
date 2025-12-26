<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$role = $_SESSION['role'] ?? '';
$username = htmlspecialchars($_SESSION['username'] ?? 'User');

$dashboardPath = ($role === 'admin')
  ? 'pages/dashboard_admin.php'
  : 'pages/dashboard_pegawai.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $page_title ?? 'Sistem Kepegawaian'; ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ============================= */
/* === GLOBAL HEADER (LOGIN DNA) === */
/* ============================= */

:root{
  --grad-a:#667eea;
  --grad-b:#764ba2;

  --glass: rgba(255,255,255,0.14);
  --stroke: rgba(255,255,255,0.22);

  --text:#ffffff;
  --text-soft: rgba(255,255,255,0.88);
  --muted: rgba(255,255,255,0.70);

  --btn:#ffd369;
  --btn-hover:#ffbf00;

  --ring: rgba(255,211,105,.45);
  --shadow: 0 12px 30px rgba(0,0,0,.25);

  --radius: 18px;
}

*{ box-sizing:border-box; }

/* body default (page lain boleh override) */
body{
  font-family:"Segoe UI",Inter,Arial,sans-serif;
  padding-top:80px;
}

/* 🔥 matiin underline link */
a{ text-decoration:none !important; }

/* ===== NAVBAR ===== */
.navbar-glass{
  background: linear-gradient(135deg, var(--grad-a), var(--grad-b));
  box-shadow: var(--shadow);
}

.navbar-inner{
  background: var(--glass);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  backdrop-filter: blur(12px);
  padding: 10px 14px;
}

/* Brand */
.navbar-glass .navbar-brand{
  color:#fff;
  font-weight:900;
  letter-spacing:.3px;
  display:flex;
  align-items:center;
  gap:10px;
}
.navbar-glass .navbar-brand:hover{opacity:.9}

/* Nav link */
.navbar-glass .nav-link{
  color: var(--text-soft);
  padding: 8px 14px;
  border-radius: 999px;
  font-weight:700;
  transition:.25s ease;
}
.navbar-glass .nav-link:hover{
  background: rgba(255,255,255,.18);
  color:#fff;
  transform: translateY(-1px);
}
.navbar-glass .nav-link.active{
  background: rgba(255,255,255,.26);
  color:#fff;
  box-shadow: 0 0 0 3px var(--ring);
}

/* User chip */
.user-chip{
  background: rgba(255,255,255,.22);
  color:#fff;
  padding: 8px 14px;
  border-radius: 999px;
  font-size: 13px;
  font-weight:700;
  white-space:nowrap;
}

/* Logout */
.btn-logout{
  background: var(--btn);
  color:#333;
  border-radius: 999px;
  padding: 8px 14px;
  font-weight:800;
  transition:.25s ease;
  border:none;
}
.btn-logout:hover{
  background: var(--btn-hover);
  transform: translateY(-2px);
}

/* Toggler */
.navbar-toggler{
  border:none;
}
.navbar-toggler-icon{
  filter: invert(1);
}

/* Responsive */
@media(max-width:768px){
  .user-chip{ display:none; }
  body{ padding-top:90px; }
}
</style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-glass fixed-top">
  <div class="container-fluid px-3 px-lg-4">
    <div class="navbar-inner d-flex w-100 align-items-center justify-content-between">

      <a class="navbar-brand" href="<?= app_url('index.php') ?>">
        Sistem Kepegawaian
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-end" id="topNav">
        <ul class="navbar-nav align-items-lg-center gap-2 me-lg-3 mt-3 mt-lg-0">
          <li class="nav-item">
            <a class="nav-link active" href="<?= app_url($dashboardPath) ?>">
              Dashboard
            </a>
          </li>
        </ul>

        <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
          <span class="user-chip">
            <?= $username ?> (<?= $role ?>)
          </span>
          <a href="<?= app_url('logout.php') ?>" class="btn btn-logout btn-sm">
            Logout
          </a>
        </div>
      </div>

    </div>
  </div>
</nav>

<div class="container-fluid">
