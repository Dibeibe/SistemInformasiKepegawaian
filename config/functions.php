<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        redirect("../login.php");
    }
}

function current_pegawai_id(): int {
    if (isset($_SESSION['pegawai_id'])) {
        return (int)$_SESSION['pegawai_id'];
    }

    if (!isset($_SESSION['user_id'])) {
        return 0;
    }

    global $conn;
    $uid = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT id FROM pegawai WHERE user_id=? LIMIT 1");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->bind_result($pid);

    if ($stmt->fetch()) {
        $_SESSION['pegawai_id'] = (int)$pid;
        return (int)$pid;
    }

    return 0;
}
