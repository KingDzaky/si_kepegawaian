<?php
// includes/header.php
// Cek apakah session sudah dimulai, jika belum baru start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/koneksi.php';
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>SISTEM INFORMASI - Kepegawaian</title>

  <!-- Bootstrap & FontAwesome & DataTables CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="css/header.css">
  <link rel="stylesheet" href="css/sidebar.css">
   <!-- SweetAlert2 CSS -->
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <!-- xlsxJs, jsPDF & jsPDF-AutoTable CDN for PDF Export -->
  <script src="https://unpkg.com/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/3.0.2/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
  <!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
   /* header.css - Tema Biru Muda dengan Gradien */

/* Reset dan base styling */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  margin-top: 80px;
}

/* Custom Navbar Styling - GRADIEN BIRU MUDA */
.custom-navbar {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 25px rgba(59, 130, 246, 0.25);
  border: none;
  height: 80px;
  z-index: 1050;
  position: fixed;
  width: 100%;
  top: 0;
  left: 0;
  transition: all 0.3s ease;
}

.custom-navbar::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(90deg, rgba(255,255,255,0.1) 0%, transparent 50%, rgba(255,255,255,0.1) 100%);
  animation: shimmer 3s infinite;
  pointer-events: none;
}

@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

/* Sidebar Toggle Button */
.sidebar-toggle-btn {
  background: rgba(255, 255, 255, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.3);
  width: 50px;
  height: 50px;
  border-radius: 15px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  position: relative;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.sidebar-toggle-btn::before {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.sidebar-toggle-btn:hover {
  background: rgba(255, 255, 255, 0.35);
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
  border-color: rgba(255, 255, 255, 0.5);
}

.sidebar-toggle-btn:hover::before {
  opacity: 1;
}

.hamburger-line {
  width: 25px;
  height: 3px;
  background: white;
  margin: 3px 0;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 3px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.sidebar-toggle-btn.active .hamburger-line:nth-child(1) {
  transform: rotate(-45deg) translate(-6px, 6px);
}

.sidebar-toggle-btn.active .hamburger-line:nth-child(2) {
  opacity: 0;
  transform: translateX(20px);
}

.sidebar-toggle-btn.active .hamburger-line:nth-child(3) {
  transform: rotate(45deg) translate(-6px, -6px);
}

/* Brand Styling - CENTERED */
.navbar-brand-container {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
}

.custom-brand {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: white !important;
  font-weight: bold;
  transition: all 0.3s ease;
}

.custom-brand:hover {
  color: rgba(255, 255, 255, 0.95) !important;
  transform: translateY(-1px);
}

.brand-icon {
  background: rgba(255, 255, 255, 0.25);
  width: 50px;
  height: 50px;
  border-radius: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
  position: relative;
  overflow: hidden;
}

.brand-icon::before {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.brand-icon:hover {
  background: rgba(255, 255, 255, 0.35);
  transform: scale(1.1) rotate(5deg);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
}

.brand-icon:hover::before {
  opacity: 1;
}

.brand-icon i {
  font-size: 24px;
  color: white;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
  z-index: 1;
}

.brand-text .brand-title {
  display: block;
  font-size: 18px;
  font-weight: 700;
  line-height: 1.2;
  color: white;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  letter-spacing: 0.5px;
}

.brand-text .brand-subtitle {
  display: block;
  font-size: 12px;
  opacity: 0.95;
  font-weight: 400;
  color: rgba(255, 255, 255, 0.95);
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

/* Navigation Links */
.navbar-nav {
  align-items: center;
  gap: 5px;
  margin-left: auto;
}

.navbar-nav .nav-link {
  color: white !important;
  font-weight: 500;
  padding: 10px 15px !important;
  border-radius: 12px;
  transition: all 0.3s ease;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.navbar-nav .nav-link::before {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.1);
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.3s ease;
  border-radius: 12px;
}

.navbar-nav .nav-link:hover::before {
  transform: scaleX(1);
}

.navbar-nav .nav-link:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

/* User Dropdown - IMPROVED */
.user-dropdown {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.15) 100%) !important;
  border-radius: 50px !important;
  padding: 6px 18px 6px 6px !important;
  border: 2px solid rgba(255, 255, 255, 0.35);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 6px 18px rgba(59, 130, 246, 0.2), inset 0 1px 3px rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  position: relative;
  overflow: hidden;
}

.user-dropdown::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
  transition: left 0.5s ease;
}

.user-dropdown:hover::before {
  left: 100%;
}

.user-dropdown:hover {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.35) 0%, rgba(255, 255, 255, 0.25) 100%) !important;
  transform: translateY(-2px) scale(1.02);
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3), inset 0 2px 4px rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.5);
}

.user-avatar {
  position: relative;
  width: 42px;
  height: 42px;
  display: inline-block;
}

.avatar-img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid rgba(255, 255, 255, 0.6);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2), 0 0 0 4px rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.user-dropdown:hover .avatar-img {
  border-color: rgba(255, 255, 255, 0.8);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25), 0 0 0 4px rgba(255, 255, 255, 0.15);
  transform: scale(1.05);
}

.avatar-status {
  position: absolute;
  bottom: 2px;
  right: 0px;
  width: 14px;
  height: 14px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  border: 3px solid white;
  border-radius: 50%;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4), 0 0 0 2px rgba(255, 255, 255, 0.2);
  animation: statusPulse 2s ease-in-out infinite;
}

@keyframes statusPulse {
  0%, 100% { 
    opacity: 1;
    transform: scale(1);
  }
  50% { 
    opacity: 0.8;
    transform: scale(1.1);
  }
}

.user-name {
  color: white;
  font-weight: 600;
  font-size: 14px;
  text-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
  letter-spacing: 0.3px;
  position: relative;
  z-index: 2;
}

/* Custom Dropdown */
.custom-dropdown {
  background: white;
  border: none;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(59, 130, 246, 0.2);
  padding: 10px 0;
  margin-top: 15px;
  min-width: 250px;
  border: 1px solid rgba(59, 130, 246, 0.1);
}

.dropdown-header {
  padding: 15px 20px;
  border-bottom: 1px solid rgba(59, 130, 246, 0.1);
}

.user-info {
  display: flex;
  align-items: center;
}

.header-avatar {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  margin-right: 12px;
  border: 3px solid #3b82f6;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.user-details strong {
  display: block;
  color: #1e293b;
  font-size: 14px;
  font-weight: 700;
}

.user-details small {
  color: #64748b;
  font-size: 12px;
}

.dropdown-link {
  padding: 12px 20px;
  transition: all 0.3s ease;
  color: #475569;
  display: flex;
  align-items: center;
  font-weight: 500;
  position: relative;
}

.dropdown-link::before {
  content: '';
  position: absolute;
  left: 0;
  width: 4px;
  height: 0;
  background: linear-gradient(180deg, #3b82f6 0%, #60a5fa 100%);
  transition: height 0.3s ease;
}

.dropdown-link:hover::before {
  height: 100%;
}

.dropdown-link:hover {
  background: linear-gradient(90deg, #eff6ff 0%, #dbeafe 100%);
  color: #3b82f6;
  transform: translateX(5px);
}

.logout-link::before {
  background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%);
}

.logout-link:hover {
  background: linear-gradient(90deg, #fef2f2 0%, #fee2e2 100%);
  color: #ef4444;
}

/* Mobile Toggle */
.mobile-toggle {
  background: transparent;
  border: none;
  color: white;
  padding: 5px;
}

.toggler-icon {
  display: block;
  width: 25px;
  height: 3px;
  background: white;
  position: relative;
  transition: all 0.3s ease;
  border-radius: 3px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.toggler-icon::before,
.toggler-icon::after {
  content: '';
  display: block;
  width: 25px;
  height: 3px;
  background: white;
  position: absolute;
  transition: all 0.3s ease;
  border-radius: 3px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.toggler-icon::before {
  top: -8px;
}

.toggler-icon::after {
  bottom: -8px;
}

/* Progress Bar */
.page-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background: rgba(255, 255, 255, 0.2);
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #93c5fd, #60a5fa, #3b82f6, #2563eb);
  background-size: 300% 100%;
  animation: progressFlow 3s ease-in-out infinite;
  width: 0%;
  transition: width 0.3s ease;
  box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
}

@keyframes progressFlow {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

/* Sidebar Overlay untuk Mobile */
.sidebar-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(59, 130, 246, 0.2);
  backdrop-filter: blur(4px);
  z-index: 1040;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.sidebar-overlay.show {
  display: block;
  opacity: 1;
}

/* Responsive */
@media (max-width: 991px) {
  .navbar-brand-container {
    position: static;
    transform: none;
    margin: 0 auto;
  }
}

@media (max-width: 768px) {
  .custom-navbar {
    height: 70px;
  }

  body {
    margin-top: 70px;
  }

  .brand-text .brand-title {
    font-size: 16px;
  }

  .brand-text .brand-subtitle {
    font-size: 11px;
  }

  .navbar-nav .nav-link {
    padding: 8px 12px !important;
  }
}

@media (max-width: 576px) {
  .custom-navbar {
    height: 65px;
  }

  body {
    margin-top: 65px;
  }

  .brand-text {
    display: none;
  }

  .user-name {
    display: none;
  }

  .sidebar-toggle-btn {
    width: 45px;
    height: 45px;
  }

  .brand-icon {
    width: 45px;
    height: 45px;
    margin-right: 0;
  }
  
  .navbar-brand-container {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
  }
}

/* Fix untuk dropdown yang terpotong */
.dropdown-menu {
  margin-top: 0.5rem !important;
}

/* Smooth transitions */
.navbar-nav,
.navbar-nav .nav-item,
.navbar-nav .nav-link {
  transition: all 0.3s ease;
}

/* Initialize tooltips */
[data-bs-toggle="tooltip"] {
  cursor: pointer;
}

/* Fix Dropdown Menu */
.navbar-nav .dropdown-menu {
  z-index: 9999 !important;
  position: absolute !important;
}

.navbar-nav .dropdown {
  position: relative;
}

/* Pastikan dropdown bisa diklik */
.dropdown-menu.show {
  display: block;
  opacity: 1;
  visibility: visible;
  pointer-events: auto;
}
  </style>
</head>

<body>

  <!-- Enhanced Top Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top custom-navbar">
    <div class="container-fluid">

      <!-- Animated Sidebar Toggle -->
      <button id="sidebarToggle" class="btn sidebar-toggle-btn me-3" type="button" aria-label="Toggle Sidebar">
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
      </button>

      <!-- Enhanced Brand with Logo - CENTERED -->
      <div class="navbar-brand-container">
        <a class="navbar-brand custom-brand" href="dashboard.php">
          <div class="brand-icon">
            <i class="fas fa-building"></i>
          </div>
          <div class="brand-text">
            <span class="brand-title">SISTEM INFORMASI</span>
            <small class="brand-subtitle">Kepegawaian</small>
          </div>
        </a>
      </div>

      <!-- Mobile Menu Toggle -->
      <button class="navbar-toggler mobile-toggle d-lg-none ms-auto" type="button" data-bs-toggle="collapse"
        data-bs-target="#topNav" aria-controls="topNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="toggler-icon"></span>
      </button>

      <!-- Navigation Content -->
      <div class="collapse navbar-collapse" id="topNav">
        <ul class="navbar-nav ms-auto">
          <!-- User Profile Dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle user-dropdown" href="#" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              <div class="user-avatar">
                <img src="assets/img/default-avatar.png" alt="User" class="avatar-img" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama_lengkap'] ?? 'User') ?>&background=3b82f6&color=fff'">
                <div class="avatar-status"></div>
              </div>
              <span class="user-name ms-2"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User') ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end custom-dropdown">
              <li class="dropdown-header">
                <div class="user-info">
                  <img src="assets/img/default-avatar.png" alt="User" class="header-avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama_lengkap'] ?? 'User') ?>&background=3b82f6&color=fff'">
                  <div class="user-details">
                    <strong><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User') ?></strong>
                    <small><?= ucfirst(str_replace('_', ' ', $_SESSION['role'] ?? 'user')) ?></small>
                  </div>
                </div>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <a class="dropdown-item dropdown-link" href="help.php">
                  <i class="fa fa-question-circle me-2"></i>Bantuan
                </a>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <a class="dropdown-item dropdown-link logout-link" href="logout.php">
                  <i class="fa fa-sign-out-alt me-2"></i>Logout
                </a>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>

    <!-- Progress Bar -->
    <div class="page-progress">
      <div class="progress-fill"></div>
    </div>
  </nav>


  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- Initialize Tooltips -->
  <script>
    // Initialize Bootstrap tooltips
    document.addEventListener('DOMContentLoaded', function() {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });
  </script>

  <!-- Load header-sidebar integration script -->
  <script src="js/header.js"></script>
  <?php  include 'includes/alert_handler.php'; ?>