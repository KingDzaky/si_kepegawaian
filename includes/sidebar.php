<?php
// includes/sidebar.php
?>

<link rel="stylesheet" href="css/sidebar.css">

<style>
/* ============================================================
   SIDEBAR STYLES
   Fix: icon tidak rata saat collapsed
============================================================ */

/* ===== MAIN CONTENT OFFSET ===== */
.main-content {
  margin-left: 260px;
  margin-top: 70px;
  transition: margin-left 0.3s ease;
  padding: 20px;
}

.sidebar.collapsed ~ .main-content {
  margin-left: 60px;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 0 !important;
    margin-top: 60px;
  }
}

/* ===== SIDEBAR BASE ===== */
.sidebar {
  position: fixed;
  top: 70px;
  left: 0;
  width: 260px;
  height: calc(100vh - 70px);
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
  color: #ecf0f1;
  transition: width 0.3s ease;
  z-index: 1040;
  overflow-y: auto;
  overflow-x: hidden;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.sidebar::-webkit-scrollbar { width: 6px; }
.sidebar::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); }
.sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 3px; }
.sidebar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.3); }

/* ===== SIDEBAR INNER ===== */
.sidebar-inner {
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: 0;
}

/* ===== SIDEBAR HEADER ===== */
.sidebar-header {
  padding: 20px 15px;
  background: rgba(0, 0, 0, 0.2);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 10px;
  text-align: center;
  overflow: hidden;
  white-space: nowrap;
}

.sidebar-header h5 {
  margin: 0;
  font-size: 14px;
  letter-spacing: 1px;
  color: #ecf0f1;
  transition: opacity 0.3s ease, visibility 0.3s ease;
}

.text-gradient {
  background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.header-line {
  width: 60px;
  height: 3px;
  background: linear-gradient(90deg, #3b82f6, #60a5fa);
  margin: 10px auto 0;
  border-radius: 2px;
  transition: opacity 0.3s ease, visibility 0.3s ease;
}

/* ===== SIDEBAR NAV ===== */
.sidebar-nav {
  flex: 1;
  padding: 10px 0;
}

.sidebar-nav .nav {
  padding: 0;
  margin: 0;
  list-style: none;
}

.sidebar-nav .nav-item {
  margin-bottom: 4px;
  position: relative;
}

/* ===== SIDEBAR LINK - override Bootstrap nav-link ===== */
.sidebar .nav-link.sidebar-link {
  display: flex;
  align-items: center;
  padding: 12px 20px !important; /* override Bootstrap */
  color: #bdc3c7;
  text-decoration: none;
  transition: background 0.3s ease, color 0.3s ease, border-color 0.3s ease, padding 0.3s ease;
  position: relative;
  overflow: hidden;
  border-left: 3px solid transparent;
}

.sidebar .nav-link.sidebar-link:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #fff;
  border-left-color: #3b82f6;
  padding-left: 25px !important;
}

.sidebar .nav-link.sidebar-link.active {
  background: rgba(59, 130, 246, 0.1);
  color: #fff;
  border-left-color: #3b82f6;
  font-weight: 600;
}

/* ===== LINK CONTENT ===== */
.link-content {
  display: flex;
  align-items: center;
  position: relative;
  z-index: 2;
  width: 100%;
  overflow: hidden;
}

/* ===== NAV ICON ===== */
.nav-icon {
  width: 20px;
  min-width: 20px; /* ✅ Fix: agar icon tidak menyusut */
  font-size: 18px;
  margin-right: 12px;
  color: #3b82f6;
  transition: transform 0.3s ease, color 0.3s ease, margin 0.3s ease;
  text-align: center;
}

.sidebar-link:hover .nav-icon {
  transform: scale(1.1);
  color: #60a5fa;
}

/* ===== NAV TEXT ===== */
.nav-text {
  font-size: 14px;
  font-weight: 500;
  white-space: nowrap;
  transition: opacity 0.3s ease, visibility 0.3s ease;
  overflow: hidden;
}

/* ===== HOVER EFFECT ===== */
.link-hover-effect {
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
  transition: left 0.5s ease;
  pointer-events: none;
}

.sidebar-link:hover .link-hover-effect {
  left: 100%;
}

/* ===== LOGO SECTION ===== */
.logo-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 30px 20px;
  margin-top: auto;
  background: rgba(0, 0, 0, 0.2);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  transition: padding 0.3s ease;
  overflow: hidden;
}

.logo-container {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100px;
  height: 100px;
  margin-bottom: 15px;
  transition: width 0.3s ease, height 0.3s ease, margin 0.3s ease;
  flex-shrink: 0;
}

.logo-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  position: relative;
  z-index: 2;
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
  transition: transform 0.3s ease;
}

.logo-container:hover .logo-img {
  transform: scale(1.05);
}

.logo-shadow {
  position: absolute;
  width: 100%;
  height: 100%;
  background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, transparent 70%);
  border-radius: 50%;
  z-index: 1;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 0.5; }
  50% { transform: scale(1.1); opacity: 0.8; }
}

.logo-text {
  margin: 0;
  font-size: 13px;
  font-weight: 600;
  color: #ecf0f1;
  text-align: center;
  letter-spacing: 0.5px;
  line-height: 1.4;
  transition: opacity 0.3s ease, visibility 0.3s ease;
  white-space: nowrap;
}

/* ===== SUBMENU ===== */
.nav-item.has-submenu .submenu {
  display: none;
  list-style: none;
  padding-left: 0;
  margin: 0;
  background: rgba(255, 255, 255, 0.05);
  overflow: hidden;
}

.nav-item.has-submenu.active .submenu {
  display: block;
}

.submenu-item { margin: 0; }

.submenu-link {
  display: flex;
  align-items: center;
  padding: 10px 20px 10px 50px;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  font-size: 0.9em;
  transition: background 0.3s ease, color 0.3s ease, padding 0.3s ease;
}

.submenu-link:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  padding-left: 55px;
}

.submenu-link i {
  margin-right: 10px;
  font-size: 0.85em;
  width: 16px;
}

.submenu-arrow {
  margin-left: auto;
  font-size: 0.8em;
  transition: transform 0.3s ease;
  flex-shrink: 0;
}

.nav-item.has-submenu.active .submenu-arrow {
  transform: rotate(180deg);
}

/* ============================================================
   COLLAPSED STATE - FIX UTAMA ICON TIDAK RATA
============================================================ */
.sidebar.collapsed {
  width: 60px;
}

/* Sembunyikan teks & elemen yang tidak perlu */
.sidebar.collapsed .sidebar-header h5,
.sidebar.collapsed .header-line,
.sidebar.collapsed .nav-text,
.sidebar.collapsed .logo-text {
  opacity: 0;
  visibility: hidden;
  width: 0;
  overflow: hidden;
}

/* ✅ FIX: sidebar-link center saat collapsed */
.sidebar.collapsed .nav-link.sidebar-link {
  padding: 12px 0 !important;
  justify-content: center;
  border-left-color: transparent;
}

.sidebar.collapsed .nav-link.sidebar-link:hover {
  padding: 12px 0 !important; /* ✅ Tidak geser saat hover */
  border-left-color: #3b82f6;
  background: rgba(255, 255, 255, 0.05);
}

/* ✅ FIX: link-content center */
.sidebar.collapsed .link-content {
  justify-content: center;
  width: 100%;
}

/* ✅ FIX: icon margin hilang & benar-benar center */
.sidebar.collapsed .nav-icon {
  margin-right: 0 !important;
  width: 20px;
  min-width: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ✅ FIX: sembunyikan submenu-arrow (ini penyebab icon geser) */
.sidebar.collapsed .submenu-arrow {
  display: none !important;
}

/* ✅ FIX: sembunyikan submenu saat collapsed */
.sidebar.collapsed .submenu {
  display: none !important;
}

/* Logo kecil saat collapsed */
.sidebar.collapsed .logo-section {
  padding: 15px 0;
}

.sidebar.collapsed .logo-container {
  width: 36px;
  height: 36px;
  margin-bottom: 0;
}

/* ===== TOOLTIP saat collapsed ===== */
.sidebar.collapsed .nav-item::after {
  content: attr(data-tooltip);
  position: absolute;
  left: 65px;
  top: 50%;
  transform: translateY(-50%);
  background: #2c3e50;
  color: #fff;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 13px;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.2s ease, visibility 0.2s ease;
  pointer-events: none;
  z-index: 9999;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.sidebar.collapsed .nav-item:hover::after {
  opacity: 1;
  visibility: visible;
}

/* ===== VERSION BADGE ===== */
.version-badge {
  text-align: center;
  padding: 8px;
  font-size: 11px;
  color: rgba(255, 255, 255, 0.4);
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

/* ===== MOBILE RESPONSIVE ===== */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
    top: 60px;
    height: calc(100vh - 60px);
    width: 260px !important; /* selalu full di mobile */
    transition: transform 0.3s ease;
  }

  .sidebar.show {
    transform: translateX(0);
  }

  /* Reset collapsed styles di mobile */
  .sidebar.collapsed .nav-link.sidebar-link {
    padding: 12px 20px !important;
    justify-content: flex-start;
  }

  .sidebar.collapsed .nav-link.sidebar-link:hover {
    padding-left: 25px !important;
  }

  .sidebar.collapsed .link-content {
    justify-content: flex-start;
  }

  .sidebar.collapsed .nav-icon {
    margin-right: 12px !important;
    min-width: 20px;
  }

  .sidebar.collapsed .nav-text,
  .sidebar.collapsed .sidebar-header h5,
  .sidebar.collapsed .header-line,
  .sidebar.collapsed .logo-text {
    opacity: 1;
    visibility: visible;
    width: auto;
    overflow: visible;
  }

  .sidebar.collapsed .logo-container {
    width: 100px;
    height: 100px;
    margin-bottom: 15px;
  }

  .sidebar.collapsed .logo-section {
    padding: 30px 20px;
  }

  .sidebar.collapsed .submenu-arrow {
    display: inline-block !important;
  }
}
</style>

<!-- Sidebar -->
<aside id="sidebar" class="sidebar">
  <div class="sidebar-inner">

    <!-- Header -->
    <div class="sidebar-header">
      <h5 class="text-uppercase fw-bold text-gradient">MENU</h5>
      <div class="header-line"></div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
      <ul class="nav flex-column">

        <!-- Dashboard -->
        <li class="nav-item" data-tooltip="Dashboard">
          <a class="nav-link sidebar-link" href="dashboard.php">
            <div class="link-content">
              <i class="fas fa-home nav-icon"></i>
              <span class="nav-text">Dashboard</span>
            </div>
            <div class="link-hover-effect"></div>
          </a>
        </li>

        <?php if (isAdmin()): ?>

        <!-- Kepala OPD -->
        <li class="nav-item" data-tooltip="Kepala OPD">
          <a class="nav-link sidebar-link" href="kepala_opd.php">
            <div class="link-content">
              <i class="fas fa-user-tie nav-icon"></i>
              <span class="nav-text">Kepala OPD</span>
            </div>
            <div class="link-hover-effect"></div>
          </a>
        </li>

        <!-- Data DUK -->
        <li class="nav-item" data-tooltip="Data DUK">
          <a class="nav-link sidebar-link" href="dataduk.php">
            <div class="link-content">
              <i class="fas fa-users nav-icon"></i>
              <span class="nav-text">Data DUK</span>
            </div>
            <div class="link-hover-effect"></div>
          </a>
        </li>

        <!-- Tambah DUK -->
        <li class="nav-item" data-tooltip="Tambah DUK">
          <a class="nav-link sidebar-link" href="form_tambah_duk.php">
            <div class="link-content">
              <i class="fas fa-user-plus nav-icon"></i>
              <span class="nav-text">Tambah DUK</span>
            </div>
            <div class="link-hover-effect"></div>
          </a>
        </li>

        <!-- Data Penyuluh -->
        <li class="nav-item" data-tooltip="Data Penyuluh">
          <a class="nav-link sidebar-link" href="penyuluh.php">
            <div class="link-content">
              <i class="fas fa-chalkboard-teacher nav-icon"></i>
              <span class="nav-text">Data Penyuluh</span>
            </div>
            <div class="link-hover-effect"></div>
          </a>
        </li>

        <!-- Usulan Kenaikan Pangkat -->
        <li class="nav-item has-submenu" data-tooltip="Kenaikan Pangkat">
          <a class="nav-link sidebar-link" href="#" onclick="toggleSubmenu(event, this)">
            <div class="link-content">
              <i class="fas fa-file-export nav-icon"></i>
              <span class="nav-text">Usulan Kenaikan Pangkat</span>
              <i class="fas fa-chevron-down submenu-arrow"></i>
            </div>
            <div class="link-hover-effect"></div>
          </a>
          <ul class="submenu">
            <li class="submenu-item">
              <a href="kenaikan_pangkat.php" class="submenu-link">
                <i class="fas fa-file-alt"></i>
                <span>Daftar Usulan</span>
              </a>
            </li>
            <li class="submenu-item">
              <a href="list_surat_pernyataan_disiplin.php" class="submenu-link">
                <i class="fas fa-file-word"></i>
                <span>Surat Keterangan</span>
              </a>
            </li>
            <li class="submenu-item">
              <a href="list_surat_lainnya.php" class="submenu-link">
                <i class="fas fa-file-word"></i>
                <span>Surat HukDis</span>
              </a>
            </li>
          </ul>
        </li>

        <!-- Usulan Satya Lencana -->
        <li class="nav-item has-submenu" data-tooltip="Satya Lencana">
          <a class="nav-link sidebar-link" href="#" onclick="toggleSubmenu(event, this)">
            <div class="link-content">
              <i class="fas fa-medal nav-icon"></i>
              <span class="nav-text">Usulan Satya Lencana</span>
              <i class="fas fa-chevron-down submenu-arrow"></i>
            </div>
            <div class="link-hover-effect"></div>
          </a>
          <ul class="submenu">
            <li class="submenu-item">
              <a href="list_surat_satya_lencana.php" class="submenu-link">
                <i class="fas fa-file-word"></i>
                <span>Surat Satya Lencana</span>
              </a>
            </li>
          </ul>
        </li>

        <!-- Usulan Pensiun -->
        <li class="nav-item has-submenu" data-tooltip="Pensiun Pegawai">
          <a class="nav-link sidebar-link" href="#" onclick="toggleSubmenu(event, this)">
            <div class="link-content">
              <i class="fas fa-user-graduate nav-icon"></i>
              <span class="nav-text">Usulan Pensiun Pegawai</span>
              <i class="fas fa-chevron-down submenu-arrow"></i>
            </div>
            <div class="link-hover-effect"></div>
          </a>
          <ul class="submenu">
            <li class="submenu-item">
              <a href="usulan_pensiun.php" class="submenu-link">
                <i class="fas fa-list-alt"></i>
                <span>Daftar Usulan</span>
              </a>
            </li>
          </ul>
        </li>

        <?php endif; ?>

        <?php if (isSuperAdmin()): ?>
        <!-- Data User -->
        <li class="nav-item" data-tooltip="Data User">
          <a class="nav-link sidebar-link" href="users.php">
            <div class="link-content">
              <i class="fas fa-user-cog nav-icon"></i>
              <span class="nav-text">Data User</span>
            </div>
            <div class="link-hover-effect"></div>
          </a>
        </li>
        <?php endif; ?>

        <?php if (isKepalaDinas()): ?>
        <!-- Laporan -->
        <li class="nav-item" data-tooltip="Laporan">
          <a class="nav-link sidebar-link" href="laporan.php">
            <div class="link-content">
              <i class="fas fa-file-alt nav-icon"></i>
              <span class="nav-text">Laporan</span>
            </div>
            <div class="link-hover-effect"></div>
          </a>
        </li>

        <!-- Approval -->
        <li class="nav-item" data-tooltip="Approval">
          <a class="nav-link sidebar-link" href="approval.php">
            <div class="link-content">
              <i class="fas fa-check-circle nav-icon"></i>
              <span class="nav-text">Approval</span>
            </div>
            <div class="link-hover-effect"></div>
          </a>
        </li>
        <?php endif; ?>

      </ul>
    </nav>

    <!-- Logo Section -->
    <div class="logo-section">
      <div class="logo-container">
        <img src="assets/img/logo.png" alt="Logo Kota Banjarmasin" class="logo-img"
             onerror="this.src='https://via.placeholder.com/100x100/3b82f6/ffffff?text=Logo'">
        <div class="logo-shadow"></div>
      </div>
      <p class="logo-text">DPPKBPM Kota Banjarmasin</p>
    </div>

  </div>
</aside>

<script src="js/scripts.js"></script>

<script>
// Set active link berdasarkan halaman aktif
document.addEventListener('DOMContentLoaded', function () {
  const currentPage = window.location.pathname.split('/').pop() || 'dashboard.php';
  document.querySelectorAll('.sidebar-link').forEach(link => {
    const href = link.getAttribute('href');
    if (href === currentPage || (currentPage === '' && href === 'dashboard.php')) {
      link.classList.add('active');
      // Buka submenu jika link aktif ada di dalam submenu
      const parentSubmenu = link.closest('.has-submenu');
      if (parentSubmenu) parentSubmenu.classList.add('active');
    }
  });
});

// Toggle submenu
function toggleSubmenu(event, element) {
  event.preventDefault();
  const navItem = element.closest('.nav-item');
  document.querySelectorAll('.nav-item.has-submenu').forEach(item => {
    if (item !== navItem) item.classList.remove('active');
  });
  navItem.classList.toggle('active');
}

// Tutup submenu saat klik di luar sidebar
document.addEventListener('click', function (event) {
  const sidebar = document.querySelector('.sidebar');
  if (sidebar && !sidebar.contains(event.target)) {
    document.querySelectorAll('.nav-item.has-submenu.active').forEach(item => {
      item.classList.remove('active');
    });
  }
});
</script>