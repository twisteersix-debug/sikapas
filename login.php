<?php
// ============================================================
// SIKAPAS.RIAU — LOGIN PREMIUM (Redesign by SIKAPAS v2)
// ============================================================
require_once 'includes/config.php';
startSession();

// Redirect jika sudah login
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
<title>SIKAPAS.RIAU — Login</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,600&display=swap" rel="stylesheet">
<style>
/* ===================== RESET ===================== */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

/* ===================== BODY / BG ===================== */
body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    position: relative;
    overflow: hidden;
    background: #061437;
}

/* Background building photo */
.bg-photo {
    position: fixed;
    inset: 0;
    background:
        linear-gradient(160deg, rgba(5,16,50,0.75) 0%, rgba(10,35,95,0.55) 50%, rgba(5,16,50,0.80) 100%);
    z-index: 0;
}
/* Simulate the atmospheric building background using CSS */
.bg-photo::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 80% 60% at 20% 50%, rgba(30,80,180,0.45) 0%, transparent 60%),
        radial-gradient(ellipse 60% 80% at 80% 60%, rgba(10,30,100,0.6) 0%, transparent 55%),
        radial-gradient(ellipse 100% 40% at 50% 100%, rgba(5,15,50,0.9) 0%, transparent 50%);
}

/* Animated starfield / bokeh */
.bg-bokeh {
    position: fixed;
    inset: 0;
    z-index: 0;
    overflow: hidden;
}
.bokeh-dot {
    position: absolute;
    border-radius: 50%;
    background: rgba(200,220,255,0.55);
    animation: twinkle var(--dur, 4s) ease-in-out infinite alternate;
    animation-delay: var(--delay, 0s);
}
@keyframes twinkle {
    from { opacity: 0.2; transform: scale(1); }
    to   { opacity: 0.7; transform: scale(1.4); }
}

/* ===================== MAIN CONTAINER ===================== */
.page-wrap {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    padding: 24px 16px 100px;
}

/* ===================== CARD ===================== */
.login-card {
    width: 100%;
    max-width: 520px;
    background: rgba(220, 235, 255, 0.18);
    border: 1px solid rgba(255,255,255,0.28);
    border-radius: 28px;
    padding: 44px 44px 36px;
    backdrop-filter: blur(22px);
    -webkit-backdrop-filter: blur(22px);
    box-shadow:
        0 32px 80px rgba(0,0,0,0.45),
        inset 0 1px 0 rgba(255,255,255,0.22),
        inset 0 -1px 0 rgba(0,0,0,0.08);
    position: relative;
    overflow: hidden;
}

/* subtle inner glow on card top */
.login-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
    border-radius: 28px 28px 0 0;
}

/* ===================== WATERMARK INSIDE CARD ===================== */
.card-watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 320px;
    height: 320px;
    opacity: 0.07;
    pointer-events: none;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 0;
}
.watermark-text {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 8px;
    text-transform: uppercase;
    color: rgba(255,255,255,1);
    white-space: nowrap;
}
.watermark-ring {
    position: absolute;
    inset: 0;
    border: 2px solid rgba(255,255,255,0.8);
    border-radius: 50%;
}
.watermark-ring-inner {
    position: absolute;
    inset: 30px;
    border: 1px solid rgba(255,255,255,0.5);
    border-radius: 50%;
}
/* Circular text via SVG */
.watermark-svg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}

/* ===================== LOGO SECTION ===================== */
.logo-section {
    position: relative;
    z-index: 2;
    text-align: center;
    margin-bottom: 24px;
}

/* Shield logo wrapper — dua logo bertumpuk */
.shield-wrap {
    display: inline-block;
    margin-bottom: 18px;
    position: relative;
}

/* Logo Kementerian (belakang / background) */
.logo-kemen {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 148px;
    height: 148px;
    object-fit: contain;
    opacity: 0.22;
    filter: brightness(2.5) saturate(0);
    z-index: 0;
    pointer-events: none;
}

/* Logo SDM (depan) — hilangkan bg putih pakai mix-blend-mode */
.shield-svg {
    width: 110px;
    height: 128px;
    position: relative;
    z-index: 1;
    filter:
        drop-shadow(0 8px 20px rgba(0,0,0,0.55))
        drop-shadow(0 0 1px rgba(0,0,0,0.3));
    mix-blend-mode: screen;
}

.brand-name {
    font-size: 38px;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: 0.5px;
    text-shadow: 0 2px 12px rgba(0,0,0,0.3);
    margin-bottom: 6px;
}

.brand-sub {
    font-size: 13px;
    color: rgba(255,255,255,0.82);
    line-height: 1.6;
    margin-bottom: 18px;
}

/* Slogan pill */
.slogan-pill {
    display: inline-block;
    padding: 10px 22px;
    border-radius: 50px;
    background: rgba(255,255,255,0.14);
    border: 1px solid rgba(255,255,255,0.22);
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    font-style: italic;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

/* ===================== FORM ===================== */
.form-body {
    position: relative;
    z-index: 2;
}

.field-group {
    margin-bottom: 16px;
}

.field-label {
    display: block;
    color: rgba(255,255,255,0.9);
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
    letter-spacing: 0.1px;
}

.input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 14px;
    width: 18px;
    height: 18px;
    color: rgba(100,130,190,0.7);
    flex-shrink: 0;
    pointer-events: none;
}
.input-icon svg {
    width: 18px;
    height: 18px;
    stroke: rgba(100,130,190,0.8);
    fill: none;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.form-input {
    width: 100%;
    padding: 14px 16px 14px 44px;
    background: rgba(255,255,255,0.92);
    border: 1.5px solid rgba(200,215,245,0.5);
    border-radius: 12px;
    font-size: 14px;
    font-family: inherit;
    color: #1a2a4a;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.form-input::placeholder { color: rgba(120,140,180,0.7); }
.form-input:focus {
    outline: none;
    border-color: rgba(60,120,220,0.6);
    box-shadow: 0 0 0 3.5px rgba(60,120,220,0.18);
    background: #fff;
}

/* eye toggle */
.eye-toggle {
    position: absolute;
    right: 14px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    color: rgba(100,130,190,0.7);
    display: flex;
    align-items: center;
}
.eye-toggle svg {
    width: 18px;
    height: 18px;
    stroke: rgba(100,130,190,0.8);
    fill: none;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* ===================== BUTTONS ===================== */
.btn-primary {
    width: 100%;
    margin-top: 8px;
    padding: 15px 20px;
    border: none;
    border-radius: 14px;
    background: linear-gradient(135deg, #1e56b0 0%, #0f3d8a 100%);
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    font-family: inherit;
    letter-spacing: 0.2px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 6px 20px rgba(15,60,140,0.45);
    transition: transform 0.2s, box-shadow 0.2s;
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(15,60,140,0.55);
}
.btn-primary:active { transform: translateY(0); }
.btn-primary svg {
    width: 17px;
    height: 17px;
    stroke: #fff;
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* OR divider */
.or-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 18px 0;
    color: rgba(255,255,255,0.5);
    font-size: 12px;
    font-weight: 500;
}
.or-divider::before,
.or-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(255,255,255,0.18);
}

/* SSO button */
.btn-sso {
    width: 100%;
    padding: 14px 20px;
    border: 1.5px solid rgba(255,255,255,0.28);
    border-radius: 14px;
    background: rgba(255,255,255,0.10);
    color: #1d6bba;
    font-size: 14px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    backdrop-filter: blur(8px);
    transition: background 0.2s, border-color 0.2s, transform 0.2s;
    background: rgba(255,255,255,0.88);
}
.btn-sso:hover {
    background: rgba(255,255,255,0.96);
    transform: translateY(-1px);
    border-color: rgba(255,255,255,0.5);
}
.btn-sso svg {
    width: 17px;
    height: 17px;
    stroke: #1d6bba;
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* ===================== ERROR ===================== */
.alert-error {
    margin-bottom: 16px;
    padding: 12px 14px;
    border-radius: 12px;
    background: rgba(220,40,40,0.18);
    border: 1px solid rgba(255,90,90,0.35);
    color: #fff;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.alert-error svg {
    flex-shrink: 0;
    width: 16px;
    height: 16px;
    stroke: #ff8080;
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* ===================== CARD FOOTER ===================== */
.card-footer {
    position: relative;
    z-index: 2;
    margin-top: 22px;
    padding-top: 18px;
    border-top: 1px solid rgba(255,255,255,0.12);
    text-align: center;
}
.footer-icon {
    margin-bottom: 6px;
    opacity: 0.6;
}
.footer-icon svg {
    width: 20px;
    height: 20px;
    stroke: rgba(255,255,255,0.7);
    fill: none;
    stroke-width: 1.5;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.card-footer p {
    color: rgba(255,255,255,0.65);
    font-size: 11.5px;
    line-height: 1.7;
}

/* ===================== BOTTOM BAR ===================== */
.bottom-bar {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    z-index: 10;
    background: rgba(8,20,60,0.82);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-top: 1px solid rgba(255,255,255,0.1);
    padding: 14px 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0;
}
.bar-item {
    display: flex;
    align-items: center;
    gap: 7px;
    color: rgba(255,255,255,0.78);
    font-size: 12px;
    font-weight: 600;
    padding: 0 28px;
}
.bar-item:not(:last-child) {
    border-right: 1px solid rgba(255,255,255,0.15);
}
.bar-item svg {
    width: 16px;
    height: 16px;
    stroke: rgba(180,210,255,0.85);
    fill: none;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* ===================== RESPONSIVE ===================== */
@media (max-width: 560px) {
    .login-card { padding: 32px 24px 28px; }
    .brand-name { font-size: 28px; }
    .shield-svg { width: 90px; height: 105px; }
    .bar-item { padding: 0 14px; font-size: 11px; }
    .slogan-pill { font-size: 12px; padding: 9px 16px; }
}

/* ===================== LOADING STATE ===================== */
.btn-primary.loading {
    pointer-events: none;
    opacity: 0.8;
}
.spinner {
    display: none;
    width: 17px;
    height: 17px;
    border: 2.5px solid rgba(255,255,255,0.35);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}
.btn-primary.loading .spinner { display: inline-block; }
.btn-primary.loading .btn-lock { display: none; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
</head>
<body>

<!-- ===== BACKGROUND ===== -->
<div class="bg-photo"></div>

<!-- Bokeh dots -->
<div class="bg-bokeh" id="bokeh"></div>

<!-- ===== PAGE WRAP ===== -->
<div class="page-wrap">

    <!-- ===== CARD ===== -->
    <div class="login-card">

        <!-- Watermark ring inside card -->
        <div class="card-watermark">
            <div class="watermark-ring"></div>
            <div class="watermark-ring-inner"></div>
            <svg class="watermark-svg" viewBox="0 0 320 320" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <path id="circ-outer" d="M 160,160 m -130,0 a 130,130 0 1,1 260,0 a 130,130 0 1,1 -260,0"/>
                    <path id="circ-inner" d="M 160,160 m -105,0 a 105,105 0 1,1 210,0 a 105,105 0 1,1 -210,0"/>
                </defs>
                <text font-family="Plus Jakarta Sans, sans-serif" font-size="13" font-weight="700" fill="white" letter-spacing="6">
                    <textPath href="#circ-outer" startOffset="0%">KEMENTERIAN IMIGRASI DAN PEMASYARAKATAN &nbsp;&nbsp;</textPath>
                </text>
                <text font-family="Plus Jakarta Sans, sans-serif" font-size="11" font-weight="600" fill="white" letter-spacing="4">
                    <textPath href="#circ-inner" startOffset="0%">KANWIL DITJENPAS RIAU &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</textPath>
                </text>
            </svg>
        </div>

        <!-- ===== LOGO SECTION ===== -->
        <div class="logo-section">

            <!-- Shield logo -->
            <div class="shield-wrap">

                <!-- Logo Kementerian (belakang, transparan) -->
                <img
                    src="logo.png"
                    alt="Logo Kementerian"
                    class="logo-kemen"
                    onerror="this.style.display='none'"
                >

                <!-- Logo SDM (depan) -->
                <svg class="shield-svg" viewBox="0 0 110 128" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Outer shield gold border -->
                    <path d="M55 4 L100 20 L100 64 C100 92 75 114 55 124 C35 114 10 92 10 64 L10 20 Z"
                          fill="#C9933A" />
                    <!-- Inner shield dark bg -->
                    <path d="M55 10 L94 24 L94 64 C94 88 72 109 55 118 C38 109 16 88 16 64 L16 24 Z"
                          fill="#0d1f4f" />
                    <!-- Inner shield subtle gradient -->
                    <path d="M55 10 L94 24 L94 64 C94 88 72 109 55 118 C38 109 16 88 16 64 L16 24 Z"
                          fill="url(#shieldGrad)" opacity="0.4"/>

                    <!-- Tree trunk -->
                    <rect x="52" y="72" width="6" height="22" rx="3" fill="#C9933A"/>
                    <!-- Tree branches left -->
                    <path d="M55 72 C55 72 40 65 36 55" stroke="#C9933A" stroke-width="3.5" stroke-linecap="round" fill="none"/>
                    <path d="M55 78 C55 78 38 74 32 65" stroke="#C9933A" stroke-width="3" stroke-linecap="round" fill="none"/>
                    <!-- Tree branches right -->
                    <path d="M55 72 C55 72 70 65 74 55" stroke="#C9933A" stroke-width="3.5" stroke-linecap="round" fill="none"/>
                    <path d="M55 78 C55 78 72 74 78 65" stroke="#C9933A" stroke-width="3" stroke-linecap="round" fill="none"/>
                    <!-- Tree center branch -->
                    <path d="M55 66 L55 52" stroke="#C9933A" stroke-width="3.5" stroke-linecap="round" fill="none"/>

                    <!-- Flower buds (white circles) -->
                    <circle cx="55" cy="48" r="5" fill="white"/>
                    <circle cx="36" cy="52" r="4.5" fill="white"/>
                    <circle cx="74" cy="52" r="4.5" fill="white"/>
                    <circle cx="30" cy="62" r="4" fill="white"/>
                    <circle cx="80" cy="62" r="4" fill="white"/>
                    <circle cx="43" cy="38" r="4" fill="white"/>
                    <circle cx="67" cy="38" r="4" fill="white"/>

                    <!-- Human figure (roots / person silhouette) -->
                    <circle cx="55" cy="88" r="5" fill="#C9933A"/>

                    <!-- Top banner -->
                    <path d="M24 28 Q55 22 86 28 L84 36 Q55 30 26 36 Z" fill="#C9933A"/>
                    <text x="55" y="34" text-anchor="middle" font-family="Plus Jakarta Sans, sans-serif" font-size="5.5" font-weight="800" fill="#0d1f4f" letter-spacing="0.5">SUMBER DAYA MANUSIA</text>

                    <!-- Bottom banner -->
                    <path d="M22 100 Q55 107 88 100 L86 109 Q55 116 24 109 Z" fill="#C9933A"/>
                    <text x="55" y="107" text-anchor="middle" font-family="Plus Jakarta Sans, sans-serif" font-size="5" font-weight="800" fill="#0d1f4f" letter-spacing="0.3">PEMASYARAKATAN RIAU</text>

                    <defs>
                        <linearGradient id="shieldGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#2a4a8f"/>
                            <stop offset="100%" stop-color="#060f28"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>

            <div class="brand-name">SIKAPAS.RIAU</div>
            <div class="brand-sub">
                Sistem Informasi Kepegawaian dan Administrasi<br>
                Pemasyarakatan Kanwil Riau
            </div>
            <div class="slogan-pill">✦ Satu Sikap, Satu Data PAS Riau ✦</div>
        </div>

        <!-- ===== FORM ===== -->
        <div class="form-body">

            <?php if ($error): ?>
            <div class="alert-error">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">

                <!-- Username -->
                <div class="field-group">
                    <label class="field-label" for="username">Username</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-input"
                            placeholder="Masukkan username"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            autocomplete="username"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- Password -->
                <div class="field-group">
                    <label class="field-label" for="password">Password</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="eye-toggle" id="eyeBtn" title="Tampilkan password" aria-label="Tampilkan password">
                            <svg id="eyeIcon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-primary" id="submitBtn">
                    <svg class="btn-lock" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <div class="spinner"></div>
                    <span class="btn-text">Masuk ke Sistem</span>
                </button>

            </form>

            <!-- OR -->
            <div class="or-divider">atau masuk dengan</div>

            <!-- SSO -->
            <button type="button" class="btn-sso" onclick="window.location.href='sso.php'">
                <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Akun Kanwil / SSO
            </button>

        </div><!-- /form-body -->

        <!-- Card Footer -->
        <div class="card-footer">
            <div class="footer-icon">
                <svg viewBox="0 0 24 24"><path d="M3 22v-2a4 4 0 0 1 4-4h2M9 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM22 16l-4 4-2-2"/></svg>
            </div>
            <p>© 2026 SIKAPAS.RIAU — Kanwil Ditjenpas Riau<br>Kementerian Imigrasi &amp; Pemasyarakatan</p>
        </div>

    </div><!-- /card -->

</div><!-- /page-wrap -->

<!-- ===== BOTTOM BAR ===== -->
<div class="bottom-bar">
    <div class="bar-item">
        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Aman &amp; Terpercaya
    </div>
    <div class="bar-item">
        <svg viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
        Satu Sikap, Satu Data
    </div>
    <div class="bar-item">
        <svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Modern &amp; Integratif
    </div>
</div>

<script>
// ===== BOKEH DOTS =====
(function() {
    const container = document.getElementById('bokeh');
    const count = 35;
    for (let i = 0; i < count; i++) {
        const d = document.createElement('div');
        d.className = 'bokeh-dot';
        const size = Math.random() * 3 + 1.5;
        d.style.cssText = `
            width:${size}px;
            height:${size}px;
            left:${Math.random()*100}%;
            top:${Math.random()*100}%;
            --dur:${(Math.random()*4+3).toFixed(1)}s;
            --delay:-${(Math.random()*5).toFixed(1)}s;
            opacity:${Math.random()*0.4+0.15};
        `;
        container.appendChild(d);
    }
})();

// ===== EYE TOGGLE =====
const eyeBtn  = document.getElementById('eyeBtn');
const pwInput = document.getElementById('password');
const eyeIcon = document.getElementById('eyeIcon');
let shown = false;

eyeBtn.addEventListener('click', () => {
    shown = !shown;
    pwInput.type = shown ? 'text' : 'password';
    eyeIcon.innerHTML = shown
        ? `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
           <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
           <line x1="1" y1="1" x2="23" y2="23"/>`
        : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
});

// ===== LOADING STATE =====
document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.classList.add('loading');
    btn.querySelector('.btn-text').textContent = 'Memproses...';
});
</script>

</body>
</html>
