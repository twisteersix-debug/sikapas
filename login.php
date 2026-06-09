<?php
// ============================================================
// SIKAPAS.RIAU — LOGIN PREMIUM
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

        $stmt = $db->prepare("
            SELECT *
            FROM users
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->execute([$username]);

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];

            $_SESSION['user'] = [
                'id'      => $user['id'],
                'nama'    => $user['nama'],
                'role'    => $user['role'],
                'inisial' => strtoupper(substr($user['nama'],0,1))
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

<title>SIKAPAS.RIAU</title>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    font-family:'Plus Jakarta Sans',sans-serif;

    min-height:100vh;

    display:flex;
    justify-content:center;
    align-items:center;

    padding:20px;

    background:
    linear-gradient(
        rgba(6,20,55,.80),
        rgba(6,20,55,.80)
    ),
    linear-gradient(
        135deg,
        #0d1f4f,
        #1d3f86,
        #1e6fbf
    );

    background-attachment:fixed;
}

.card{

    position:relative;

    width:100%;
    max-width:650px;

    padding:45px;

    border-radius:30px;

    background:rgba(255,255,255,.12);

    border:1px solid rgba(255,255,255,.18);

    backdrop-filter:blur(18px);
    -webkit-backdrop-filter:blur(18px);

    box-shadow:
    0 30px 80px rgba(0,0,0,.35),
    inset 0 1px 0 rgba(255,255,255,.15);
}

.logo-section{

    position:relative;

    text-align:center;

    margin-bottom:30px;
}

.watermark{

    position:absolute;

    left:50%;
    top:-40px;

    transform:translateX(-50%);

    width:320px;

    opacity:.08;

    filter:
    grayscale(100%)
    brightness(2);

    z-index:1;

    pointer-events:none;
}

.main-logo{

    position:relative;

    z-index:2;

    width:130px;

    margin-bottom:10px;
}

.brand{

    position:relative;
    z-index:2;

    font-size:42px;

    font-weight:800;

    color:#ffffff;

    letter-spacing:.5px;
}

.tagline{

    position:relative;
    z-index:2;

    margin-top:10px;

    color:rgba(255,255,255,.85);

    font-size:14px;

    line-height:1.6;
}

.tagline-hero{

    position:relative;
    z-index:2;

    margin-top:20px;

    padding:12px 16px;

    border-radius:14px;

    background:rgba(255,255,255,.12);

    border:1px solid rgba(255,255,255,.15);

    color:#fff;

    font-style:italic;

    font-weight:600;
}

.divider{

    height:1px;

    background:rgba(255,255,255,.12);

    margin:30px 0;
}

h2{

    color:#fff;

    margin-bottom:20px;

    font-size:22px;
}

.form-group{

    margin-bottom:18px;
}

label{

    display:block;

    margin-bottom:8px;

    color:#ffffff;

    font-size:13px;

    font-weight:600;
}

input{

    width:100%;

    padding:15px 16px;

    border:none;

    border-radius:12px;

    background:rgba(255,255,255,.95);

    font-size:15px;

    font-family:inherit;
}

input:focus{

    outline:none;

    box-shadow:
    0 0 0 4px rgba(30,111,191,.25);
}

.btn{

    width:100%;

    padding:16px;

    margin-top:5px;

    border:none;

    border-radius:14px;

    cursor:pointer;

    font-size:16px;

    font-weight:700;

    color:#fff;

    background:
    linear-gradient(
        135deg,
        #1e6fbf,
        #0f4fa0
    );

    transition:.25s;
}

.btn:hover{

    transform:translateY(-2px);

    box-shadow:
    0 12px 25px rgba(0,0,0,.25);
}

.error{

    margin-bottom:18px;

    padding:12px 14px;

    border-radius:12px;

    background:
    rgba(255,60,60,.15);

    border:
    1px solid rgba(255,80,80,.35);

    color:#fff;
}

.footer-tag{

    margin-top:25px;

    padding-top:20px;

    border-top:1px solid rgba(255,255,255,.1);

    text-align:center;

    color:rgba(255,255,255,.75);

    font-size:12px;

    line-height:1.6;
}

@media(max-width:768px){

    .card{
        padding:25px;
    }

    .brand{
        font-size:30px;
    }

    .main-logo{
        width:95px;
    }

    .watermark{
        width:220px;
    }

    .tagline{
        font-size:13px;
    }
}

</style>

</head>
<body>

<div class="card">

```
<div class="logo-section">

    <img
        src="imipas-shadow.png"
        class="watermark"
        alt="IMIPAS">

    <img
        src="logo.png"
        class="main-logo"
        alt="SIKAPAS">

    <div class="brand">
        SIKAPAS.RIAU
    </div>

    <div class="tagline">
        Sistem Informasi Kepegawaian dan Administrasi<br>
        Pemasyarakatan Kanwil Riau
    </div>

    <div class="tagline-hero">
        ✦ Satu Sikap, Satu Data PAS Riau ✦
    </div>

</div>

<div class="divider"></div>

<h2>Masuk ke Sistem</h2>

<?php if($error): ?>
    <div class="error">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST">

    <div class="form-group">
        <label>Username</label>
        <input
            type="text"
            name="username"
            placeholder="Masukkan username"
            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
            required
            autofocus>
    </div>

    <div class="form-group">
        <label>Password</label>
        <input
            type="password"
            name="password"
            placeholder="Masukkan password"
            required>
    </div>

    <button type="submit" class="btn">
        Masuk ke Sistem
    </button>

</form>

<div class="footer-tag">
    © 2026 SIKAPAS.RIAU — Kanwil Ditjenpas Riau<br>
    Kementerian Imigrasi dan Pemasyarakatan
</div>
```

</div>

</body>
</html>
