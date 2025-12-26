<?php
require_once __DIR__ . '/config/functions.php';

// Hapus semua session
session_unset();
session_destroy();

redirect("login.php");
?>
