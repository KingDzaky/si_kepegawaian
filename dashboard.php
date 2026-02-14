<?php
session_start();
require_once 'check_session.php';
// Tidak ada proteksi role, semua user yang login bisa akses

require_once 'config/koneksi.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Statistik
$total_duk = $koneksi->query("SELECT COUNT(*) as total FROM duk")->fetch_assoc()['total'] ?? 0;
$total_penyuluh = $koneksi->query("SELECT COUNT(*) as total FROM penyuluh")->fetch_assoc()['total'] ?? 0;
$total_user = $koneksi->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'] ?? 0;
$total_pegawai = $total_duk + $total_penyuluh;

// TAMBAHAN: Statistik Kenaikan Pangkat
$total_usulan = $koneksi->query("SELECT COUNT(*) as total FROM kenaikan_pangkat")->fetch_assoc()['total'] ?? 0;
$usulan_draft = $koneksi->query("SELECT COUNT(*) as total FROM kenaikan_pangkat WHERE status = 'draft'")->fetch_assoc()['total'] ?? 0;
$usulan_diajukan = $koneksi->query("SELECT COUNT(*) as total FROM kenaikan_pangkat WHERE status = 'diajukan'")->fetch_assoc()['total'] ?? 0;
$usulan_disetujui = $koneksi->query("SELECT COUNT(*) as total FROM kenaikan_pangkat WHERE status = 'disetujui'")->fetch_assoc()['total'] ?? 0;
$usulan_ditolak = $koneksi->query("SELECT COUNT(*) as total FROM kenaikan_pangkat WHERE status = 'ditolak'")->fetch_assoc()['total'] ?? 0;

// Statistik Jabatan
$jft_count = $koneksi->query("SELECT COUNT(*) as total FROM duk WHERE jenis_jabatan = 'JFT'")->fetch_assoc()['total'] ?? 0;
$jfu_count = $koneksi->query("SELECT COUNT(*) as total FROM duk WHERE jenis_jabatan = 'JFU'")->fetch_assoc()['total'] ?? 0;
$struktural_count = $koneksi->query("SELECT COUNT(*) as total FROM duk WHERE jenis_jabatan = 'Struktural'")->fetch_assoc()['total'] ?? 0;

// Statistik Eselon
$eselon_II = $koneksi->query("SELECT COUNT(*) as total FROM duk WHERE eselon LIKE 'II%'")->fetch_assoc()['total'] ?? 0;
$eselon_III = $koneksi->query("SELECT COUNT(*) as total FROM duk WHERE eselon LIKE 'III%'")->fetch_assoc()['total'] ?? 0;
$eselon_IV = $koneksi->query("SELECT COUNT(*) as total FROM duk WHERE eselon LIKE 'IV%'")->fetch_assoc()['total'] ?? 0;

// Pegawai tanpa kartu pegawai
$tanpa_karpeg = $koneksi->query("SELECT COUNT(*) as total FROM duk WHERE kartu_pegawai IS NULL OR kartu_pegawai = ''")->fetch_assoc()['total'] ?? 0;

// Statistik per jenis kenaikan pangkat
$kp_pilihan = $koneksi->query("SELECT COUNT(*) as total FROM kenaikan_pangkat WHERE jenis_kenaikan = 'Pilihan'")->fetch_assoc()['total'] ?? 0;
$kp_reguler = $koneksi->query("SELECT COUNT(*) as total FROM kenaikan_pangkat WHERE jenis_kenaikan = 'Reguler'")->fetch_assoc()['total'] ?? 0;

// Pegawai yang sudah punya NIP vs belum
$ada_nip = $koneksi->query("SELECT COUNT(*) as total FROM duk WHERE nip IS NOT NULL AND nip != ''")->fetch_assoc()['total'] ?? 0;
$belum_nip = $total_duk - $ada_nip;

// BARU: Statistik Reminder & Notifikasi
$perlu_reminder = $koneksi->query("
    SELECT COUNT(*) as total 
    FROM kenaikan_pangkat kp
    LEFT JOIN duk d ON kp.nip = d.nip
    WHERE kp.status = 'disetujui'
    AND d.nomor_wa IS NOT NULL
    AND DATEDIFF(kp.tmt_pangkat_baru, CURDATE()) BETWEEN 335 AND 395
    AND NOT EXISTS (
        SELECT 1 FROM notifikasi_wa 
        WHERE id_kenaikan_pangkat = kp.id 
        AND status = 'terkirim' 
        AND pesan LIKE '%PENGINGAT KENAIKAN PANGKAT%'
    )
")->fetch_assoc()['total'] ?? 0;

$notif_terkirim = $koneksi->query("
    SELECT COUNT(*) as total 
    FROM notifikasi_wa 
    WHERE status = 'terkirim' 
    AND DATE(tanggal_kirim) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
")->fetch_assoc()['total'] ?? 0;

$notif_gagal = $koneksi->query("
    SELECT COUNT(*) as total 
    FROM notifikasi_wa 
    WHERE status = 'gagal' 
    AND DATE(tanggal_kirim) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
")->fetch_assoc()['total'] ?? 0;

$pegawai_tanpa_wa = $koneksi->query("
    SELECT COUNT(*) as total 
    FROM duk 
    WHERE nomor_wa IS NULL OR nomor_wa = ''
")->fetch_assoc()['total'] ?? 0;

// Data jenis kelamin untuk pie chart
$jk = $koneksi->query("SELECT jenis_kelamin, COUNT(*) as total FROM duk GROUP BY jenis_kelamin");
$labels_jk = [];
$data_jk = [];
while ($row = $jk->fetch_assoc()) {
    $labels_jk[] = $row['jenis_kelamin'];
    $data_jk[] = $row['total'];
}



// Data pendidikan terakhir untuk bar chart
$pendidikan = $koneksi->query("SELECT pendidikan_terakhir, COUNT(*) as total FROM duk GROUP BY pendidikan_terakhir");
$labels_pendidikan = [];
$data_pendidikan = [];
while ($row = $pendidikan->fetch_assoc()) {
    $labels_pendidikan[] = $row['pendidikan_terakhir'];
    $data_pendidikan[] = $row['total'];
}


// Query untuk chart jabatan
$jabatan_query = $koneksi->query("
    SELECT jenis_jabatan, COUNT(*) as total 
    FROM duk 
    WHERE jenis_jabatan IS NOT NULL AND jenis_jabatan != ''
    GROUP BY jenis_jabatan
");
$labels_jabatan = [];
$data_jabatan = [];
while ($row = $jabatan_query->fetch_assoc()) {
    $labels_jabatan[] = $row['jenis_jabatan'];
    $data_jabatan[] = $row['total'];
}

// Query untuk chart eselon
$eselon_query = $koneksi->query("
    SELECT 
        CASE 
            WHEN eselon LIKE 'II%' THEN 'Eselon II'
            WHEN eselon LIKE 'III%' THEN 'Eselon III'
            WHEN eselon LIKE 'IV%' THEN 'Eselon IV'
            WHEN eselon = 'Non-Eselon' THEN 'Non-Eselon'
            ELSE 'Lainnya'
        END as eselon_group,
        COUNT(*) as total
    FROM duk
    WHERE eselon IS NOT NULL AND eselon != ''
    GROUP BY eselon_group
    ORDER BY eselon_group
");
$labels_eselon = [];
$data_eselon = [];
while ($row = $eselon_query->fetch_assoc()) {
    $labels_eselon[] = $row['eselon_group'];
    $data_eselon[] = $row['total'];
}

// Query untuk chart golongan (yang sudah ada tapi perlu diperbaiki)
$golongan_query = $koneksi->query("
    SELECT golongan, COUNT(*) as total 
    FROM duk 
    WHERE golongan IS NOT NULL AND golongan != ''
    GROUP BY golongan 
    ORDER BY golongan ASC
");
$labels_golongan = [];
$data_golongan = [];
while ($row = $golongan_query->fetch_assoc()) {
    $labels_golongan[] = $row['golongan'];
    $data_golongan[] = $row['total'];
}

// Query untuk tabel DUK
$sql_duk = "SELECT id, nama, nip, pangkat_terakhir, golongan, jabatan_terakhir, ttl, jenis_kelamin, pendidikan_terakhir, tmt_eselon, eselon 
            FROM duk ORDER BY nama ASC LIMIT 10";
$result_duk = $koneksi->query($sql_duk);
?>

<link rel="stylesheet" href="css/dashboard.css">

<main class="main-content">
  <!-- Header Section -->
  <div class="page-header fade-in">
  <div class="d-flex align-items-center">
    <!-- Logo -->
    <div class="header-logo me-4">
      <img src="assets/img/logo.png" alt="Logo Banjarmasin" class="logo-image">
    </div>
    
    <!-- Text Content -->
    <div class="header-text">
      <h1><i class="fas fa-tachometer-alt me-3"></i>Dashboard DPPKBPM</h1>
      <p class="page-subtitle">Sistem Informasi Dinas Pemberdayaan Perempuan, Perlindungan Anak, Kependudukan dan Keluarga Berencana Kota Banjarmasin</p>
    </div>
  </div>
</div>

  <!-- Statistik Cards Row 1: Data Pegawai -->
  <div class="row stat-card justify-content-center mb-4">
    <!-- Card Total Pegawai -->
    <div class="col-md-3 fade-in">
        <div class="card card-dark text-white mb-4">
            <div class="card-body">
                <div>
                    <div>Total Pegawai</div>
                    <small>DUK + Penyuluh</small>
                </div>
                <i class="fas fa-user-friends card-icon"></i>
            </div>
            <div class="card-footer">
                <span class="counter" data-target="<?= $total_pegawai ?>"><?= $total_pegawai ?></span>
            </div>
        </div>
    </div>

    <!-- Card Total DUK -->
    <div class="col-md-3 fade-in">
        <div class="card card-primary text-white mb-4">
            <div class="card-body">
                <div>
                    <div>Total DUK</div>
                    <small>Data Urut Kepangkatan</small>
                </div>
                <i class="fas fa-users card-icon"></i>
            </div>
            <div class="card-footer">
                <span class="counter" data-target="<?= $total_duk ?>"><?= $total_duk ?></span>
            </div>
        </div>
    </div>

    <!-- Card Total Penyuluh -->
    <div class="col-md-3 fade-in">
        <div class="card card-success text-white mb-4">
            <div class="card-body">
                <div>
                    <div>Total Penyuluh</div>
                    <small>Penyuluh Aktif</small>
                </div>
                <i class="fas fa-chalkboard-teacher card-icon"></i>
            </div>
            <div class="card-footer">
                <span class="counter" data-target="<?= $total_penyuluh ?>"><?= $total_penyuluh ?></span>
            </div>
        </div>
    </div>

    <!-- Card Total User -->
    <div class="col-md-3 fade-in">
        <div class="card card-warning text-white mb-4">
            <div class="card-body">
                <div>
                    <div>User Terdaftar</div>
                    <small>Pengguna Sistem</small>
                </div>
                <i class="fas fa-user-check card-icon"></i>
            </div>
            <div class="card-footer">
                <span class="counter" data-target="<?= $total_user ?>"><?= $total_user ?></span>
            </div>
        </div>
    </div>
  </div>

  <!-- Statistik Cards Row 2: Kenaikan Pangkat -->
  <div class="row stat-card justify-content-center mb-4">
    <!-- Card Total Usulan -->
    <div class="col-md-3 fade-in">
        <div class="card card-info text-white mb-4">
            <div class="card-body">
                <div>
                    <div>Total Usulan</div>
                    <small>Kenaikan Pangkat</small>
                </div>
                <i class="fas fa-file-alt card-icon"></i>
            </div>
            <div class="card-footer">
                <span class="counter" data-target="<?= $total_usulan ?>"><?= $total_usulan ?></span>
            </div>
        </div>
    </div>

    <!-- Card Draft -->
    <div class="col-md-2 fade-in">
        <div class="card card-secondary text-white mb-4">
            <div class="card-body">
                <div>
                    <div>Draft</div>
                    <small>Belum Diajukan</small>
                </div>
                <i class="fas fa-edit card-icon"></i>
            </div>
            <div class="card-footer">
                <span class="counter" data-target="<?= $usulan_draft ?>"><?= $usulan_draft ?></span>
            </div>
        </div>
    </div>

    <!-- Card Diajukan -->
    <div class="col-md-2 fade-in">
        <div class="card card-pending text-white mb-4">
            <div class="card-body">
                <div>
                    <div>Diajukan</div>
                    <small>Proses Review</small>
                </div>
                <i class="fas fa-paper-plane card-icon"></i>
            </div>
            <div class="card-footer">
                <span class="counter" data-target="<?= $usulan_diajukan ?>"><?= $usulan_diajukan ?></span>
            </div>
        </div>
    </div>

    <!-- Card Disetujui -->
    <div class="col-md-2 fade-in">
        <div class="card card-approved text-white mb-4">
            <div class="card-body">
                <div>
                    <div>Disetujui</div>
                    <small>Sudah Approve</small>
                </div>
                <i class="fas fa-check-circle card-icon"></i>
            </div>
            <div class="card-footer">
                <span class="counter" data-target="<?= $usulan_disetujui ?>"><?= $usulan_disetujui ?></span>
            </div>
        </div>
    </div>

    <!-- Card Ditolak -->
    <div class="col-md-2 fade-in">
        <div class="card card-rejected text-white mb-4">
            <div class="card-body">
                <div>
                    <div>Ditolak</div>
                    <small>Tidak Disetujui</small>
                </div>
                <i class="fas fa-times-circle card-icon"></i>
            </div>
            <div class="card-footer">
                <span class="counter" data-target="<?= $usulan_ditolak ?>"><?= $usulan_ditolak ?></span>
            </div>
        </div>
    </div>
  </div>

  <!-- BARU: Statistik Cards Row 3: Reminder & Notifikasi -->
  <div class="row stat-card justify-content-center mb-5">
    <!-- Card Perlu Reminder -->
    <div class="col-md-3 fade-in">
        <div class="card text-white mb-4" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body">
                <div>
                    <div>Perlu Reminder</div>
                    <small>Kenaikan Pangkat</small>
                </div>
                <i class="fas fa-bell card-icon"></i>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <span class="counter" data-target="<?= $perlu_reminder ?>"><?= $perlu_reminder ?></span>
                <?php if ($perlu_reminder > 0): ?>
                <a href="kenaikan_pangkat.php?tab=reminder" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Card Notif Terkirim -->
    <div class="col-md-3 fade-in">
        <div class="card text-white mb-4" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body">
                <div>
                    <div>Notif Terkirim</div>
                    <small>30 Hari Terakhir</small>
                </div>
                <i class="fas fa-check-double card-icon"></i>
            </div>
            <div class="card-footer">
                <span class="counter" data-target="<?= $notif_terkirim ?>"><?= $notif_terkirim ?></span>
            </div>
        </div>
    </div>

    <!-- Card Notif Gagal -->
    <div class="col-md-3 fade-in">
        <div class="card text-white mb-4" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <div class="card-body">
                <div>
                    <div>Notif Gagal</div>
                    <small>7 Hari Terakhir</small>
                </div>
                <i class="fas fa-exclamation-triangle card-icon"></i>
            </div>
            <div class="card-footer">
                <span class="counter" data-target="<?= $notif_gagal ?>"><?= $notif_gagal ?></span>
            </div>
        </div>
    </div>

    <!-- Card Tanpa Nomor WA -->
    <div class="col-md-3 fade-in">
        <div class="card text-white mb-4" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333 !important;">
            <div class="card-body">
                <div style="color: #333;">
                    <div>Tanpa No. WA</div>
                    <small>Perlu Dilengkapi</small>
                </div>
                <i class="fas fa-phone-slash card-icon" style="color: #333;"></i>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center" style="color: #333;">
                <span class="counter" data-target="<?= $pegawai_tanpa_wa ?>"><?= $pegawai_tanpa_wa ?></span>
                <?php if ($pegawai_tanpa_wa > 0): ?>
                <a href="dataduk.php" class="btn btn-dark btn-sm">
                    <i class="fas fa-edit"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
  </div>

  <!-- BARU: Widget Quick Actions untuk Reminder -->
  <?php if ($perlu_reminder > 0): ?>
  <div class="alert alert-warning fade-in mb-4" style="border-left: 4px solid #f5576c;">
    <div class="d-flex align-items-center">
      <div class="flex-grow-1">
        <h5 class="mb-1">
          <i class="fas fa-bell me-2"></i>
          <strong><?= $perlu_reminder ?></strong> ASN Perlu Dikirim Reminder!
        </h5>
        <p class="mb-0">
          Ada ASN yang akan naik pangkat sekitar 1 tahun lagi. Segera kirim reminder untuk persiapan berkas.
        </p>
      </div>
      <div>
        <a href="kenaikan_pangkat.php?tab=reminder" class="btn btn-danger">
          <i class="fas fa-paper-plane me-2"></i>Kirim Reminder
        </a>
      </div>
    </div>
  </div>

<!-- Row: Kelengkapan Data -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card" style="border-left: 4px solid #667eea;">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Monitoring Kelengkapan Data Pegawai
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="p-3 border rounded">
                            <i class="fas fa-id-card fa-2x mb-2" style="color: #667eea;"></i>
                            <h4 class="mb-0"><?= $tanpa_karpeg ?></h4>
                            <small class="text-muted">Tanpa Kartu Pegawai</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded">
                            <i class="fas fa-phone-slash fa-2x mb-2" style="color: #f5576c;"></i>
                            <h4 class="mb-0"><?= $pegawai_tanpa_wa ?></h4>
                            <small class="text-muted">Tanpa No. WhatsApp</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded">
                            <i class="fas fa-id-badge fa-2x mb-2" style="color: #28a745;"></i>
                            <h4 class="mb-0"><?= $ada_nip ?></h4>
                            <small class="text-muted">Sudah Punya NIP</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded">
                            <i class="fas fa-user-times fa-2x mb-2" style="color: #ffc107;"></i>
                            <h4 class="mb-0"><?= $belum_nip ?></h4>
                            <small class="text-muted">Belum Punya NIP</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
  
  <?php endif; ?>

  <!-- Data Table Section -->
  <div class="table-section fade-in">
    <div class="table-header">
      <h5 class="table-title">
        <i class="fas fa-table me-2"></i>
        Data Pegawai DUK Terbaru
      </h5>
      <div>
        <a href="dataduk.php" class="btn btn-info btn-sm me-2">
          <i class="fas fa-eye me-1"></i>Lihat Semua
        </a>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Pegawai</th>
            <th>Pangkat/Gol</th>
            <th>Jabatan</th>
            <th>Jenis Kelamin</th>
            <th>Pendidikan</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if ($result_duk && $result_duk->num_rows > 0):
            while($row = $result_duk->fetch_assoc()):
              $initials = strtoupper(substr($row['nama'], 0, 1));
              if (strpos($row['nama'], ' ') !== false) {
                  $nama_parts = explode(' ', $row['nama']);
                  $initials = strtoupper(substr($nama_parts[0], 0, 1) . substr(end($nama_parts), 0, 1));
              }
              $genderClass = $row['jenis_kelamin'] === 'Laki-laki' ? 'primary' : 'warning';
              $genderIcon = $row['jenis_kelamin'] === 'Laki-laki' ? 'fa-mars' : 'fa-venus';
          ?>
            <tr>
              <td>
                <div class="employee-info">
                  <div class="employee-avatar">
                    <?= $initials ?>
                  </div>
                  <div class="employee-details">
                    <h6><?= htmlspecialchars($row['nama']) ?></h6>
                    <small><i class="fas fa-id-card me-1"></i><?= htmlspecialchars($row['nip'] ?: 'Belum ada NIP') ?></small>
                  </div>
                </div>
              </td>
              <td>
                <div>
                  <strong><?= htmlspecialchars($row['pangkat_terakhir'] ?: '-') ?></strong>
                  <br><span class="badge badge-<?= $genderClass ?>"><?= htmlspecialchars($row['golongan'] ?: '-') ?></span>
                </div>
              </td>
              <td>
                <span class="fw-semibold"><?= htmlspecialchars($row['jabatan_terakhir'] ?: '-') ?></span>
              </td>
              <td>
                <span class="badge badge-<?= $genderClass ?>">
                  <i class="fas <?= $genderIcon ?> me-1"></i><?= htmlspecialchars($row['jenis_kelamin']) ?>
                </span>
              </td>
              <td>
                <span class="badge badge-info"><?= htmlspecialchars($row['pendidikan_terakhir'] ?: '-') ?></span>
              </td>
            </tr>
          <?php 
            endwhile;
          else: 
          ?>
            <tr>
              <td colspan="5" class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                <h5>Belum ada data DUK</h5>
                <p>Silakan tambahkan data pegawai terlebih dahulu</p>
                <a href="form_tambah_duk.php" class="btn btn-primary">
                  <i class="fas fa-plus me-2"></i>Tambah Data Pertama
                </a>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Charts Section -->
  <div class="row mb-5">
    <div class="col-md-6 fade-in">
      <div class="chart-card">
        <div class="card-header">
          <i class="fas fa-chart-pie me-2"></i> Distribusi Jenis Kelamin
        </div>
        <div class="card-body">
          <div class="chart-loading" id="loadingJK">
            <div class="loading-spinner"></div>
            Memuat data...
          </div>
          <canvas id="chartJenisKelamin" style="display: none; height: 300px;"></canvas>
        </div>
      </div>
    </div>

    <div class="col-md-6 fade-in">
      <div class="chart-card">
        <div class="card-header">
          <i class="fas fa-chart-bar me-2"></i> Distribusi Pendidikan Terakhir
        </div>
        <div class="card-body">
          <div class="chart-loading" id="loadingPendidikan">
            <div class="loading-spinner"></div>
            Memuat data...
          </div>
          <canvas id="chartPendidikan" style="display: none; height: 300px;"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-5">
    <!-- Jenis Jabatan -->
    <div class="col-md-6 fade-in">
        <div class="chart-card">
            <div class="card-header">
                <i class="fas fa-briefcase me-2"></i> Distribusi Jenis Jabatan
            </div>
            <div class="card-body">
                <canvas id="chartJabatan" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!--  Eselon -->
    <div class="col-md-6 fade-in">
        <div class="chart-card">
            <div class="card-header">
                <i class="fas fa-layer-group me-2"></i> Distribusi Eselon
            </div>
            <div class="card-body">
                <canvas id="chartEselon" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!--  Golongan (Full Width) -->
<div class="row mb-5">
    <div class="col-12 fade-in">
        <div class="chart-card">
            <div class="card-header">
                <i class="fas fa-chart-line me-2"></i> Distribusi Pegawai Per Golongan
            </div>
            <div class="card-body">
                <canvas id="chartGolongan" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

 <!-- Carousel Foto -->
<div class="carousel-container fade-in">
  <div class="chart-card">
    <div class="card-header">
      <i class="fas fa-images me-2"></i> Galeri DPPKBPM Kota Banjarmasin
    </div>
    <div class="card-body p-0">
      <div id="fotoCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-indicators">
          <button type="button" data-bs-target="#fotoCarousel" data-bs-slide-to="0" class="active"></button>
          <button type="button" data-bs-target="#fotoCarousel" data-bs-slide-to="1"></button>
          <button type="button" data-bs-target="#fotoCarousel" data-bs-slide-to="2"></button>
          <button type="button" data-bs-target="#fotoCarousel" data-bs-slide-to="3"></button>
          <button type="button" data-bs-target="#fotoCarousel" data-bs-slide-to="4"></button>
        </div>

        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="assets/img/DPPKBPM_f.jpeg" class="d-block w-100" style="height:400px; object-fit:cover;" alt="Kegiatan Rapat">
            <div class="carousel-caption">
              <h5><i class="fas fa-users me-2"></i>Kegiatan Rapat Koordinasi</h5>
              <p>Dokumentasi rapat internal dinas yang membahas program kerja strategis tahun ini.</p>
            </div>
          </div>
          <div class="carousel-item">
            <img src="assets/img/Depan.jpeg" class="d-block w-100" style="height:400px; object-fit:cover;" alt="Penyuluhan">
            <div class="carousel-caption">
              <h5><i class="fas fa-bullhorn me-2"></i>Penyuluhan Masyarakat</h5>
              <p>Kegiatan sosialisasi dan edukasi bersama masyarakat.</p>
            </div>
          </div>
          <div class="carousel-item">
            <img src="assets/img/Foto.jpeg" class="d-block w-100" style="height:400px; object-fit:cover;" alt="Pelatihan">
            <div class="carousel-caption">
              <h5><i class="fas fa-graduation-cap me-2"></i>Pelatihan Pegawai</h5>
              <p>Program pengembangan SDM melalui pelatihan.</p>
            </div>
          </div>
          <div class="carousel-item">
            <img src="assets/img/Olahraga.jpeg" class="d-block w-100" style="height:400px; object-fit:cover;" alt="Lapangan">
            <div class="carousel-caption">
              <h5><i class="fas fa-eye me-2"></i>Monitoring Lapangan</h5>
              <p>Kegiatan pengawasan dan evaluasi langsung di lapangan.</p>
            </div>
          </div>
          <div class="carousel-item">
            <img src="assets/img/Apel.jpeg" class="d-block w-100" style="height:400px; object-fit:cover;" alt="Workshop">
            <div class="carousel-caption">
              <h5><i class="fas fa-laptop-code me-2"></i>Workshop Teknologi</h5>
              <p>Pelatihan penggunaan sistem informasi kepegawaian berbasis teknologi.</p>
            </div>
          </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#fotoCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#fotoCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      </div>
    </div>
  </div>
</div>

  <!-- Visi Misi -->
  <div class="vision-mission fade-in">
    <div class="card-header">
      <i class="fas fa-bullseye me-2"></i> Visi & Misi DPPKBPM
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <h5><i class="fas fa-eye me-2" style="color: #667eea;"></i>Visi</h5>
          <p>"Terwujudnya kesetaraan dan keadilan gender, perlindungan anak yang optimal, serta pengendalian penduduk yang berkualitas menuju Banjarmasin yang sejahtera."</p>
        </div>
        <div class="col-md-6">
          <h5><i class="fas fa-rocket me-2" style="color: #28a745;"></i>Misi</h5>
          <ul>
            <li>Meningkatkan kualitas hidup perempuan melalui pemberdayaan ekonomi dan sosial</li>
            <li>Mengoptimalkan sistem perlindungan anak dari berbagai bentuk kekerasan</li>
            <li>Mengembangkan program kependudukan dan keluarga berencana yang berkelanjutan</li>
            <li>Memperkuat kapasitas kelembagaan dan SDM aparatur yang profesional</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const increment = target / 100;
        let current = 0;
        
        const updateCounter = () => {
            if (current < target) {
                current += increment;
                counter.textContent = Math.ceil(current);
                setTimeout(updateCounter, 20);
            } else {
                counter.textContent = target;
            }
        };
        
        setTimeout(updateCounter, 500);
    });

    setTimeout(() => {
        document.getElementById('loadingJK').style.display = 'none';
        document.getElementById('chartJenisKelamin').style.display = 'block';
        
        const ctxJK = document.getElementById('chartJenisKelamin').getContext('2d');
        new Chart(ctxJK, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($labels_jk) ?>,
                datasets: [{
                    data: <?= json_encode($data_jk) ?>,
                    backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#f5576c'],
                    borderWidth: 0,
                    hoverBorderWidth: 3,
                    hoverBorderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { size: 12 }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }, 1000);

    setTimeout(() => {
        document.getElementById('loadingPendidikan').style.display = 'none';
        document.getElementById('chartPendidikan').style.display = 'block';
        
        const ctxPendidikan = document.getElementById('chartPendidikan').getContext('2d');
        new Chart(ctxPendidikan, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels_pendidikan) ?>,
                datasets: [{
                    label: 'Jumlah Pegawai',
                    data: <?= json_encode($data_pendidikan) ?>,
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.1)' },
                        ticks: { font: { size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });
    }, 1500);

    const observeElements = document.querySelectorAll('.fade-in');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });

    observeElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        observer.observe(el);
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const increment = target / 100;
        let current = 0;
        
        const updateCounter = () => {
            if (current < target) {
                current += increment;
                counter.textContent = Math.ceil(current);
                setTimeout(updateCounter, 20);
            } else {
                counter.textContent = target;
            }
        };
        
        setTimeout(updateCounter, 500);
    });

    setTimeout(() => {
        document.getElementById('loadingJK').style.display = 'none';
        document.getElementById('chartJenisKelamin').style.display = 'block';
        
        const ctxJK = document.getElementById('chartJenisKelamin').getContext('2d');
        new Chart(ctxJK, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($labels_jk) ?>,
                datasets: [{
                    data: <?= json_encode($data_jk) ?>,
                    backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#f5576c'],
                    borderWidth: 0,
                    hoverBorderWidth: 3,
                    hoverBorderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { size: 12 }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }, 1000);

    setTimeout(() => {
        document.getElementById('loadingPendidikan').style.display = 'none';
        document.getElementById('chartPendidikan').style.display = 'block';
        
        const ctxPendidikan = document.getElementById('chartPendidikan').getContext('2d');
        new Chart(ctxPendidikan, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels_pendidikan) ?>,
                datasets: [{
                    label: 'Jumlah Pegawai',
                    data: <?= json_encode($data_pendidikan) ?>,
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.1)' },
                        ticks: { font: { size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });
    }, 1500);

    const observeElements = document.querySelectorAll('.fade-in');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });

    observeElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        observer.observe(el);
    });
});

// Chart Jenis Jabatan (Doughnut)
setTimeout(() => {
    const ctxJabatan = document.getElementById('chartJabatan').getContext('2d');
    new Chart(ctxJabatan, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($labels_jabatan) ?>,
            datasets: [{
                data: <?= json_encode($data_jabatan) ?>,
                backgroundColor: ['#667eea', '#28a745', '#f5576c', '#ffc107'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}, 2500);

// Chart Eselon (Bar Horizontal)
setTimeout(() => {
    const ctxEselon = document.getElementById('chartEselon').getContext('2d');
    new Chart(ctxEselon, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels_eselon) ?>,
            datasets: [{
                label: 'Jumlah Pegawai',
                data: <?= json_encode($data_eselon) ?>,
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderRadius: 8
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
}, 3000);

// Chart Golongan (Line)
setTimeout(() => {
    const ctxGolongan = document.getElementById('chartGolongan').getContext('2d');
    new Chart(ctxGolongan, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels_golongan) ?>,
            datasets: [{
                label: 'Jumlah Pegawai',
                data: <?= json_encode($data_golongan) ?>,
                backgroundColor: 'rgba(118, 75, 162, 0.1)',
                borderColor: 'rgba(118, 75, 162, 1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}, 3500);

</script>
<script src="js/scripts.js"></script>