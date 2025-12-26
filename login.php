<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/functions.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();

    // LOGIN PLAIN TEXT (SEMENTARA)
    if ($user && $password === $user['password']) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['role']     = $user['role'];
        $_SESSION['username'] = $user['username'];

        if ($user['role'] === 'admin') {
            redirect("pages/dashboard_admin.php");
        } else {
            redirect("pages/dashboard_pegawai.php");
        }
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login - Sistem Kepegawaian</title>

<style>
/* ============================= */
/* === LOGIN PAGE ONLY STYLE === */
/* ============================= */

body.login-body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    justify-content: center;
    align-items: center;
}

/* container */
.login-container {
    width: 100%;
    max-width: 400px;
    padding: 20px;
}

/* card */
.login-card {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(12px);
    border-radius: 16px;
    padding: 35px;
    color: #fff;
    box-shadow: 0 15px 30px rgba(0,0,0,0.3);
    animation: loginFade 0.8s ease;
}

.login-card h2 {
    text-align: center;
    margin-bottom: 5px;
}

.login-subtitle {
    text-align: center;
    font-size: 14px;
    opacity: 0.85;
    margin-bottom: 28px;
}

/* input */
.login-input-group {
    position: relative;
    margin-bottom: 22px;
}

.login-input-group input {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    outline: none;
    font-size: 15px;
}

.login-input-group label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    opacity: 0.9;
}

/* button */
.login-btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 25px;
    background: #ffd369;
    color: #333;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s;
}

.login-btn:hover {
    background: #ffbf00;
    transform: translateY(-2px);
}

/* error */
.login-error {
    background: rgba(255,0,0,0.25);
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    text-align: center;
    font-size: 14px;
}

/* animation */
@keyframes loginFade {
    from {
        opacity: 0;
        transform: translateY(25px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
</head>

<body class="login-body">

<div class="login-container">
    <div class="login-card">
        <h2>Sistem Kepegawaian</h2>
        <p class="login-subtitle">Silakan login untuk melanjutkan</p>

        <?php if ($error): ?>
            <div class="login-error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="login-input-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="login-input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>
    </div>
</div>

</body>
</html>
