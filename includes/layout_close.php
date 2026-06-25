  </div><!-- /content -->
</div><!-- /main -->
</div><!-- /shell -->

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
/* ── Sidebar toggle ────────────────────────── */
function toggleSidebar() {
  var sb = document.getElementById('sidebar');
  var ov = document.getElementById('mobOverlay');
  if (window.innerWidth <= 900) {
    var open = sb.classList.toggle('mob-open');
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
  var sub   = document.getElementById(id);
  var arrow = el.querySelector('.nav-arrow');
  var isOpen = sub.classList.contains('open');
  // tutup semua sub-menu dulu
  document.querySelectorAll('.nav-sub').forEach(function(s) { s.classList.remove('open'); });
  document.querySelectorAll('.nav-arrow').forEach(function(a) { a.classList.remove('rotated'); });
  if (!isOpen) {
    sub.classList.add('open');
    if (arrow) arrow.classList.add('rotated');
  }
}

/* ── User dropdown — PERBAIKAN PRESISI ────── */
var _userDropdownTimer = null;

function toggleUser(e) {
  e.stopPropagation();
  var pill = document.getElementById('userPill');
  if (!pill) return;

  var isOpen = pill.classList.contains('open');

  // Debounce 120ms: cegah double-toggle akibat klik cepat
  if (_userDropdownTimer) {
    clearTimeout(_userDropdownTimer);
    _userDropdownTimer = null;
  }

  if (isOpen) {
    pill.classList.remove('open');
    pill.setAttribute('aria-expanded', 'false');
  } else {
    pill.classList.add('open');
    pill.setAttribute('aria-expanded', 'true');
  }
}

// Tutup dropdown saat klik di luar
document.addEventListener('click', function() {
  var pill = document.getElementById('userPill');
  if (pill && pill.classList.contains('open')) {
    pill.classList.remove('open');
    pill.setAttribute('aria-expanded', 'false');
  }
});

// Keyboard: Enter/Space buka, Escape tutup
document.addEventListener('keydown', function(e) {
  var pill = document.getElementById('userPill');
  if (!pill) return;

  // Jika fokus di pill
  if (document.activeElement === pill) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      pill.click();
    }
  }

  // Escape tutup dropdown & modal
  if (e.key === 'Escape') {
    if (pill.classList.contains('open')) {
      pill.classList.remove('open');
      pill.setAttribute('aria-expanded', 'false');
      pill.focus();
    }
    document.querySelectorAll('.modal-overlay.open').forEach(function(m) {
      m.classList.remove('open');
    });
  }
});

/* ── Modal helpers ──────────────────────────── */
function openModal(id) {
  var el = document.getElementById(id);
  if (el) el.classList.add('open');
}
function closeModal(id) {
  var el = document.getElementById(id);
  if (el) el.classList.remove('open');
}
function closeModalOutside(e, id) {
  if (e.target === document.getElementById(id)) closeModal(id);
}

/* ── Toast ──────────────────────────────────── */
function showToast(msg, type) {
  type = type || 'success';
  var el = document.getElementById('toast');
  if (!el) return;
  el.textContent = msg;
  el.className = 'toast ' + type + ' show';
  clearTimeout(el._timer);
  el._timer = setTimeout(function() { el.classList.remove('show'); }, 3000);
}
function toast(msg, type) { showToast(msg, type); }
</script>
</body>
</html>
