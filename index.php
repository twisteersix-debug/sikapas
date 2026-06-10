<?php
// ============================================================
//  SIKAPAS.RIAU — Dashboard Utama
//  File: dashboard.php
// ============================================================
require_once 'includes/config.php';
requireLogin();
$user = currentUser();
$db   = getDB();

// ── Data ringkasan ───────────────────────────────────────
$totalPegawai = $db->query("SELECT COUNT(*) FROM pegawai")->fetchColumn();
$totalSatker  = $db->query("SELECT COUNT(*) FROM satker")->fetchColumn();
$totalArsip   = $db->query("SELECT COUNT(*) FROM arsip")->fetchColumn();
$totalKGB     = $db->query("SELECT COUNT(*) FROM kenaikan_gaji")->fetchColumn();

// Distribusi golongan
$golRows = $db->query("
    SELECT
        CASE
            WHEN golongan LIKE 'I/%'   THEN 'Gol I'
            WHEN golongan LIKE 'II/%'  THEN 'Gol II'
            WHEN golongan LIKE 'III/%' THEN 'Gol III'
            WHEN golongan LIKE 'IV/%'  THEN 'Gol IV'
            ELSE 'Lainnya'
        END AS gol_group,
        COUNT(*) AS jml
    FROM pegawai
    GROUP BY gol_group
    ORDER BY gol_group
")->fetchAll();
$golData = ['Gol I'=>0,'Gol II'=>0,'Gol III'=>0,'Gol IV'=>0];
foreach ($golRows as $r) { if (isset($golData[$r['gol_group']])) $golData[$r['gol_group']] = (int)$r['jml']; }

// Distribusi status
$aktif   = $db->query("SELECT COUNT(*) FROM pegawai WHERE status='Aktif'")->fetchColumn();
$pensiun = $db->query("SELECT COUNT(*) FROM pegawai WHERE status='Pensiun'")->fetchColumn();
$proses  = $db->query("SELECT COUNT(*) FROM pegawai WHERE status='Dalam Proses'")->fetchColumn();

// Pegawai per satker (top 6)
$satkerData = $db->query("
    SELECT s.nama, COUNT(p.id) AS jml
    FROM satker s LEFT JOIN pegawai p ON p.satker_id=s.id
    GROUP BY s.id ORDER BY jml DESC LIMIT 6
")->fetchAll();

// Aktivitas terakhir
$logRows = $db->query("
    SELECT aksi, modul, detail, user_nama, created_at
    FROM activity_log ORDER BY created_at DESC LIMIT 8
")->fetchAll();

// Pengingat pensiun terdekat
$pensiunRows = $db->query("
    SELECT p.nama, p.nip, p.jabatan, pg.tgl_pensiun,
           DATEDIFF(pg.tgl_pensiun, NOW()) AS sisa_hari,
           s.nama AS satker
    FROM pengingat pg
    JOIN pegawai p ON p.id=pg.pegawai_id
    LEFT JOIN satker s ON s.id=p.satker_id
    WHERE pg.tgl_pensiun >= NOW()
    ORDER BY pg.tgl_pensiun ASC LIMIT 5
")->fetchAll();

// KGB bulan ini
$kgbBulanIni = $db->query("
    SELECT COUNT(*) FROM kenaikan_gaji
    WHERE MONTH(tmt)=MONTH(NOW()) AND YEAR(tmt)=YEAR(NOW())
")->fetchColumn();

// Satker nama user
$userSatker = '';
if (!isAdmin()) {
    $s = $db->prepare("SELECT s.nama FROM users u JOIN satker s ON s.id=u.satker_id WHERE u.id=?");
    $s->execute([$_SESSION['user_id']]);
    $r = $s->fetch();
    $userSatker = $r['nama'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — SIKAPAS.RIAU</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
/* ═══════════════════════════════════════════════════
   RESET & ROOT
═══════════════════════════════════════════════════ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --navy:#0f1e45;--navy2:#162354;--navy3:#1c2d6b;
  --blue:#1e6fbf;--blue2:#2980d4;--blue-pale:#e8f2fb;
  --gold:#c9933a;--gold2:#e8b04a;
  --green:#16a34a;--green-pale:#dcfce7;
  --red:#dc2626;--red-pale:#fee2e2;
  --orange:#ea7c17;--orange-pale:#fff3e0;
  --purple:#7c3aed;--purple-pale:#ede9fe;
  --white:#fff;
  --gray-50:#f8fafc;--gray-100:#f1f5f9;--gray-200:#e2e8f0;
  --gray-400:#94a3b8;--gray-600:#475569;--gray-800:#1e293b;
  --sidebar-w:240px;
  --header-h:64px;
  --radius:12px;--radius-lg:18px;
  --shadow:0 1px 3px rgba(0,0,0,.08),0 4px 16px rgba(0,0,0,.06);
  --shadow-md:0 4px 12px rgba(0,0,0,.10),0 8px 32px rgba(0,0,0,.08);
}

html,body{height:100%;font-family:'Plus Jakarta Sans',sans-serif;background:var(--gray-50);color:var(--gray-800)}

/* ═══════════════════════════════════════════════════
   LAYOUT SHELL
═══════════════════════════════════════════════════ */
.shell{display:flex;height:100vh;overflow:hidden}

/* ═══════════════════════════════════════════════════
   SIDEBAR
═══════════════════════════════════════════════════ */
.sidebar{
  width:var(--sidebar-w);flex-shrink:0;
  background:linear-gradient(180deg,var(--navy) 0%,var(--navy2) 60%,#0b1733 100%);
  display:flex;flex-direction:column;
  overflow-y:auto;overflow-x:hidden;
  position:relative;z-index:10;
  transition:width .3s;
}
.sidebar::-webkit-scrollbar{width:4px}
.sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:4px}

/* Sidebar header */
.sb-head{
  padding:20px 16px 16px;
  border-bottom:1px solid rgba(255,255,255,.07);
  display:flex;align-items:center;gap:10px;
  flex-shrink:0;
}
.sb-logo{width:44px;height:44px;flex-shrink:0;border-radius:8px;overflow:hidden;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center}
.sb-logo img{width:44px;height:44px;object-fit:contain}
.sb-brand{font-size:15px;font-weight:800;color:#fff;letter-spacing:.5px;line-height:1.2}
.sb-sub{font-size:9.5px;color:rgba(255,255,255,.5);line-height:1.4;margin-top:2px}

/* Nav sections */
.sb-section{padding:16px 14px 6px;font-size:10px;font-weight:700;letter-spacing:1.2px;color:rgba(255,255,255,.35);text-transform:uppercase}

.nav-item{
  display:flex;align-items:center;gap:10px;
  padding:10px 14px;margin:1px 8px;
  border-radius:8px;cursor:pointer;
  color:rgba(255,255,255,.65);font-size:13px;font-weight:500;
  text-decoration:none;transition:all .18s;position:relative;
}
.nav-item:hover{background:rgba(255,255,255,.07);color:#fff}
.nav-item.active{background:var(--blue);color:#fff;font-weight:600;
  box-shadow:0 4px 12px rgba(30,111,191,.4)}
.nav-item svg{width:17px;height:17px;flex-shrink:0;stroke:currentColor;fill:none;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
.nav-item .badge-dot{margin-left:auto;background:#ef4444;color:#fff;border-radius:20px;font-size:10px;font-weight:700;padding:1px 7px;min-width:20px;text-align:center}
.nav-arrow{margin-left:auto;width:14px;height:14px;transition:transform .2s;stroke:currentColor;fill:none;stroke-width:2}
.nav-sub{overflow:hidden;max-height:0;transition:max-height .3s ease}
.nav-sub.open{max-height:200px}
.nav-sub-item{
  display:flex;align-items:center;gap:8px;
  padding:8px 14px 8px 40px;margin:0 8px;
  border-radius:8px;cursor:pointer;
  color:rgba(255,255,255,.5);font-size:12.5px;
  text-decoration:none;transition:all .18s;
}
.nav-sub-item:hover{color:rgba(255,255,255,.85);background:rgba(255,255,255,.05)}
.nav-sub-item.active{color:var(--blue2)}

/* Sidebar footer */
.sb-footer{
  margin-top:auto;padding:16px 14px;
  border-top:1px solid rgba(255,255,255,.07);
  display:flex;align-items:center;gap:10px;
}
/* ════ PERBAIKAN: hapus .sb-footer-logo agar tidak ada gambar duplikat ════ */
.sb-footer-text{font-size:9px;color:rgba(255,255,255,.4);line-height:1.6}
.sb-footer-text strong{display:block;font-size:10px;color:rgba(255,255,255,.6);font-weight:700}

/* ═══════════════════════════════════════════════════
   MAIN AREA
═══════════════════════════════════════════════════ */
.main{flex:1;display:flex;flex-direction:column;min-width:0;overflow:hidden}

/* ── Topbar ── */
.topbar{
  height:var(--header-h);flex-shrink:0;
  background:#fff;border-bottom:1px solid var(--gray-200);
  display:flex;align-items:center;padding:0 28px;gap:16px;
  position:sticky;top:0;z-index:5;
  box-shadow:0 1px 0 var(--gray-200);
}
.topbar-left{display:flex;align-items:center;gap:12px;flex:1}
.menu-toggle{background:none;border:none;cursor:pointer;padding:6px;border-radius:8px;color:var(--gray-600);transition:background .15s}
.menu-toggle:hover{background:var(--gray-100)}
.menu-toggle svg{width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2;display:block}
.topbar-title{font-size:18px;font-weight:700;color:var(--navy)}
.topbar-right{display:flex;align-items:center;gap:8px}

/* Notif bell */
.icon-btn{
  position:relative;width:40px;height:40px;border-radius:10px;
  display:flex;align-items:center;justify-content:center;
  background:none;border:none;cursor:pointer;color:var(--gray-600);
  transition:background .15s;
}
.icon-btn:hover{background:var(--gray-100)}
.icon-btn svg{width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.notif-badge{position:absolute;top:6px;right:6px;width:16px;height:16px;background:#ef4444;border-radius:50%;font-size:9px;font-weight:700;color:#fff;display:flex;align-items:center;justify-content:center;border:2px solid #fff}

/* User profile topbar */
.user-pill{
  display:flex;align-items:center;gap:10px;
  padding:6px 12px 6px 6px;border-radius:32px;
  background:var(--gray-50);border:1px solid var(--gray-200);
  cursor:pointer;transition:background .15s;
  position:relative;
}
.user-pill:hover{background:var(--gray-100)}
.user-ava{
  width:34px;height:34px;border-radius:50%;
  background:linear-gradient(135deg,var(--blue),var(--navy3));
  display:flex;align-items:center;justify-content:center;
  font-weight:700;font-size:14px;color:#fff;flex-shrink:0;
  overflow:hidden;
}
.user-ava img{width:34px;height:34px;object-fit:cover;border-radius:50%}
.user-info{line-height:1.3}
.user-name{font-size:13px;font-weight:700;color:var(--gray-800)}
.user-role{font-size:11px;color:var(--gray-400)}
.user-caret{width:14px;height:14px;stroke:var(--gray-400);fill:none;stroke-width:2.5}
.user-dropdown{
  position:absolute;top:calc(100%+8px);right:0;
  background:#fff;border-radius:12px;min-width:200px;
  box-shadow:var(--shadow-md);border:1px solid var(--gray-200);
  overflow:hidden;display:none;z-index:50;
}
.user-pill.open .user-dropdown{display:block}
.dd-item{display:flex;align-items:center;gap:10px;padding:11px 16px;font-size:13px;color:var(--gray-800);text-decoration:none;transition:background .15s;cursor:pointer;border:none;background:none;width:100%;font-family:inherit}
.dd-item:hover{background:var(--gray-50)}
.dd-item svg{width:15px;height:15px;stroke:var(--gray-400);fill:none;stroke-width:2}
.dd-divider{height:1px;background:var(--gray-100);margin:4px 0}
.dd-item.red{color:#dc2626}
.dd-item.red svg{stroke:#dc2626}
.dd-item.red:hover{background:#fff5f5}

/* ── Content ── */
.content{flex:1;overflow-y:auto;padding:24px 28px 40px}
.content::-webkit-scrollbar{width:6px}
.content::-webkit-scrollbar-thumb{background:var(--gray-200);border-radius:6px}

/* ═══════════════════════════════════════════════════
   HERO BANNER
═══════════════════════════════════════════════════ */
.hero{
  background:linear-gradient(130deg,var(--navy) 0%,var(--navy3) 55%,var(--blue) 100%);
  border-radius:var(--radius-lg);
  padding:28px 32px;
  display:flex;align-items:center;gap:24px;
  margin-bottom:24px;position:relative;overflow:hidden;
}
.hero::before{
  content:'';position:absolute;right:-40px;top:-60px;
  width:300px;height:300px;border-radius:50%;
  background:rgba(255,255,255,.03);
}
.hero::after{
  content:'';position:absolute;right:80px;bottom:-80px;
  width:220px;height:220px;border-radius:50%;
  background:rgba(255,255,255,.04);
}

/* ════ PERBAIKAN WATERMARK ════
   - z-index:0  → berada di belakang semua elemen hero
   - Posisi digeser agar tidak menimpa hero-avatar
   - Ukuran diperkecil sedikit agar proporsional
*/
.hero-wm{
  position:absolute;
  left:50%;                    /* tengah horizontal */
  top:50%;
  transform:translate(-50%,-50%);
  width:160px;height:160px;
  opacity:.05;
  pointer-events:none;
  z-index:0;                   /* PALING BELAKANG */
}
.hero-wm img{width:100%;height:100%;object-fit:contain;filter:brightness(10)}

/* ════ PERBAIKAN AVATAR ════
   z-index:2 → selalu di atas watermark
*/
.hero-avatar{
  width:64px;height:64px;border-radius:50%;flex-shrink:0;
  background:rgba(255,255,255,.15);border:3px solid rgba(255,255,255,.25);
  display:flex;align-items:center;justify-content:center;
  font-size:26px;font-weight:800;color:#fff;
  position:relative;z-index:2;  /* di atas watermark */
}
.hero-body{position:relative;z-index:2;flex:1}
.hero-greeting{font-size:13px;color:rgba(255,255,255,.7);font-weight:500;margin-bottom:4px}
.hero-name{font-size:26px;font-weight:800;color:#fff;letter-spacing:.3px;display:flex;align-items:center;gap:10px;margin-bottom:2px}
.hero-name svg{width:20px;height:20px;stroke:var(--gold);fill:none;stroke-width:2;flex-shrink:0}
.hero-role{font-size:13px;color:rgba(255,255,255,.6);margin-bottom:12px}
.hero-slogan{
  display:inline-block;padding:6px 16px;
  border-radius:50px;border:1px solid rgba(255,255,255,.2);
  background:rgba(255,255,255,.1);
  font-size:12px;font-weight:600;color:rgba(255,255,255,.85);font-style:italic;
}
.hero-meta{
  position:relative;z-index:2;
  display:flex;gap:0;border-left:1px solid rgba(255,255,255,.12);
  padding-left:28px;margin-left:auto;
}
.hero-meta-item{
  display:flex;flex-direction:column;align-items:center;gap:4px;
  padding:0 20px;border-right:1px solid rgba(255,255,255,.12);
}
.hero-meta-item:last-child{border-right:none}
.hero-meta-icon{color:rgba(255,255,255,.5)}
.hero-meta-icon svg{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:1.8}
.hero-meta-label{font-size:10px;color:rgba(255,255,255,.5);font-weight:600;text-transform:uppercase;letter-spacing:.5px}
.hero-meta-val{font-size:14px;font-weight:700;color:#fff;text-align:center;line-height:1.3}

/* ═══════════════════════════════════════════════════
   STAT CARDS ROW
═══════════════════════════════════════════════════ */
.stat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
.stat-card{
  background:#fff;border-radius:var(--radius);
  padding:20px;border:1px solid var(--gray-200);
  box-shadow:var(--shadow);
  display:flex;align-items:flex-start;gap:16px;
  position:relative;overflow:hidden;
}
.stat-icon{
  width:52px;height:52px;border-radius:12px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
}
.stat-icon svg{width:26px;height:26px;stroke:#fff;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.si-blue{background:linear-gradient(135deg,#1e6fbf,#2980d4)}
.si-green{background:linear-gradient(135deg,#16a34a,#22c55e)}
.si-orange{background:linear-gradient(135deg,#ea7c17,#f59e0b)}
.si-purple{background:linear-gradient(135deg,#7c3aed,#a855f7)}
.stat-body{flex:1;min-width:0}
.stat-label{font-size:11.5px;color:var(--gray-400);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
.stat-value{font-size:30px;font-weight:800;color:var(--gray-800);line-height:1;margin-bottom:6px}
.stat-trend{font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px}
.trend-up{color:var(--green)}
.trend-same{color:var(--gray-400)}
.trend-down{color:var(--red)}
.stat-spark{position:absolute;bottom:0;right:0;opacity:.12;pointer-events:none}

/* ═══════════════════════════════════════════════════
   GRID: 3-column content area
═══════════════════════════════════════════════════ */
.grid-3{display:grid;grid-template-columns:1fr 1fr 1.1fr;gap:20px;margin-bottom:20px}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}

/* ── Card base ── */
.card{
  background:#fff;border-radius:var(--radius);
  border:1px solid var(--gray-200);box-shadow:var(--shadow);
  overflow:hidden;
}
.card-head{
  display:flex;align-items:center;justify-content:space-between;
  padding:16px 20px;border-bottom:1px solid var(--gray-100);
}
.card-title{font-size:14px;font-weight:700;color:var(--gray-800)}
.card-link{font-size:12px;font-weight:600;color:var(--blue);text-decoration:none;padding:5px 12px;border-radius:6px;background:var(--blue-pale);transition:background .15s}
.card-link:hover{background:#d0e7f7}
.card-body{padding:20px}

/* ═══════════════════════════════════════════════════
   CHART CARDS
═══════════════════════════════════════════════════ */
.chart-wrap{position:relative;height:220px}

/* Donut center label */
.donut-center{
  position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
  text-align:center;pointer-events:none;
}
.donut-val{font-size:26px;font-weight:800;color:var(--gray-800)}
.donut-lbl{font-size:11px;color:var(--gray-400);font-weight:600}

/* Legend */
.legend{display:flex;flex-direction:column;gap:8px;padding:0 20px 16px}
.legend-item{display:flex;align-items:center;justify-content:space-between;font-size:13px}
.legend-dot-wrap{display:flex;align-items:center;gap:8px}
.legend-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.legend-name{color:var(--gray-600);font-weight:500}
.legend-pct{font-weight:700;color:var(--gray-800)}
.legend-count{font-size:11px;color:var(--gray-400);font-weight:500}

/* ═══════════════════════════════════════════════════
   PENGUMUMAN / AKTIVITAS
═══════════════════════════════════════════════════ */
.notif-list{display:flex;flex-direction:column;gap:0}
.notif-item{
  display:flex;align-items:flex-start;gap:12px;
  padding:14px 20px;border-bottom:1px solid var(--gray-100);
  transition:background .15s;
}
.notif-item:last-child{border-bottom:none}
.notif-item:hover{background:var(--gray-50)}
.notif-ico{
  width:36px;height:36px;border-radius:9px;flex-shrink:0;
  background:var(--blue-pale);display:flex;align-items:center;justify-content:center;
  margin-top:1px;
}
.notif-ico svg{width:17px;height:17px;stroke:var(--blue);fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.notif-body{flex:1;min-width:0}
.notif-title{font-size:13px;font-weight:600;color:var(--gray-800);margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.notif-sub{font-size:12px;color:var(--gray-400)}
.notif-date{font-size:11px;color:var(--gray-400);white-space:nowrap;margin-top:2px}

/* ═══════════════════════════════════════════════════
   AKTIVITAS LOG
═══════════════════════════════════════════════════ */
.log-list{display:flex;flex-direction:column;gap:0}
.log-item{
  display:flex;align-items:center;gap:12px;
  padding:12px 20px;border-bottom:1px solid var(--gray-100);
  transition:background .15s;
}
.log-item:last-child{border-bottom:none}
.log-item:hover{background:var(--gray-50)}
.log-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;background:var(--blue)}
.log-body{flex:1;min-width:0}
.log-title{font-size:12.5px;font-weight:600;color:var(--gray-800)}
.log-by{font-size:11.5px;color:var(--gray-400)}
.log-time{font-size:11px;color:var(--gray-400);white-space:nowrap;flex-shrink:0}

/* ═══════════════════════════════════════════════════
   SATKER BAR CHART (CSS-only)
═══════════════════════════════════════════════════ */
.satker-bars{display:flex;flex-direction:column;gap:12px;padding:0 20px 16px}
.satker-bar-item{}
.satker-bar-label{display:flex;justify-content:space-between;margin-bottom:5px}
.satker-bar-name{font-size:12px;color:var(--gray-600);font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:70%}
.satker-bar-count{font-size:12px;font-weight:700;color:var(--gray-800)}
.satker-bar-track{height:8px;background:var(--gray-100);border-radius:99px;overflow:hidden}
.satker-bar-fill{height:100%;border-radius:99px;background:linear-gradient(90deg,var(--blue),var(--blue2));transition:width .6s ease}

/* ═══════════════════════════════════════════════════
   PENSIUN TABLE
═══════════════════════════════════════════════════ */
.mini-table{width:100%;border-collapse:collapse}
.mini-table th{
  background:var(--gray-50);padding:9px 16px;
  font-size:11px;font-weight:700;color:var(--gray-400);
  text-align:left;text-transform:uppercase;letter-spacing:.4px;
}
.mini-table td{padding:11px 16px;font-size:12.5px;border-bottom:1px solid var(--gray-100);color:var(--gray-800)}
.mini-table tr:last-child td{border-bottom:none}
.mini-table tr:hover td{background:var(--gray-50)}
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600}
.badge-red{background:var(--red-pale);color:var(--red)}
.badge-orange{background:var(--orange-pale);color:var(--orange)}
.badge-green{background:var(--green-pale);color:var(--green)}
.badge-blue{background:var(--blue-pale);color:var(--blue)}

/* ═══════════════════════════════════════════════════
   KELENGKAPAN DATA (progress bars)
═══════════════════════════════════════════════════ */
.progress-list{display:flex;flex-direction:column;gap:14px;padding:0 20px 16px}
.progress-item{}
.progress-label{display:flex;justify-content:space-between;margin-bottom:5px}
.progress-name{font-size:13px;color:var(--gray-600);font-weight:500}
.progress-pct{font-size:13px;font-weight:700;color:var(--gray-800)}
.progress-track{height:9px;background:var(--gray-100);border-radius:99px;overflow:hidden}
.progress-fill{height:100%;border-radius:99px;transition:width .8s ease}
.pf-blue{background:linear-gradient(90deg,#1e6fbf,#60a5fa)}
.pf-green{background:linear-gradient(90deg,#16a34a,#4ade80)}
.pf-orange{background:linear-gradient(90deg,#ea7c17,#fbbf24)}
.pf-purple{background:linear-gradient(90deg,#7c3aed,#c084fc)}

/* ═══════════════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════════════ */
@media(max-width:1280px){
  .stat-row{grid-template-columns:repeat(2,1fr)}
  .grid-3{grid-template-columns:1fr 1fr}
}
@media(max-width:900px){
  .sidebar{width:0;overflow:hidden}
  .sidebar.mob-open{width:var(--sidebar-w)}
  .hero-meta{display:none}
  .stat-row{grid-template-columns:repeat(2,1fr)}
  .grid-3{grid-template-columns:1fr}
  .grid-2{grid-template-columns:1fr}
  .content{padding:16px}
}
@media(max-width:560px){
  .stat-row{grid-template-columns:1fr}
  .hero{padding:20px}
  .hero-name{font-size:20px}
}
</style>
</head>
<body>
<div class="shell">

<!-- ═══════════════════ SIDEBAR ═══════════════════ -->
<aside class="sidebar" id="sidebar">

  <div class="sb-head">
    <div class="sb-logo">
      <img src="logo.png" alt="SIKAPAS" onerror="this.parentElement.innerHTML='🏛️'">
    </div>
    <div>
      <div class="sb-brand">SIKAPAS.RIAU</div>
      <div class="sb-sub">Sistem Informasi Kepegawaian<br>dan Administrasi Pemasyarakatan</div>
    </div>
  </div>

  <div class="sb-section">Menu Utama</div>

  <a href="dashboard.php" class="nav-item active">
    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
    Dashboard
  </a>

  <div class="nav-item" onclick="toggleSub('sub-pegawai',this)">
    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    Pegawai
    <svg class="nav-arrow" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
  </div>
  <div class="nav-sub" id="sub-pegawai">
    <a href="pegawai_satker.php" class="nav-sub-item">Data per Satker</a>
    <a href="index.php" class="nav-sub-item">Semua Pegawai</a>
    <a href="pengingat.php" class="nav-sub-item">Pengingat Pensiun</a>
  </div>

  <div class="nav-item" onclick="toggleSub('sub-arsip',this)">
    <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    Arsip Kepegawaian
    <svg class="nav-arrow" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
  </div>
  <div class="nav-sub" id="sub-arsip">
    <a href="dokumen.php" class="nav-sub-item">Dokumen Pegawai</a>
    <a href="arsip.php" class="nav-sub-item">Arsip File</a>
  </div>

  <a href="satker.php" class="nav-item">
    <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    Satker
  </a>

  <div class="nav-item" onclick="toggleSub('sub-lap',this)">
    <svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
    Laporan
    <svg class="nav-arrow" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
  </div>
  <div class="nav-sub" id="sub-lap">
    <a href="index.php" class="nav-sub-item">KGB</a>
    <a href="index.php" class="nav-sub-item">SLKS / SKP</a>
    <a href="index.php" class="nav-sub-item">Tunjangan</a>
  </div>

  <?php if (isAdmin()): ?>
  <div class="sb-section">Pengaturan</div>
  <a href="users.php" class="nav-item">
    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
    Kelola User
  </a>
  <a href="aktivitas.php" class="nav-item">
    <svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
    Log Aktivitas
  </a>
  <?php endif; ?>

  <div class="sb-section">Informasi</div>
  <a href="#" class="nav-item">
    <svg viewBox="0 0 24 24"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>
    Pengumuman
    <span class="badge-dot">2</span>
  </a>
  <a href="#" class="nav-item">
    <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    Kalender Kegiatan
  </a>
  <a href="#" class="nav-item">
    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    Bantuan
  </a>

  <!-- ════ Sidebar footer: hanya teks, tanpa gambar duplikat ════ -->
  <div class="sb-footer">
    <div class="sb-footer-text">
      <strong>KEMENTERIAN IMIGRASI<br>DAN PEMASYARAKATAN</strong>
      REPUBLIK INDONESIA<br>
      © 2026 SIKAPAS.RIAU
    </div>
  </div>

</aside><!-- /sidebar -->

<!-- ═══════════════════ MAIN ═══════════════════ -->
<div class="main">

  <!-- ── Topbar ── -->
  <header class="topbar">
    <div class="topbar-left">
      <button class="menu-toggle" onclick="toggleSidebar()" title="Toggle menu">
        <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <span class="topbar-title">Dashboard</span>
    </div>
    <div class="topbar-right">
      <!-- Mode gelap (cosmetic) -->
      <button class="icon-btn" title="Mode Gelap">
        <svg viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      </button>
      <!-- Notifikasi -->
      <button class="icon-btn" title="Notifikasi">
        <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <span class="notif-badge">3</span>
      </button>
      <!-- User profile -->
      <div class="user-pill" id="userPill" onclick="toggleUser(event)">
        <div class="user-ava"><?= htmlspecialchars($user['inisial'] ?? 'A') ?></div>
        <div class="user-info">
          <div class="user-name"><?= htmlspecialchars($user['nama'] ?? 'Admin') ?></div>
          <div class="user-role"><?= isAdmin() ? 'Administrator' : ucfirst($user['role'] ?? 'Operator') ?></div>
        </div>
        <svg class="user-caret" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
        <div class="user-dropdown">
          <div style="padding:14px 16px 10px;border-bottom:1px solid var(--gray-100)">
            <div style="font-size:13px;font-weight:700;color:var(--gray-800)"><?= htmlspecialchars($user['nama'] ?? '') ?></div>
            <div style="font-size:11px;color:var(--gray-400)"><?= isAdmin() ? 'Administrator' : ucfirst($user['role'] ?? '') ?></div>
            <?php if ($userSatker): ?><div style="font-size:11px;color:var(--blue);font-weight:600">🏢 <?= htmlspecialchars($userSatker) ?></div><?php endif; ?>
          </div>
          <a href="profile.php" class="dd-item">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            Profil Saya
          </a>
          <div class="dd-divider"></div>
          <a href="logout.php" class="dd-item red">
            <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Keluar
          </a>
        </div>
      </div>
    </div>
  </header>

  <!-- ── Content ── -->
  <div class="content">

    <!-- HERO BANNER -->
    <div class="hero">

      <!-- ════ WATERMARK: ditempatkan PERTAMA agar berada di belakang secara HTML stacking,
           diperkuat dengan z-index:0 via CSS ════ -->
      <div class="hero-wm">
        <img src="logo.png" alt="" onerror="this.style.display='none'">
      </div>

      <!-- ════ LOGO SDM (avatar inisial): z-index:2, selalu di atas watermark ════ -->
      <div class="hero-avatar"><?= htmlspecialchars($user['inisial'] ?? 'A') ?></div>

      <div class="hero-body">
        <div class="hero-greeting">Selamat Datang Kembali,</div>
        <div class="hero-name">
          <?= strtoupper(htmlspecialchars($user['nama'] ?? 'Admin')) ?>
          <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <div class="hero-role"><?= isAdmin() ? 'Administrator' : ucfirst($user['role'] ?? 'Operator') ?><?= $userSatker ? ' — '.$userSatker : '' ?></div>
        <div class="hero-slogan">✦ Satu Sikap, Satu Data PAS Riau ✦</div>
      </div>
      <div class="hero-meta">
        <div class="hero-meta-item">
          <div class="hero-meta-icon"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
          <div class="hero-meta-label">Hari ini</div>
          <div class="hero-meta-val"><?= date('d M Y') ?><br><span style="font-size:11px;font-weight:500;opacity:.7"><?= ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][date('w')] ?></span></div>
        </div>
        <div class="hero-meta-item">
          <div class="hero-meta-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
          <div class="hero-meta-label">Waktu</div>
          <div class="hero-meta-val" id="clock">--:--:--</div>
        </div>
        <div class="hero-meta-item">
          <div class="hero-meta-icon"><svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
          <div class="hero-meta-label">Lokasi</div>
          <div class="hero-meta-val">Kanwil Ditjenpas<br><span style="font-size:11px;opacity:.7">Riau</span></div>
        </div>
      </div>
    </div><!-- /hero -->

    <!-- STAT CARDS -->
    <div class="stat-row">
      <div class="stat-card">
        <div class="stat-icon si-blue">
          <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-body">
          <div class="stat-label">Total Pegawai</div>
          <div class="stat-value"><?= number_format($totalPegawai) ?></div>
          <div class="stat-trend trend-up">
            <svg width="12" height="12" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
            <?= $aktif ?> aktif
          </div>
        </div>
        <svg class="stat-spark" width="80" height="40" viewBox="0 0 80 40"><polyline points="0,35 15,28 30,32 45,18 60,22 75,10 80,14" stroke="#1e6fbf" stroke-width="2" fill="none" opacity="0.5"/></svg>
      </div>
      <div class="stat-card">
        <div class="stat-icon si-green">
          <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </div>
        <div class="stat-body">
          <div class="stat-label">Satker</div>
          <div class="stat-value"><?= $totalSatker ?></div>
          <div class="stat-trend trend-same">
            <svg width="12" height="12" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tidak berubah
          </div>
        </div>
        <svg class="stat-spark" width="80" height="40" viewBox="0 0 80 40"><polyline points="0,20 20,20 40,20 60,20 80,20" stroke="#16a34a" stroke-width="2" fill="none" opacity="0.5"/></svg>
      </div>
      <div class="stat-card">
        <div class="stat-icon si-orange">
          <svg viewBox="0 0 24 24"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
        </div>
        <div class="stat-body">
          <div class="stat-label">Arsip Aktif</div>
          <div class="stat-value"><?= number_format($totalArsip) ?></div>
          <div class="stat-trend trend-up">
            <svg width="12" height="12" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
            Dokumen tersimpan
          </div>
        </div>
        <svg class="stat-spark" width="80" height="40" viewBox="0 0 80 40"><polyline points="0,38 15,30 30,34 45,20 60,25 75,12 80,16" stroke="#ea7c17" stroke-width="2" fill="none" opacity="0.5"/></svg>
      </div>
      <div class="stat-card">
        <div class="stat-icon si-purple">
          <svg viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        </div>
        <div class="stat-body">
          <div class="stat-label">KGB Bulan Ini</div>
          <div class="stat-value"><?= $kgbBulanIni ?></div>
          <div class="stat-trend trend-up">
            <svg width="12" height="12" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
            Kenaikan gaji berkala
          </div>
        </div>
        <svg class="stat-spark" width="80" height="40" viewBox="0 0 80 40"><polyline points="0,32 15,28 30,22 45,26 60,16 75,10 80,13" stroke="#7c3aed" stroke-width="2" fill="none" opacity="0.5"/></svg>
      </div>
    </div><!-- /stat-row -->

    <!-- ROW: Charts + Pengumuman -->
    <div class="grid-3">

      <!-- Donut: Komposisi Status -->
      <div class="card">
        <div class="card-head">
          <span class="card-title">Komposisi Pegawai</span>
        </div>
        <div class="card-body" style="padding-bottom:4px">
          <div class="chart-wrap" style="height:200px">
            <canvas id="chartStatus"></canvas>
            <div class="donut-center">
              <div class="donut-val"><?= $totalPegawai ?></div>
              <div class="donut-lbl">Pegawai</div>
            </div>
          </div>
        </div>
        <div class="legend">
          <div class="legend-item">
            <div class="legend-dot-wrap"><div class="legend-dot" style="background:#16a34a"></div><span class="legend-name">Aktif</span></div>
            <div style="display:flex;align-items:center;gap:8px">
              <span class="legend-count"><?= $aktif ?></span>
              <span class="legend-pct"><?= $totalPegawai ? round($aktif/$totalPegawai*100) : 0 ?>%</span>
            </div>
          </div>
          <div class="legend-item">
            <div class="legend-dot-wrap"><div class="legend-dot" style="background:#dc2626"></div><span class="legend-name">Pensiun</span></div>
            <div style="display:flex;align-items:center;gap:8px">
              <span class="legend-count"><?= $pensiun ?></span>
              <span class="legend-pct"><?= $totalPegawai ? round($pensiun/$totalPegawai*100) : 0 ?>%</span>
            </div>
          </div>
          <div class="legend-item">
            <div class="legend-dot-wrap"><div class="legend-dot" style="background:#f59e0b"></div><span class="legend-name">Dalam Proses</span></div>
            <div style="display:flex;align-items:center;gap:8px">
              <span class="legend-count"><?= $proses ?></span>
              <span class="legend-pct"><?= $totalPegawai ? round($proses/$totalPegawai*100) : 0 ?>%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Bar: Distribusi Golongan -->
      <div class="card">
        <div class="card-head">
          <span class="card-title">Pegawai per Golongan</span>
        </div>
        <div class="card-body">
          <div class="chart-wrap" style="height:220px">
            <canvas id="chartGol"></canvas>
          </div>
        </div>
      </div>

      <!-- Pengumuman -->
      <div class="card">
        <div class="card-head">
          <span class="card-title">Pengumuman Terbaru</span>
          <a href="#" class="card-link">Lihat Semua</a>
        </div>
        <div class="notif-list">
          <div class="notif-item">
            <div class="notif-ico"><svg viewBox="0 0 24 24"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3z"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></div>
            <div class="notif-body">
              <div class="notif-title">Jadwal Penilaian Kinerja (SKP) Semester I</div>
              <div class="notif-sub">Dimulai 1 Juni – 30 Juni <?= date('Y') ?></div>
            </div>
            <div class="notif-date"><?= date('d M') ?></div>
          </div>
          <div class="notif-item">
            <div class="notif-ico" style="background:var(--green-pale)"><svg viewBox="0 0 24 24" style="stroke:#16a34a"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
            <div class="notif-body">
              <div class="notif-title">Pemutakhiran Data Pegawai</div>
              <div class="notif-sub">Segera lengkapi data profil Anda</div>
            </div>
            <div class="notif-date"><?= date('d M', strtotime('-2 day')) ?></div>
          </div>
          <div class="notif-item">
            <div class="notif-ico" style="background:var(--orange-pale)"><svg viewBox="0 0 24 24" style="stroke:#ea7c17"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg></div>
            <div class="notif-body">
              <div class="notif-title">Pengajuan Kenaikan Pangkat</div>
              <div class="notif-sub">Periode April – Juni <?= date('Y') ?></div>
            </div>
            <div class="notif-date"><?= date('d M', strtotime('-5 day')) ?></div>
          </div>
          <div class="notif-item">
            <div class="notif-ico" style="background:var(--purple-pale)"><svg viewBox="0 0 24 24" style="stroke:#7c3aed"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
            <div class="notif-body">
              <div class="notif-title">Rapat Koordinasi Kepegawaian</div>
              <div class="notif-sub">Kanwil Ditjenpas Riau, <?= date('d M Y', strtotime('+3 day')) ?></div>
            </div>
            <div class="notif-date"><?= date('d M', strtotime('-7 day')) ?></div>
          </div>
        </div>
      </div>

    </div><!-- /grid-3 -->

    <!-- ROW: Satker bars + Kelengkapan + Aktivitas -->
    <div class="grid-3" style="margin-bottom:20px">

      <!-- Satker bar -->
      <div class="card">
        <div class="card-head">
          <span class="card-title">Pegawai per Satker</span>
          <a href="pegawai_satker.php" class="card-link">Lihat Detail</a>
        </div>
        <div class="satker-bars" style="padding-top:16px">
          <?php
          $maxSatker = $satkerData ? max(array_column($satkerData,'jml')) : 1;
          foreach ($satkerData as $s):
            $pct = $maxSatker > 0 ? round($s['jml']/$maxSatker*100) : 0;
          ?>
          <div class="satker-bar-item">
            <div class="satker-bar-label">
              <span class="satker-bar-name" title="<?= htmlspecialchars($s['nama']) ?>">
                <?= htmlspecialchars(mb_substr($s['nama'],0,28)) ?>
              </span>
              <span class="satker-bar-count"><?= $s['jml'] ?></span>
            </div>
            <div class="satker-bar-track">
              <div class="satker-bar-fill" style="width:<?= $pct ?>%"></div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php if (empty($satkerData)): ?>
          <div style="text-align:center;color:var(--gray-400);padding:1.5rem;font-size:13px">Belum ada data</div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Kelengkapan data -->
      <div class="card">
        <div class="card-head">
          <span class="card-title">Kelengkapan Data Pegawai</span>
          <a href="pegawai_satker.php" class="card-link">Lihat Detail</a>
        </div>
        <?php
        $total = max(1, $totalPegawai);
        $hasNip     = $db->query("SELECT COUNT(*) FROM pegawai WHERE nip IS NOT NULL AND nip!='' ")->fetchColumn();
        $hasJabatan = $db->query("SELECT COUNT(*) FROM pegawai WHERE jabatan IS NOT NULL AND jabatan!='' ")->fetchColumn();
        $hasKTP     = $db->query("SELECT COUNT(*) FROM pegawai WHERE no_ktp IS NOT NULL AND no_ktp!='' ")->fetchColumn();
        $hasLahir   = $db->query("SELECT COUNT(*) FROM pegawai WHERE tanggal_lahir IS NOT NULL")->fetchColumn();
        $pNip     = round($hasNip/$total*100);
        $pJabatan = round($hasJabatan/$total*100);
        $pKTP     = round($hasKTP/$total*100);
        $pLahir   = round($hasLahir/$total*100);
        ?>
        <div class="progress-list" style="padding-top:16px">
          <div class="progress-item">
            <div class="progress-label"><span class="progress-name">Data NIP</span><span class="progress-pct"><?= $pNip ?>%</span></div>
            <div class="progress-track"><div class="progress-fill pf-blue" style="width:<?= $pNip ?>%"></div></div>
          </div>
          <div class="progress-item">
            <div class="progress-label"><span class="progress-name">Data Jabatan</span><span class="progress-pct"><?= $pJabatan ?>%</span></div>
            <div class="progress-track"><div class="progress-fill pf-green" style="width:<?= $pJabatan ?>%"></div></div>
          </div>
          <div class="progress-item">
            <div class="progress-label"><span class="progress-name">No. KTP</span><span class="progress-pct"><?= $pKTP ?>%</span></div>
            <div class="progress-track"><div class="progress-fill pf-orange" style="width:<?= $pKTP ?>%"></div></div>
          </div>
          <div class="progress-item">
            <div class="progress-label"><span class="progress-name">Tanggal Lahir</span><span class="progress-pct"><?= $pLahir ?>%</span></div>
            <div class="progress-track"><div class="progress-fill pf-purple" style="width:<?= $pLahir ?>%"></div></div>
          </div>
        </div>
      </div>

      <!-- Aktivitas terakhir -->
      <div class="card">
        <div class="card-head">
          <span class="card-title">Aktivitas Terakhir</span>
          <?php if(isAdmin()): ?><a href="aktivitas.php" class="card-link">Lihat Semua</a><?php endif; ?>
        </div>
        <div class="log-list">
          <?php if ($logRows): ?>
          <?php foreach ($logRows as $l):
            $waktu = date('H:i', strtotime($l['created_at']));
          ?>
          <div class="log-item">
            <div class="log-dot"></div>
            <div class="log-body">
              <div class="log-title"><?= htmlspecialchars($l['aksi'].' '.$l['modul']) ?></div>
              <div class="log-by">oleh <?= htmlspecialchars($l['user_nama'] ?? '-') ?></div>
            </div>
            <div class="log-time"><?= $waktu ?> WIB</div>
          </div>
          <?php endforeach; ?>
          <?php else: ?>
          <div style="text-align:center;color:var(--gray-400);padding:2rem;font-size:13px">Belum ada aktivitas</div>
          <?php endif; ?>
        </div>
      </div>

    </div><!-- /grid-3 -->

    <!-- ROW: Pengingat Pensiun full-width -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-head">
        <span class="card-title">⏳ Pengingat Pensiun Terdekat</span>
        <a href="pengingat.php" class="card-link">Lihat Semua</a>
      </div>
      <?php if ($pensiunRows): ?>
      <div style="overflow-x:auto">
        <table class="mini-table">
          <thead>
            <tr>
              <th>Nama Pegawai</th>
              <th>NIP</th>
              <th>Jabatan</th>
              <th>Satker</th>
              <th>Tgl Pensiun</th>
              <th>Sisa Hari</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pensiunRows as $p):
              $sisa = (int)$p['sisa_hari'];
              if ($sisa <= 90)      { $badgeCls = 'badge-red';    $badgeTxt = '⚠️ Sangat Dekat'; }
              elseif ($sisa <= 180) { $badgeCls = 'badge-orange'; $badgeTxt = '⚡ Dekat'; }
              elseif ($sisa <= 365) { $badgeCls = 'badge-blue';   $badgeTxt = '📅 Dalam 1 Tahun'; }
              else                  { $badgeCls = 'badge-green';  $badgeTxt = '✓ Masih Lama'; }
            ?>
            <tr>
              <td style="font-weight:600"><?= htmlspecialchars($p['nama']) ?></td>
              <td style="font-family:monospace;font-size:12px"><?= htmlspecialchars($p['nip']) ?></td>
              <td><?= htmlspecialchars($p['jabatan'] ?? '-') ?></td>
              <td><?= htmlspecialchars($p['satker'] ?? '-') ?></td>
              <td><?= date('d M Y', strtotime($p['tgl_pensiun'])) ?></td>
              <td style="font-weight:700;color:<?= $sisa<=90?'var(--red)':($sisa<=180?'var(--orange)':'var(--gray-800)') ?>"><?= $sisa ?> hari</td>
              <td><span class="badge <?= $badgeCls ?>"><?= $badgeTxt ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div style="text-align:center;padding:2.5rem;color:var(--gray-400);font-size:13px">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block;margin:0 auto 12px;color:var(--gray-300)"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Tidak ada pengingat pensiun dalam waktu dekat
      </div>
      <?php endif; ?>
    </div>

    <!-- Footer -->
    <div style="text-align:center;font-size:12px;color:var(--gray-400);padding-top:8px">
      SIKAPAS.RIAU — Sistem Informasi Kepegawaian dan Administrasi Pemasyarakatan — Kanwil Ditjenpas Riau
    </div>

  </div><!-- /content -->
</div><!-- /main -->
</div><!-- /shell -->

<script>
// ── Jam Real-time ──────────────────────────────────
function updateClock() {
  const now = new Date();
  const h = String(now.getHours()).padStart(2,'0');
  const m = String(now.getMinutes()).padStart(2,'0');
  const s = String(now.getSeconds()).padStart(2,'0');
  const el = document.getElementById('clock');
  if (el) el.textContent = `${h}:${m}:${s}`;
}
setInterval(updateClock, 1000);
updateClock();

// ── Sidebar toggle ─────────────────────────────────
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('mob-open');
}

// ── Nav sub-menu ───────────────────────────────────
function toggleSub(id, el) {
  const sub = document.getElementById(id);
  const isOpen = sub.classList.contains('open');
  document.querySelectorAll('.nav-sub').forEach(s => s.classList.remove('open'));
  document.querySelectorAll('.nav-arrow').forEach(a => a.style.transform = '');
  if (!isOpen) {
    sub.classList.add('open');
    el.querySelector('.nav-arrow').style.transform = 'rotate(180deg)';
  }
}

// ── User dropdown ──────────────────────────────────
function toggleUser(e) {
  e.stopPropagation();
  document.getElementById('userPill').classList.toggle('open');
}
document.addEventListener('click', () => {
  document.getElementById('userPill')?.classList.remove('open');
});

// ── Chart: Donut Status ────────────────────────────
const ctxStatus = document.getElementById('chartStatus').getContext('2d');
new Chart(ctxStatus, {
  type: 'doughnut',
  data: {
    labels: ['Aktif','Pensiun','Dalam Proses'],
    datasets:[{
      data: [<?= $aktif ?>, <?= $pensiun ?>, <?= $proses ?>],
      backgroundColor: ['#16a34a','#dc2626','#f59e0b'],
      borderWidth: 0,
      hoverOffset: 6,
    }]
  },
  options: {
    cutout: '72%',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => ` ${ctx.label}: ${ctx.raw} pegawai`
        }
      }
    }
  }
});

// ── Chart: Bar Golongan ────────────────────────────
const ctxGol = document.getElementById('chartGol').getContext('2d');
new Chart(ctxGol, {
  type: 'bar',
  data: {
    labels: ['Gol I','Gol II','Gol III','Gol IV'],
    datasets:[{
      label: 'Pegawai',
      data: [<?= implode(',', array_values($golData)) ?>],
      backgroundColor: ['rgba(30,111,191,0.75)','rgba(30,111,191,0.75)','rgba(30,111,191,0.75)','rgba(30,111,191,0.75)'],
      hoverBackgroundColor: '#1e6fbf',
      borderRadius: 6,
      borderSkipped: false,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => ` ${ctx.raw} pegawai`
        }
      }
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: { font: { size: 12, family: 'Plus Jakarta Sans' }, color: '#64748b' }
      },
      y: {
        grid: { color: '#f1f5f9' },
        ticks: { font: { size: 11, family: 'Plus Jakarta Sans' }, color: '#94a3b8' },
        beginAtZero: true,
      }
    }
  }
});
</script>
</body>
</html>
