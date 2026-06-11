  </div><!-- /content -->
</div><!-- /main -->
</div><!-- /shell -->

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
/* ── Sidebar toggle ────────────────────────── */
function toggleSidebar() {
  const sb = document.getElementById('sidebar');
  const ov = document.getElementById('mobOverlay');
  if (window.innerWidth <= 900) {
    const open = sb.classList.toggle('mob-open');
    ov.classList.toggle('show', open);
  } else {
    sb.classList.toggle('collapsed');
  }
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('mob-open');
  document.getElementById('mobOverlay').classList.remove('show');
}

/* ── Sub-menu accordion ────────────────────── */
function toggleSub(id, el) {
  const sub   = document.getElementById(id);
  const arrow = el.querySelector('.nav-arrow');
  const isOpen = sub.classList.contains('open');
  // tutup semua
  document.querySelectorAll('.nav-sub').forEach(s => s.classList.remove('open'));
  document.querySelectorAll('.nav-arrow').forEach(a => a.classList.remove('rotated'));
  if (!isOpen) {
    sub.classList.add('open');
    if (arrow) arrow.classList.add('rotated');
  }
}

/* ── User dropdown ──────────────────────────── */
function toggleUser(e) {
  e.stopPropagation();
  document.getElementById('userPill').classList.toggle('open');
}
document.addEventListener('click', () => {
  document.getElementById('userPill')?.classList.remove('open');
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.getElementById('userPill')?.classList.remove('open');
    document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
  }
});

/* ── Modal helpers ──────────────────────────── */
function openModal(id)  { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }
function closeModalOutside(e, id) {
  if (e.target === document.getElementById(id)) closeModal(id);
}

/* ── Toast ──────────────────────────────────── */
function showToast(msg, type = 'success') {
  const el = document.getElementById('toast');
  if (!el) return;
  el.textContent = msg;
  el.className = 'toast ' + type + ' show';
  clearTimeout(el._timer);
  el._timer = setTimeout(() => el.classList.remove('show'), 3000);
}
// alias
function toast(msg, type='success') { showToast(msg, type); }
</script>
</body>
</html>
