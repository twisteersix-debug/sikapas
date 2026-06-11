<?php
// ============================================================
//  SIKAPAS.RIAU — Layout Footer / Penutup
//  File: includes/layout_close.php
//  Letakkan di paling bawah setiap halaman
// ============================================================
?>
<!-- ↓↓↓ Content ditutup di sini ↓↓↓ -->
  </div><!-- /content -->
</div><!-- /main -->
</div><!-- /shell -->

<!-- Toast wrapper -->
<div class="toast-wrap" id="toastWrap"></div>

<!-- Sidebar overlay (mobile) -->
<div id="sb-overlay" onclick="sidebarToggle()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:19"></div>

<script>
/* ── Sidebar ── */
function sidebarToggle() {
  const sb = document.getElementById('sidebar');
  const ov = document.getElementById('sb-overlay');
  const isMob = window.innerWidth <= 900;
  if (isMob) {
    const open = sb.classList.toggle('mob-open');
    ov.style.display = open ? 'block' : 'none';
  } else {
    sb.classList.toggle('collapsed');
  }
}

/* ── Nav sub-menu ── */
function toggleNav(id, el) {
  const sub   = document.getElementById(id);
  const arrow = el.querySelector('.nav-arrow');
  const isOpen = sub.classList.contains('open');
  document.querySelectorAll('.nav-sub').forEach(s => s.classList.remove('open'));
  document.querySelectorAll('.nav-arrow').forEach(a => a.classList.remove('open'));
  if (!isOpen) {
    sub.classList.add('open');
    arrow?.classList.add('open');
  }
}

/* ── User dropdown ── */
function toggleUser(e) {
  e.stopPropagation();
  document.getElementById('userPill').classList.toggle('open');
}
document.addEventListener('click', () => {
  document.getElementById('userPill')?.classList.remove('open');
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.getElementById('userPill')?.classList.remove('open');
});

/* ── Toast ── */
function showToast(msg, type='success') {
  const wrap = document.getElementById('toastWrap');
  const t = document.createElement('div');
  const icons = {
    success: '<svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>',
    error:   '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
    warning: '<svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
  };
  t.className = `toast ${type}`;
  t.innerHTML = (icons[type]||icons.success) + msg;
  wrap.appendChild(t);
  requestAnimationFrame(() => { requestAnimationFrame(() => t.classList.add('show')); });
  setTimeout(() => {
    t.classList.remove('show');
    setTimeout(() => t.remove(), 350);
  }, 3000);
}

/* ── Modal helpers ── */
function openModal(id)  { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }
function closeModalOutside(e, id) { if (e.target === document.getElementById(id)) closeModal(id); }
</script>
