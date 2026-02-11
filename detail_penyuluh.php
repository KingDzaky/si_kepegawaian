<?php
session_start();
require_once 'check_session.php';
// Tidak ada proteksi role, semua user yang login bisa akses

require_once 'config/koneksi.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Ambil ID dari URL
$id = $_GET['id'] ?? 0;

if (!$id || !is_numeric($id)) {
    header('Location: datapenyuluh.php?error=ID tidak valid');
    exit;
}

// Query untuk mengambil data Penyuluh berdasarkan ID
$sql = "SELECT * FROM penyuluh WHERE id = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: datapenyuluh.php?error=Data tidak ditemukan');
    exit;
}

$penyuluh = $result->fetch_assoc();

// Hitung usia berdasarkan TTL
function hitungUsia($ttl) {
    if (empty($ttl)) return null;
    
    // Extract tahun dari TTL (format: "Tempat, YYYY-MM-DD" atau "Tempat, DD-MM-YYYY")
    preg_match('/(\d{4})/', $ttl, $matches);
    if (!empty($matches[1])) {
        $tahunLahir = $matches[1];
        $usia = date('Y') - $tahunLahir;
        return $usia > 0 && $usia < 100 ? $usia : null;
    }
    
    return null;
}

// Fungsi untuk format masa kerja
function hitungMasaKerja($tmt) {
    if (empty($tmt)) return null;
    
    $tanggalMulai = new DateTime($tmt);
    $tanggalSekarang = new DateTime();
    $interval = $tanggalMulai->diff($tanggalSekarang);
    
    return $interval->y . ' tahun ' . $interval->m . ' bulan';
}

$usia = hitungUsia($penyuluh['ttl']);
$masaKerjaPangkat = hitungMasaKerja($penyuluh['tmt_pangkat']);
?>

<main class="main-content">
  <div class="container-fluid">
    
    <!-- Breadcrumb & Navigation -->
    <div class="page-header">
      <div class="header-content">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            </li>
            <li class="breadcrumb-item">
              <a href="datapenyuluh.php"><i class="fas fa-chalkboard-teacher"></i> Data Penyuluh</a>
            </li>
            <li class="breadcrumb-item active">Detail Penyuluh</li>
          </ol>
        </nav>
        
        <div class="page-actions">
          <a href="datapenyuluh.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
          </a>
          <a href="form_edit_penyuluh.php?id=<?= $id ?>" class="btn btn-warning btn-sm">
            <i class="fas fa-edit me-1"></i>Edit Data
          </a>
          <button class="btn btn-primary btn-sm" onclick="printDetail()">
            <i class="fas fa-print me-1"></i>Cetak
          </button>
          <div class="dropdown d-inline">
            <button class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown">
              <i class="fas fa-share me-1"></i>Aksi
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#" onclick="exportToPDF()">
                <i class="fas fa-file-pdf me-2"></i>Export PDF
              </a></li>
              <li><a class="dropdown-item" href="#" onclick="shareProfile()">
                <i class="fas fa-share-alt me-2"></i>Bagikan
              </a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="#" onclick="confirmDelete()">
                <i class="fas fa-trash me-2"></i>Hapus Data
              </a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Profile Card -->
      <div class="col-lg-4 col-xl-3">
        <div class="profile-card">
          <div class="profile-header">
            <div class="profile-avatar">
              <?php
              $initials = '';
              $nameParts = explode(' ', $penyuluh['nama']);
              foreach($nameParts as $part) {
                  if (!empty($part)) {
                      $initials .= strtoupper($part[0]);
                  }
              }
              $initials = substr($initials, 0, 2);
              ?>
              <div class="avatar-circle">
                <?= $initials ?>
              </div>
              <div class="status-indicator online" data-bs-toggle="tooltip" title="Data Aktif"></div>
            </div>
            
            <div class="profile-info">
              <h4 class="profile-name"><?= htmlspecialchars($penyuluh['nama']) ?></h4>
              <p class="profile-nip">
                <i class="fas fa-id-card me-1"></i>
                <?= !empty($penyuluh['nip']) ? htmlspecialchars($penyuluh['nip']) : 'NIP tidak tersedia' ?>
              </p>
              <div class="profile-badges">
                <span class="badge badge-<?= $penyuluh['jenis_kelamin'] === 'Laki-laki' ? 'primary' : 'warning' ?>">
                  <i class="fas fa-<?= $penyuluh['jenis_kelamin'] === 'Laki-laki' ? 'mars' : 'venus' ?> me-1"></i>
                  <?= htmlspecialchars($penyuluh['jenis_kelamin'] ?? 'Tidak diketahui') ?>
                </span>
                <?php if ($usia): ?>
                <span class="badge badge-info">
                  <i class="fas fa-birthday-cake me-1"></i>
                  <?= $usia ?> Tahun
                </span>
                <?php endif; ?>
                <span class="badge badge-success">
                  <i class="fas fa-chalkboard-teacher me-1"></i>
                  Penyuluh KB
                </span>
              </div>
            </div>
          </div>

          <div class="profile-stats">
            <div class="stat-item">
              <div class="stat-icon bg-primary">
                <i class="fas fa-star"></i>
              </div>
              <div class="stat-content">
                <h6>Pangkat</h6>
                <p><?= htmlspecialchars($penyuluh['pangkat'] ?? 'Belum ditetapkan') ?></p>
              </div>
            </div>

            <div class="stat-item">
              <div class="stat-icon bg-success">
                <i class="fas fa-layer-group"></i>
              </div>
              <div class="stat-content">
                <h6>Golongan</h6>
                <p><?= htmlspecialchars($penyuluh['golongan'] ?? 'Belum ditetapkan') ?></p>
              </div>
            </div>

            <div class="stat-item">
              <div class="stat-icon bg-warning">
                <i class="fas fa-briefcase"></i>
              </div>
              <div class="stat-content">
                <h6>Jabatan</h6>
                <p><?= htmlspecialchars($penyuluh['jabatan'] ?? 'Belum ditetapkan') ?></p>
              </div>
            </div>
          </div>

          <div class="profile-actions">
            <button class="btn btn-outline-primary btn-block" onclick="contactEmployee()">
              <i class="fas fa-envelope me-2"></i>Kontak
            </button>
            <button class="btn btn-outline-success btn-block" onclick="viewCareerPath()">
              <i class="fas fa-route me-2"></i>Riwayat Karir
            </button>
          </div>
        </div>
      </div>

      <!-- Detail Information -->
      <div class="col-lg-8 col-xl-9">
        
        <!-- Information Tabs -->
        <div class="detail-card">
          <div class="card-header">
            <ul class="nav nav-tabs card-tabs" role="tablist">
              <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#personal-info">
                  <i class="fas fa-user me-2"></i>Data Pribadi
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#career-info">
                  <i class="fas fa-briefcase me-2"></i>Karir & Jabatan
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#education-info">
                  <i class="fas fa-graduation-cap me-2"></i>Pendidikan
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#timeline">
                  <i class="fas fa-history me-2"></i>Timeline
                </button>
              </li>
            </ul>
          </div>

          <div class="card-body">
            <div class="tab-content">
              
              <!-- Personal Information Tab -->
              <div class="tab-pane fade show active" id="personal-info">
                <div class="info-section">
                  <h6 class="section-title">
                    <i class="fas fa-user-circle me-2"></i>
                    Informasi Pribadi
                  </h6>
                  
                  <div class="info-grid">
                    <div class="info-item">
                      <div class="info-label">
                        <i class="fas fa-user me-2"></i>Nama Lengkap
                      </div>
                      <div class="info-value"><?= htmlspecialchars($penyuluh['nama']) ?></div>
                    </div>

                    <div class="info-item">
                      <div class="info-label">
                        <i class="fa-solid fa-tag me-2"></i>NIP
                      </div>
                      <div class="info-value">
                        <?= !empty($penyuluh['nip']) ? htmlspecialchars($penyuluh['nip']) : '<span class="text-muted">Belum diisi</span>' ?>
                      </div>
                    </div>

                    <div class="info-item">
                      <div class="info-label">
                        <i class="fas fa-map-marker-alt me-2"></i>Tempat, Tanggal Lahir
                      </div>
                      <div class="info-value">
                        <?= !empty($penyuluh['ttl']) ? htmlspecialchars($penyuluh['ttl']) : '<span class="text-muted">Belum diisi</span>' ?>
                        <?php if ($usia): ?>
                          <small class="text-muted d-block">Usia: <?= $usia ?> tahun</small>
                        <?php endif; ?>
                      </div>
                    </div>

                    <div class="info-item">
                      <div class="info-label">
                        <i class="fas fa-venus-mars me-2"></i>Jenis Kelamin
                      </div>
                      <div class="info-value">
                        <span class="badge badge-<?= $penyuluh['jenis_kelamin'] === 'Laki-laki' ? 'primary' : 'warning' ?>">
                          <i class="fas fa-<?= $penyuluh['jenis_kelamin'] === 'Laki-laki' ? 'mars' : 'venus' ?> me-1"></i>
                          <?= htmlspecialchars($penyuluh['jenis_kelamin'] ?? 'Tidak diketahui') ?>
                        </span>
                      </div>
                    </div>

                    <div class="info-item">
                      <div class="info-label">
                        <i class="fas fa-home me-2"></i>Alamat
                      </div>
                      <div class="info-value">
                        <?= !empty($penyuluh['alamat']) ? htmlspecialchars($penyuluh['alamat']) : '<span class="text-muted">Belum diisi</span>' ?>
                      </div>
                    </div>

                    <div class="info-item">
                      <div class="info-label">
                        <i class="fas fa-phone me-2"></i>No. Telepon
                      </div>
                      <div class="info-value">
                        <?= !empty($penyuluh['no_telepon']) ? htmlspecialchars($penyuluh['no_telepon']) : '<span class="text-muted">Belum diisi</span>' ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Career Information Tab -->
              <div class="tab-pane fade" id="career-info">
                <div class="info-section">
                  <h6 class="section-title">
                    <i class="fas fa-medal me-2"></i>
                    Kepangkatan
                  </h6>
                  
                  <div class="career-timeline">
                    <div class="timeline-item">
                      <div class="timeline-marker bg-primary">
                        <i class="fas fa-star"></i>
                      </div>
                      <div class="timeline-content">
                        <h6>Pangkat</h6>
                        <p class="mb-1">
                          <strong><?= htmlspecialchars($penyuluh['pangkat'] ?? 'Belum ditetapkan') ?></strong>
                        </p>
                        <small class="text-muted">
                          <i class="fas fa-calendar me-1"></i>
                          TMT: <?= !empty($penyuluh['tmt_pangkat']) ? date('d F Y', strtotime($penyuluh['tmt_pangkat'])) : 'Belum diisi' ?>
                          <?php if ($masaKerjaPangkat): ?>
                            <br>Masa kerja: <?= $masaKerjaPangkat ?>
                          <?php endif; ?>
                        </small>
                      </div>
                    </div>

                    <div class="timeline-item">
                      <div class="timeline-marker bg-success">
                        <i class="fas fa-layer-group"></i>
                      </div>
                      <div class="timeline-content">
                        <h6>Golongan</h6>
                        <p class="mb-1">
                          <strong><?= htmlspecialchars($penyuluh['golongan'] ?? 'Belum ditetapkan') ?></strong>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="info-section">
                  <h6 class="section-title">
                    <i class="fas fa-briefcase me-2"></i>
                    Jabatan
                  </h6>
                  
                  <div class="career-timeline">

                    <div class="timeline-item">
                      <div class="timeline-marker bg-warning">
                        <i class="fas fa-map-marker-alt"></i>
                      </div>
                      <div class="timeline-content">
                        <h6>Unit Kerja</h6>
                        <p class="mb-1">
                          <strong><?= htmlspecialchars($penyuluh['unit_kerja'] ?? 'Belum ditetapkan') ?></strong>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Education Tab -->
              <div class="tab-pane fade" id="education-info">
                <div class="info-section">
                  <h6 class="section-title">
                    <i class="fas fa-graduation-cap me-2"></i>
                    Pendidikan Terakhir
                  </h6>
                  
                  <div class="education-card">
                    <div class="education-icon">
                      <i class="fas fa-university"></i>
                    </div>
                    <div class="education-content">
                      <p class="text-muted mb-0">Tingkat Pendidikan</p>
                      <h5><?= htmlspecialchars($penyuluh['pendidikan_terakhir'] ?? 'Belum diisi') ?></h5>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Timeline Tab -->
              <div class="tab-pane fade" id="timeline">
                <div class="info-section">
                  <h6 class="section-title">
                    <i class="fas fa-history me-2"></i>
                    Riwayat Karir
                  </h6>
                  
                  <div class="timeline-vertical">
                    <?php if (!empty($penyuluh['tmt_jabatan'])): ?>
                    <div class="timeline-event">
                      <div class="timeline-date"><?= date('Y', strtotime($penyuluh['tmt_jabatan'])) ?></div>
                      <div class="timeline-marker bg-info">
                        <i class="fas fa-briefcase"></i>
                      </div>
                      <div class="timeline-content">
                        <h6>Penempatan Jabatan</h6>
                        <p>Diangkat sebagai <?= htmlspecialchars($penyuluh['jabatan']) ?></p>
                        <small class="text-muted"><?= date('d F Y', strtotime($penyuluh['tmt_jabatan'])) ?></small>
                      </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($penyuluh['tmt_pangkat'])): ?>
                    <div class="timeline-event">
                      <div class="timeline-date"><?= date('Y', strtotime($penyuluh['tmt_pangkat'])) ?></div>
                      <div class="timeline-marker bg-primary">
                        <i class="fas fa-star"></i>
                      </div>
                      <div class="timeline-content">
                        <h6>Kenaikan Pangkat</h6>
                        <p>Naik pangkat menjadi <?= htmlspecialchars($penyuluh['pangkat_terakhir']) ?> 
                           Golongan <?= htmlspecialchars($penyuluh['golongan']) ?></p>
                        <small class="text-muted"><?= date('d F Y', strtotime($penyuluh['tmt_pangkat'])) ?></small>
                      </div>
                    </div>
                    <?php endif; ?>

                    <div class="timeline-event">
                      <div class="timeline-date"><?= date('Y') ?></div>
                      <div class="timeline-marker bg-success">
                        <i class="fas fa-plus"></i>
                      </div>
                      <div class="timeline-content">
                        <h6>Data Terdaftar</h6>
                        <p>Data penyuluh terdaftar dalam sistem</p>
                        <small class="text-muted">Saat ini</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<style>
/* Enhanced Detail Page Styles */
.main-content {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: 100vh;
  padding: 1rem 0;
}

/* Page Header */
.page-header {
  margin-bottom: 2rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 1rem 1.5rem;
  border-radius: 15px;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.breadcrumb {
  margin: 0;
  background: transparent;
  padding: 0;
}

.breadcrumb-item a {
  color: #667eea;
  text-decoration: none;
  font-weight: 500;
}

.breadcrumb-item a:hover {
  color: #764ba2;
}

.breadcrumb-item.active {
  color: #6c757d;
  font-weight: 600;
}

.page-actions {
  display: flex;
  gap: 0.5rem;
}

/* Profile Card */
.profile-card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  margin-bottom: 2rem;
}

.profile-header {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  padding: 2rem;
  text-align: center;
  color: white;
  position: relative;
}

.profile-avatar {
  position: relative;
  display: inline-block;
  margin-bottom: 1rem;
}

.avatar-circle {
  width: 80px;
  height: 80px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  font-weight: bold;
  backdrop-filter: blur(10px);
  border: 3px solid rgba(255, 255, 255, 0.3);
}

.status-indicator {
  position: absolute;
  bottom: 5px;
  right: 5px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 3px solid white;
}

.status-indicator.online {
  background: #28a745;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.profile-name {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
}

.profile-nip {
  margin: 0.5rem 0;
  opacity: 0.9;
  font-size: 0.95rem;
}

.profile-badges {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-top: 1rem;
}

.badge {
  padding: 0.5rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.badge-primary { background: linear-gradient(135deg, #007bff, #0056b3); }
.badge-warning { background: linear-gradient(135deg, #ffc107, #e0a800); }
.badge-info { background: linear-gradient(135deg, #17a2b8, #138496); }
.badge-success { background: linear-gradient(135deg, #28a745, #1e7e34); }

/* Profile Stats */
.profile-stats {
  padding: 1.5rem;
}

.stat-item {
  display: flex;
  align-items: center;
  padding: 1rem 0;
  border-bottom: 1px solid #e9ecef;
}

.stat-item:last-child {
  border-bottom: none;
}

.stat-icon {
  width: 45px;
  height: 45px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 1rem;
  color: white;
  font-size: 18px;
}

.stat-icon.bg-primary { background: linear-gradient(135deg, #007bff, #0056b3); }
.stat-icon.bg-success { background: linear-gradient(135deg, #28a745, #1e7e34); }
.stat-icon.bg-warning { background: linear-gradient(135deg, #ffc107, #e0a800); }

.stat-content h6 {
  margin: 0;
  font-size: 0.9rem;
  color: #6c757d;
  font-weight: 600;
}

.stat-content p {
  margin: 0;
  font-weight: 600;
  color: #2c3e50;
}

/* Profile Actions */
.profile-actions {
  padding: 1.5rem;
  border-top: 1px solid #e9ecef;
}

.btn-block {
  width: 100%;
  margin-bottom: 0.5rem;
}

.btn-block:last-child {
  margin-bottom: 0;
}

/* Detail Card */
.detail-card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.card-header {
  background: linear-gradient(135deg, #2c3e50, #34495e);
  padding: 0;
  border-bottom: none;
}

.card-tabs {
  margin: 0;
  border-bottom: none;
}

.card-tabs .nav-link {
  color: rgba(255, 255, 255, 0.8);
  border: none;
  padding: 1rem 1.5rem;
  font-weight: 500;
  border-radius: 0;
  transition: all 0.3s ease;
}

.card-tabs .nav-link:hover {
  color: white;
  background: rgba(255, 255, 255, 0.1);
}

.card-tabs .nav-link.active {
  color: white;
  background: rgba(255, 255, 255, 0.2);
  border-bottom: 3px solid #28a745;
}

.card-body {
  padding: 2rem;
}

/* Info Sections */
.info-section {
  margin-bottom: 2rem;
}

.section-title {
  color: #2c3e50;
  font-weight: 600;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e9ecef;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

.info-item {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 12px;
  border-left: 4px solid #28a745;
  transition: all 0.3s ease;
}

.info-item:hover {
  background: #d4edda;
  transform: translateX(5px);
}

.info-label {
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.info-value {
  font-weight: 500;
  color: #212529;
  font-size: 1rem;
}

/* Timeline Styles */
.career-timeline {
  position: relative;
  padding-left: 2rem;
}

.timeline-item {
  position: relative;
  padding-bottom: 2rem;
}

.timeline-item:last-child {
  padding-bottom: 0;
}

.timeline-item::before {
  content: '';
  position: absolute;
  left: -2rem;
  top: 2rem;
  width: 2px;
  height: calc(100% - 1rem);
  background: #e9ecef;
}

.timeline-item:last-child::before {
  display: none;
}

.timeline-marker {
  position: absolute;
  left: -2.75rem;
  top: 0;
  width: 35px;
  height: 35px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 14px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
}

.timeline-content h6 {
  margin: 0 0 0.5rem 0;
  color: #2c3e50;
  font-weight: 600;
}

.timeline-content p {
  margin: 0 0 0.5rem 0;
  color: #495057;
}

/* Vertical Timeline */
.timeline-vertical {
  position: relative;
}

.timeline-event {
  display: flex;
  align-items: flex-start;
  margin-bottom: 2rem;
  position: relative;
}

.timeline-event:last-child {
  margin-bottom: 0;
}

.timeline-event::before {
  content: '';
  position: absolute;
  left: 90px;
  top: 2rem;
  width: 2px;
  height: calc(100% + 1rem);
  background: #e9ecef;
}

.timeline-event:last-child::before {
  display: none;
}

.timeline-date {
  width: 60px;
  font-weight: 600;
  color: #28a745;
  font-size: 0.9rem;
  text-align: center;
  margin-right: 2rem;
}

.timeline-event .timeline-marker {
  position: relative;
  left: 0;
  margin-right: 1.5rem;
}

.timeline-event .timeline-content {
  flex: 1;
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 12px;
  border-left: 4px solid #28a745;
}

/* Education Card */
.education-card {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 2rem;
  border-radius: 15px;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
  border-left: 5px solid #28a745;
  transition: all 0.3s ease;
}

.education-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.education-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #28a745, #20c997);
  border-radius: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 24px;
  flex-shrink: 0;
}

.education-content {
  flex: 1;
}

.education-content h5 {
  margin: 0;
  color: #2c3e50;
  font-weight: 700;
}

/* Responsive Design */
@media (max-width: 992px) {
  .header-content {
    flex-direction: column;
    gap: 1rem;
  }

  .page-actions {
    width: 100%;
    justify-content: center;
    flex-wrap: wrap;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .timeline-event {
    flex-direction: column;
  }

  .timeline-event::before {
    left: 30px;
  }

  .timeline-date {
    margin-bottom: 1rem;
  }
}

@media (max-width: 768px) {
  .card-tabs .nav-link {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
  }

  .profile-header {
    padding: 1.5rem;
  }

  .avatar-circle {
    width: 70px;
    height: 70px;
    font-size: 24px;
  }

  .profile-name {
    font-size: 1.25rem;
  }

  .stat-icon {
    width: 40px;
    height: 40px;
    font-size: 16px;
  }

  .education-card {
    flex-direction: column;
    text-align: center;
  }

  .card-body {
    padding: 1.5rem;
  }
}
</style>

<script>
// Print functionality
function printDetail() {
  window.print();
}

// Export to PDF (placeholder)
function exportToPDF() {
  alert('Fitur Export PDF akan segera tersedia');
}

// Share profile (placeholder)
function shareProfile() {
  if (navigator.share) {
    navigator.share({
      title: '<?= htmlspecialchars($penyuluh['nama']) ?>',
      text: 'Detail Penyuluh - <?= htmlspecialchars($penyuluh['nama']) ?>',
      url: window.location.href
    }).catch(console.error);
  } else {
    // Fallback: copy link
    navigator.clipboard.writeText(window.location.href);
    alert('Link berhasil disalin ke clipboard!');
  }
}

// Contact employee (placeholder)
function contactEmployee() {
  const phone = '<?= $penyuluh['no_telepon'] ?? '' ?>';
  if (phone) {
    window.location.href = 'tel:' + phone;
  } else {
    alert('Nomor telepon tidak tersedia');
  }
}

// View career path (placeholder)
function viewCareerPath() {
  alert('Fitur Riwayat Karir Detail akan segera tersedia');
}

// Confirm delete
function confirmDelete() {
  if (confirm('Apakah Anda yakin ingin menghapus data penyuluh ini?\n\nData yang dihapus tidak dapat dikembalikan!')) {
    window.location.href = 'hapus_penyuluh.php?id=<?= $id ?>';
  }
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});
</script>

<?php require_once 'includes/footer.php'; ?>