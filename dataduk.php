<?php
session_start();
require_once 'check_session.php';
if (!isAdmin()) { header('Location: dashboard.php?error=Akses ditolak'); exit; }
require_once 'config/koneksi.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';


// Statistik dengan query yang lebih detail
$stats_query = "
    SELECT 
        COUNT(*) as total_duk,
        COUNT(CASE WHEN jenis_kelamin = 'Laki-laki' THEN 1 END) as total_laki,
        COUNT(CASE WHEN jenis_kelamin = 'Perempuan' THEN 1 END) as total_perempuan,
        COUNT(CASE WHEN eselon IS NOT NULL AND eselon != '' AND eselon != 'Non-Eselon' THEN 1 END) as total_eselon
    FROM duk
";
$stats = $koneksi->query($stats_query)->fetch_assoc();

// Query untuk filter options
$pangkat_options = $koneksi->query("SELECT DISTINCT pangkat_terakhir FROM duk WHERE pangkat_terakhir != '' ORDER BY pangkat_terakhir")->fetch_all(MYSQLI_ASSOC);
$golongan_options = $koneksi->query("SELECT DISTINCT golongan FROM duk WHERE golongan != '' ORDER BY golongan")->fetch_all(MYSQLI_ASSOC);
$jabatan_options = $koneksi->query("SELECT DISTINCT jabatan_terakhir FROM duk WHERE jabatan_terakhir != '' ORDER BY jabatan_terakhir")->fetch_all(MYSQLI_ASSOC);
$pendidikan_options = $koneksi->query("SELECT DISTINCT pendidikan_terakhir FROM duk WHERE pendidikan_terakhir != '' ORDER BY pendidikan_terakhir")->fetch_all(MYSQLI_ASSOC);

// Query untuk data tabel DUK - PERBAIKAN: TAMBAHKAN SEMUA FIELD YANG DIPERLUKAN
$sql_duk = "SELECT id, nama, nip, kartu_pegawai, pangkat_terakhir, golongan, jabatan_terakhir, ttl, jenis_kelamin, 
            pendidikan_terakhir, prodi, tmt_pangkat, tmt_eselon, eselon, jenis_jabatan, jft_tingkat, jfu_kelas 
            FROM duk ORDER BY nama ASC";
$result_duk = $koneksi->query($sql_duk);
?>

<!-- CSS DATA DUK -->
<link rel="stylesheet" href="css/dataduk.css">

<main class="main-content">
  <!-- Dashboard Header -->
  <div class="dashboard-header fade-in">
    <h1 class="dashboard-title">
      <i class="fas fa-tachometer-alt me-2"></i>
      DATA DUK
    </h1>
    <p class="dashboard-subtitle">Sistem Administrasi Data Urusan Kepegawaian</p>
  </div>

  <!-- Enhanced Statistics -->
  <div class="stats-container fade-in">
    <div class="stat-card primary" onclick="filterByCard('all')">
      <div class="stat-icon">
        <i class="fas fa-users"></i>
      </div>
      <h3 class="stat-number"><?= $stats['total_duk'] ?></h3>
      <p class="stat-label">Total Pegawai DUK</p>
      <div class="stat-trend trend-up">
        <i class="fas fa-arrow-up me-1"></i>
        <span>Data terkini</span>
      </div>
    </div>

    <div class="stat-card success" onclick="filterByCard('laki')">
      <div class="stat-icon">
        <i class="fas fa-male"></i>
      </div>
      <h3 class="stat-number"><?= $stats['total_laki'] ?></h3>
      <p class="stat-label">Pegawai Laki-laki</p>
      <div class="stat-trend">
        <span><?= $stats['total_duk'] > 0 ? round(($stats['total_laki'] / $stats['total_duk']) * 100, 1) : 0 ?>% dari
          total</span>
      </div>
    </div>

    <div class="stat-card warning" onclick="filterByCard('perempuan')">
      <div class="stat-icon">
        <i class="fas fa-female"></i>
      </div>
      <h3 class="stat-number"><?= $stats['total_perempuan'] ?></h3>
      <p class="stat-label">Pegawai Perempuan</p>
      <div class="stat-trend">
        <span><?= $stats['total_duk'] > 0 ? round(($stats['total_perempuan'] / $stats['total_duk']) * 100, 1) : 0 ?>%
          dari total</span>
      </div>
    </div>

    <div class="stat-card info" onclick="filterByCard('eselon')">
      <div class="stat-icon">
        <i class="fas fa-star"></i>
      </div>
      <h3 class="stat-number"><?= $stats['total_eselon'] ?></h3>
      <p class="stat-label">Pegawai Eselon</p>
      <div class="stat-trend trend-up">
        <i class="fas fa-arrow-up me-1"></i>
        <span>Struktural</span>
      </div>
    </div>
  </div>

  <!-- Advanced Filter Section -->
  <div class="filter-section fade-in">
    <button class="filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#filterContent"
      aria-expanded="false">
      <span>
        <i class="fas fa-filter me-2"></i>
        Filter & Pencarian Lanjutan
      </span>
      <i class="fas fa-chevron-down"></i>
    </button>

    <div class="collapse filter-content" id="filterContent">
      <form id="filterForm">
        <div class="filter-row">
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-search me-1"></i>Pencarian Nama/NIP
            </label>
            <input type="text" class="form-control" id="searchName" placeholder="Masukkan nama atau NIP...">
          </div>

          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-venus-mars me-1"></i>Jenis Kelamin
            </label>
            <select class="form-control" id="filterGender">
              <option value="">Semua Jenis Kelamin</option>
              <option value="Laki-laki">Laki-laki</option>
              <option value="Perempuan">Perempuan</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-medal me-1"></i>Pangkat
            </label>
            <select class="form-control" id="filterPangkat">
              <option value="">Semua Pangkat</option>
              <?php foreach ($pangkat_options as $pangkat): ?>
                <option value="<?= htmlspecialchars($pangkat['pangkat_terakhir']) ?>">
                  <?= htmlspecialchars($pangkat['pangkat_terakhir']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="filter-row">
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-layer-group me-1"></i>Golongan
            </label>
            <select class="form-control" id="filterGolongan">
              <option value="">Semua Golongan</option>
              <?php foreach ($golongan_options as $golongan): ?>
                <option value="<?= htmlspecialchars($golongan['golongan']) ?>">
                  <?= htmlspecialchars($golongan['golongan']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-briefcase me-1"></i>Jabatan
            </label>
            <select class="form-control" id="filterJabatan">
              <option value="">Semua Jabatan</option>
              <?php foreach ($jabatan_options as $jabatan): ?>
                <option value="<?= htmlspecialchars($jabatan['jabatan_terakhir']) ?>">
                  <?= htmlspecialchars($jabatan['jabatan_terakhir']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-graduation-cap me-1"></i>Pendidikan
            </label>
            <select class="form-control" id="filterPendidikan">
              <option value="">Semua Pendidikan</option>
              <?php foreach ($pendidikan_options as $pendidikan): ?>
                <option value="<?= htmlspecialchars($pendidikan['pendidikan_terakhir']) ?>">
                  <?= htmlspecialchars($pendidikan['pendidikan_terakhir']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="filter-actions">
          <button type="button" class="btn btn-primary" onclick="applyFilter()">
            <i class="fas fa-search me-2"></i>Terapkan Filter
          </button>
          <button type="button" class="btn btn-secondary" onclick="resetFilter()">
            <i class="fas fa-refresh me-2"></i>Reset Filter
          </button>
          <button type="button" class="btn btn-success" onclick="exportData()">
            <i class="fas fa-download me-2"></i>Export Data
          </button>
          <a class="btn btn-primary" href="export_semua_data_duk.php?format=pdf" target="_blank">
            <i class="fas fa-file-pdf"></i> Export PDF
          </a>
          <span class="text-muted ms-auto" id="resultCount">Menampilkan <?= $result_duk->num_rows ?> data</span>
        </div>
      </form>
    </div>
  </div>

  <!-- Enhanced Table Section -->
  <div class="table-section fade-in">
    <div class="table-header">
      <h5 class="table-title">
        <i class="fas fa-table me-2"></i>
        Data Pegawai DUK
      </h5>
      <div class="table-controls">
        <div class="search-box">
          <input type="text" class="search-input" id="quickSearch" placeholder="Pencarian cepat...">
          <i class="fas fa-search search-icon"></i>
        </div>
        <a href="form_tambah_duk.php" class="btn btn-success btn-sm">
          <i class="fas fa-plus me-1"></i>Tambah Data
        </a>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover" id="dukTable">
        <thead>
          <tr>
            <th>Pegawai</th>
            <th>Pangkat/Gol</th>
            <th>Jabatan</th>
            <th>TTL</th>
            <th>Jenis Kelamin</th>
            <th>Pendidikan</th>
            <th>TMT/Eselon</th>
            <th>Jenis Jabatan</th>
            <th width="150">Aksi</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <?php
          if ($result_duk && $result_duk->num_rows > 0):
            while ($row = $result_duk->fetch_assoc()):
              $initials = strtoupper(substr($row['nama'], 0, 1));
              if (strpos($row['nama'], ' ') !== false) {
                $nama_parts = explode(' ', $row['nama']);
                $initials = strtoupper(substr($nama_parts[0], 0, 1) . substr(end($nama_parts), 0, 1));
              }
              $genderClass = $row['jenis_kelamin'] === 'Laki-laki' ? 'primary' : 'warning';
              $genderIcon = $row['jenis_kelamin'] === 'Laki-laki' ? 'fa-mars' : 'fa-venus';

              // Tentukan jenis jabatan untuk ditampilkan
              $jenis_jabatan_display = '-';
              if ($row['eselon'] === 'Non-Eselon') {
                if ($row['jenis_jabatan'] === 'JFT' && !empty($row['jft_tingkat'])) {
                  $jenis_jabatan_display = '<span class="badge badge-success"><i class="fas fa-star me-1"></i>JFT: ' . htmlspecialchars($row['jft_tingkat']) . '</span>';
                } elseif ($row['jenis_jabatan'] === 'JFU' && !empty($row['jfu_kelas'])) {
                  $jenis_jabatan_display = '<span class="badge badge-info"><i class="fas fa-list-ol me-1"></i>JFU: Kelas ' . htmlspecialchars($row['jfu_kelas']) . '</span>';
                } else {
                  $jenis_jabatan_display = '<span class="badge badge-secondary">Non-Eselon</span>';
                }
              } else {
                $jenis_jabatan_display = '<span class="badge badge-primary">Eselon</span>';
              }
              ?>
              <tr data-eselon="<?= $row['eselon'] ?>" data-jenis-jabatan="<?= $row['jenis_jabatan'] ?? '' ?>"
                data-jft-tingkat="<?= $row['jft_tingkat'] ?? '' ?>" data-jfu-kelas="<?= $row['jfu_kelas'] ?? '' ?>">
                <td>
                  <div class="employee-info">
                    <div class="employee-avatar">
                      <?= $initials ?>
                    </div>
                    <div class="employee-details">
                      <h6><?= htmlspecialchars($row['nama']) ?></h6>
                      <small><i
                          class="fas fa-id-card me-1"></i><?= htmlspecialchars($row['nip'] ?: 'Belum ada NIP') ?></small>
                    </div>
                  </div>
                </td>
                <td>
                  <div>
                    <strong><?= htmlspecialchars($row['pangkat_terakhir'] ?: '-') ?></strong>
                    <br><span
                      class="badge badge-<?= $genderClass ?>"><?= htmlspecialchars($row['golongan'] ?: '-') ?></span>
                  </div>
                </td>
                <td>
                  <span class="fw-semibold"><?= htmlspecialchars($row['jabatan_terakhir'] ?: '-') ?></span>
                </td>
                <td>
                  <small><?= htmlspecialchars($row['ttl'] ?: '-') ?></small>
                </td>
                <td>
                  <span class="badge badge-<?= $genderClass ?>">
                    <i class="fas <?= $genderIcon ?> me-1"></i><?= htmlspecialchars($row['jenis_kelamin']) ?>
                  </span>
                </td>
                <td>
                  <span class="badge badge-info"><?= htmlspecialchars($row['pendidikan_terakhir'] ?: '-') ?></span>
                </td>
                <td>
                  <div>
                    <small><strong>TMT:</strong> <?= htmlspecialchars($row['tmt_eselon'] ?: '-') ?></small>
                    <br><small><strong>Eselon:</strong> <?= htmlspecialchars($row['eselon'] ?: '-') ?></small>
                  </div>
                </td>
                <td>
                  <?= $jenis_jabatan_display ?>
                </td>
                <td>
                  <div class="action-buttons" style="display: flex; flex-direction: column; gap: 5px; align-items: stretch;">
                    <a href="detail_duk.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm" data-bs-toggle="tooltip"
                      title="Lihat Detail" style="width: 100%;">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="form_edit_duk.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm" data-bs-toggle="tooltip"
                      title="Edit Data" style="width: 100%;">
                      <i class="fas fa-edit"></i>
                    </a>
                    <a href="proses_hapus_duk.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" data-bs-toggle="tooltip"
                      title="Hapus Data" onclick="return confirm('Yakin ingin menghapus data ini?')" style="width: 100%;">
                      <i class="fas fa-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
              <?php
            endwhile;
          else:
            ?>
            <tr>
              <td colspan="9" class="empty-state">
                <i class="fas fa-inbox"></i>
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
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/dataduk.js"></script>

<script>
  // Initialize exporter dengan data
  document.addEventListener('DOMContentLoaded', function () {
    window.dukExporter = new DUKExporter(originalData);
  });
</script>


<?php require_once 'includes/footer.php'; ?>