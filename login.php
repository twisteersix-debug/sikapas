<?php
// ============================================================
// SIKAPAS.RIAU — LOGIN v4 (Logo Lambang sebagai Background)
// ============================================================
require_once 'includes/config.php';
startSession();

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

 $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = [
                'id'      => $user['id'],
                'nama'    => $user['nama'],
                'role'    => $user['role'],
                'inisial' => strtoupper(substr($user['nama'], 0, 1))
            ];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Silakan isi username dan password.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SIKAPAS.RIAU &#8212; Login</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
    background: #0a1628;
}

/* === BACKGROUND UTAMA — gambar lambang besar di tengah === */
.bg-image {
    position: fixed;
    inset: 0;
    z-index: 0;
    background-color: #0a1628;
    background-image: url('https://z-cdn-media.chatglm.cn/files/4614e546-6228-48bd-b247-fd11897498cb.png?auth_key=1882360732-5beddf799f7c4909834f4076750e4989-0-88625008dbb97d07493cfe61b9da9557');
    background-size: 55% auto;
    background-position: center center;
    background-repeat: no-repeat;
    opacity: 0.12;
}

/* === OVERLAY GRADIEN — memberi kedalaman === */
.bg-overlay {
    position: fixed;
    inset: 0;
    z-index: 1;
    background: radial-gradient(
        ellipse at center,
        rgba(10, 22, 40, 0.40) 0%,
        rgba(10, 22, 40, 0.80) 60%,
        rgba(10, 22, 40, 0.95) 100%
    );
}

/* === PARTIKEL DEKORATIF === */
.bg-particles {
    position: fixed;
    inset: 0;
    z-index: 2;
    overflow: hidden;
    pointer-events: none;
}
.bg-particles span {
    position: absolute;
    width: 3px;
    height: 3px;
    background: rgba(59, 130, 246, 0.25);
    border-radius: 50%;
    animation: floatUp linear infinite;
}
.bg-particles span:nth-child(1)  { left: 10%; bottom: -10px; animation-duration: 12s; animation-delay: 0s; }
.bg-particles span:nth-child(2)  { left: 25%; bottom: -10px; animation-duration: 16s; animation-delay: 2s; width: 2px; height: 2px; }
.bg-particles span:nth-child(3)  { left: 40%; bottom: -10px; animation-duration: 14s; animation-delay: 4s; }
.bg-particles span:nth-child(4)  { left: 55%; bottom: -10px; animation-duration: 18s; animation-delay: 1s; width: 4px; height: 4px; }
.bg-particles span:nth-child(5)  { left: 70%; bottom: -10px; animation-duration: 13s; animation-delay: 3s; }
.bg-particles span:nth-child(6)  { left: 85%; bottom: -10px; animation-duration: 15s; animation-delay: 5s; width: 2px; height: 2px; }
.bg-particles span:nth-child(7)  { left: 15%; bottom: -10px; animation-duration: 20s; animation-delay: 7s; }
.bg-particles span:nth-child(8)  { left: 60%; bottom: -10px; animation-duration: 11s; animation-delay: 6s; width: 4px; height: 4px; }
.bg-particles span:nth-child(9)  { left: 35%; bottom: -10px; animation-duration: 17s; animation-delay: 8s; }
.bg-particles span:nth-child(10) { left: 90%; bottom: -10px; animation-duration: 14s; animation-delay: 4s; width: 2px; height: 2px; }

@keyframes floatUp {
    0%   { transform: translateY(0) scale(1); opacity: 0; }
    10%  { opacity: 1; }
    90%  { opacity: 1; }
    100% { transform: translateY(-100vh) scale(0.5); opacity: 0; }
}

/* === LOGIN CARD — TRANSPARAN (glassmorphism) === */
.login-wrapper {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 420px;
    padding: 20px;
}

.login-card {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 24px;
    padding: 40px 36px 32px;
    box-shadow:
        0 25px 60px rgba(0, 0, 0, 0.30),
        inset 0 1px 0 rgba(255, 255, 255, 0.08);
    text-align: center;
}

/* === LOGO KECIL DI ATAS CARD === */
.logo-badge {
    width: 72px;
    height: 72px;
    margin: 0 auto 20px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.15);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.30);
    background: rgba(255, 255, 255, 0.05);
    display: flex;
    align-items: center;
    justify-content: center;
}
.logo-badge img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 6px;
}

/* === TYPOGRAPHY === */
.login-title {
    font-size: 24px;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.3px;
    margin-bottom: 6px;
}
.login-title span {
    color: #60a5fa;
}
.login-subtitle {
    font-size: 11.5px;
    color: rgba(255, 255, 255, 0.50);
    line-height: 1.55;
    margin-bottom: 28px;
    max-width: 300px;
    margin-left: auto;
    margin-right: auto;
}

/* === ERROR === */
.error-box {
    background: rgba(239, 68, 68, 0.12);
    border: 1px solid rgba(239, 68, 68, 0.25);
    border-radius: 10px;
    padding: 11px 14px;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: #fca5a5;
    font-weight: 500;
    text-align: left;
}
.error-box svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    color: #ef4444;
}

/* === FORM === */
.form-group {
    margin-bottom: 16px;
    text-align: left;
}
.form-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.55);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.input-wrap {
    position: relative;
}
.form-input {
    width: 100%;
    padding: 13px 14px 13px 42px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    font-size: 14px;
    font-family: inherit;
    color: #ffffff;
    background: rgba(255, 255, 255, 0.06);
    outline: none;
    transition: all 0.25s ease;
}
.form-input::placeholder {
    color: rgba(255, 255, 255, 0.30);
}
.form-input:focus {
    border-color: rgba(96, 165, 250, 0.50);
    background: rgba(255, 255, 255, 0.10);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
}
.input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    color: rgba(255, 255, 255, 0.30);
    pointer-events: none;
    transition: color 0.25s ease;
}
.form-input:focus ~ .input-icon,
.form-input:not(:placeholder-shown) ~ .input-icon {
    color: #60a5fa;
}

/* === BUTTON === */
.btn-login {
    width: 100%;
    padding: 14px 20px;
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.25s ease;
    box-shadow: 0 4px 16px rgba(30, 64, 175, 0.35);
    margin-top: 6px;
    letter-spacing: 0.2px;
}
.btn-login:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(30, 64, 175, 0.50);
    background: linear-gradient(135deg, #1d4ed8, #60a5fa);
}
.btn-login:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(30, 64, 175, 0.30);
}

/* === FOOTER === */
.login-footer {
    text-align: center;
    margin-top: 20px;
}
.login-footer p {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.40);
    line-height: 1.6;
}
.login-footer strong {
    color: rgba(255, 255, 255, 0.60);
    font-weight: 600;
}

/* === RESPONSIVE === */
@media (max-width: 480px) {
    .login-card {
        padding: 32px 22px 24px;
        border-radius: 20px;
    }
    .login-title { font-size: 21px; }
    .login-wrapper { padding: 16px; }
    .bg-image {
        background-size: 70% auto;
    }
}
</style>
</head>
<body>

<!-- Layer 1: Gambar lambang besar di tengah (z-index: 0) -->
<div class="bg-image" aria-hidden="true"></div>

<!-- Layer 2: Overlay gradien (z-index: 1) -->
<div class="bg-overlay" aria-hidden="true"></div>

<!-- Layer 3: Partikel animasi (z-index: 2) -->
<div class="bg-particles" aria-hidden="true">
    <span></span><span></span><span></span><span></span><span></span>
    <span></span><span></span><span></span><span></span><span></span>
</div>

<!-- Layer 4: Card login transparan (z-index: 10) -->
<div class="login-wrapper">
    <div class="login-card">

        <!-- Logo lambang kecil di atas card -->
        <div class="logo-badge">
            <img src="https://z-cdn-media.chatglm.cn/files/4614e546-6228-48bd-b247-fd11897498cb.png?auth_key=1882360732-5beddf799f7c4909834f4076750e4989-0-88625008dbb97d07493cfe61b9da9557" alt="Lambang PAS Riau">
        </div>

        <!-- Judul -->
        <h1 class="login-title">SIKAPAS<span>.RIAU</span></h1>
        <p class="login-subtitle">
            Sistem Informasi Kepegawaian dan Administrasi<br>Pemasyarakatan Kanwil Riau
        </p>

        <!-- Error -->
        <?php if ($error): ?>
        <div class="error-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <div class="input-wrap">
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-input"
                        placeholder="Masukkan username"
                        required
                        autofocus
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    >
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="Masukkan password"
                        required
                    >
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
            </div>

            <button type="submit" class="btn-login">Masuk ke Sistem</button>
        </form>
    </div>

    <!-- Tagline -->
    <div class="login-footer">
        <p><strong>Satu Sikap, Satu Data PAS Riau</strong><br>
        Kanwil Kementerian Hukum dan HAM Riau</p>
    </div>
</div>

</body>
</html>
