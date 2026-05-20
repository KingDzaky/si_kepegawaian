<?php
session_start();
require_once 'check_session.php';
if (!isAdmin()) { header('Location: dashboard.php?error=Akses ditolak'); exit; }
require_once 'config/koneksi.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

require_once 'includes/sweetalert.php';
require_once 'includes/alert_handler.php';
require_once 'includes/alert_functions.php';

// Cek apakah user adalah superadmin (untuk fitur hapus massal)
$is_superadmin = isSuperAdmin();

// Statistik
$stats_query = "
    SELECT 
        COUNT(*) as total_duk,
        COUNT(CASE WHEN jenis_kelamin = 'Laki-laki' THEN 1 END) as total_laki,
        COUNT(CASE WHEN jenis_kelamin = 'Perempuan' THEN 1 END) as total_perempuan,
        COUNT(CASE WHEN eselon IS NOT NULL AND eselon != '' AND eselon != 'Non-Eselon' THEN 1 END) as total_eselon,
        COUNT(CASE WHEN status_pegawai = 'nonaktif' THEN 1 END) as total_nonaktif
    FROM duk
    WHERE deleted_at IS NULL
";
$stats = $koneksi->query($stats_query)->fetch_assoc();

// Filter options
$pangkat_options    = $koneksi->query("SELECT DISTINCT pangkat_terakhir FROM duk WHERE pangkat_terakhir != '' AND deleted_at IS NULL ORDER BY pangkat_terakhir")->fetch_all(MYSQLI_ASSOC);
$golongan_options   = $koneksi->query("SELECT DISTINCT golongan FROM duk WHERE golongan != '' AND deleted_at IS NULL ORDER BY golongan")->fetch_all(MYSQLI_ASSOC);
$jabatan_options    = $koneksi->query("SELECT DISTINCT jabatan_terakhir FROM duk WHERE jabatan_terakhir != '' AND deleted_at IS NULL ORDER BY jabatan_terakhir")->fetch_all(MYSQLI_ASSOC);
$pendidikan_options = $koneksi->query("SELECT DISTINCT pendidikan_terakhir FROM duk WHERE pendidikan_terakhir != '' AND deleted_at IS NULL ORDER BY pendidikan_terakhir")->fetch_all(MYSQLI_ASSOC);

$sql_duk = "SELECT id, nama, nip, kartu_pegawai, pangkat_terakhir, golongan, jabatan_terakhir, ttl, jenis_kelamin, 
            pendidikan_terakhir, prodi, tmt_pangkat, tmt_eselon,tmt_pangkat_awal, eselon, jenis_jabatan, jft_tingkat, jfu_kelas,
            status_pegawai, alasan_nonaktif, nonaktif_at
            FROM duk 
            WHERE deleted_at IS NULL 
            ORDER BY nama ASC";
$result_duk = $koneksi->query($sql_duk);
$total_rows = $result_duk->num_rows;
?>

<link rel="stylesheet" href="css/dataduk.css">

<style>
/* ======================================================
   BULK DELETE STYLES
   ====================================================== */
.bulk-action-bar {
    display: none;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    background: linear-gradient(135deg, #fff3cd, #ffeeba);
    border: 1px solid #ffc107;
    border-radius: 8px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.bulk-action-bar.show {
    display: flex;
}

.bulk-action-bar .selected-info {
    font-weight: 600;
    color: #856404;
    margin-right: auto;
}

.bulk-action-bar .selected-info span {
    background: #ffc107;
    color: #212529;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 0.85rem;
    margin-left: 6px;
}

/* Checkbox styling */
#checkAll,
.row-check {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #dc3545;
}

th.col-check,
td.col-check {
    width: 40px;
    text-align: center;
    vertical-align: middle;
}

/* Highlight row saat diceklis */
tr.row-selected {
    background-color: #fff3cd !important;
    outline: 2px solid #ffc107;
}

/* Tombol hapus semua — merah mencolok */
.btn-hapus-semua {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    border: none;
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
}
.btn-hapus-semua:hover { opacity: 0.85; color: white; }

/* Tombol hapus terpilih */
.btn-hapus-terpilih {
    background: linear-gradient(135deg, #fd7e14, #e67e22);
    color: white;
    border: none;
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
}
.btn-hapus-terpilih:hover { opacity: 0.85; color: white; }
.btn-hapus-terpilih:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* Badge superadmin kecil */
.badge-superadmin {
    font-size: 0.65rem;
    background: #6f42c1;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    vertical-align: middle;
    margin-left: 4px;
}
</style>

<main class="main-content">
  <!-- Header -->
  <div class="dashboard-header fade-in">
    <h1 class="dashboard-title">
      <i class="fas fa-tachometer-alt me-2"></i>DATA DUK
    </h1>
    <p class="dashboard-subtitle">Sistem Administrasi Data Urusan Kepegawaian</p>
  </div>

  <!-- Statistik Cards -->
  <div class="stats-container fade-in">
    <div class="stat-card primary" onclick="filterByCard('all')">
      <div class="stat-icon"><i class="fas fa-users"></i></div>
      <h3 class="stat-number"><?= $stats['total_duk'] ?></h3>
      <p class="stat-label">Total Pegawai DUK</p>
      <div class="stat-trend trend-up"><i class="fas fa-arrow-up me-1"></i><span>Data terkini</span></div>
    </div>

    <div class="stat-card success" onclick="filterByCard('laki')">
      <div class="stat-icon"><i class="fas fa-male"></i></div>
      <h3 class="stat-number"><?= $stats['total_laki'] ?></h3>
      <p class="stat-label">Pegawai Laki-laki</p>
      <div class="stat-trend">
        <span><?= $stats['total_duk'] > 0 ? round(($stats['total_laki'] / $stats['total_duk']) * 100, 1) : 0 ?>% dari total</span>
      </div>
    </div>

    <div class="stat-card warning" onclick="filterByCard('perempuan')">
      <div class="stat-icon"><i class="fas fa-female"></i></div>
      <h3 class="stat-number"><?= $stats['total_perempuan'] ?></h3>
      <p class="stat-label">Pegawai Perempuan</p>
      <div class="stat-trend">
        <span><?= $stats['total_duk'] > 0 ? round(($stats['total_perempuan'] / $stats['total_duk']) * 100, 1) : 0 ?>% dari total</span>
      </div>
    </div>

    <div class="stat-card info" onclick="filterByCard('eselon')">
      <div class="stat-icon"><i class="fas fa-star"></i></div>
      <h3 class="stat-number"><?= $stats['total_eselon'] ?></h3>
      <p class="stat-label">Pegawai Eselon</p>
      <div class="stat-trend trend-up"><i class="fas fa-arrow-up me-1"></i><span>Struktural</span></div>
    </div>

    <div class="stat-card" style="background: linear-gradient(135deg, #6c757d, #495057); cursor:pointer;" onclick="filterByCard('nonaktif')">
      <div class="stat-icon"><i class="fas fa-user-slash"></i></div>
      <h3 class="stat-number"><?= $stats['total_nonaktif'] ?></h3>
      <p class="stat-label">Pegawai Nonaktif</p>
      <div class="stat-trend"><span>Pensiun / Pindah</span></div>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="filter-section fade-in">
    <button class="filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#filterContent" aria-expanded="false">
      <span><i class="fas fa-filter me-2"></i>Filter & Pencarian Lanjutan</span>
      <i class="fas fa-chevron-down"></i>
    </button>

    <div class="collapse filter-content" id="filterContent">
      <form id="filterForm">
        <div class="filter-row">
          <div class="form-group">
            <label class="form-label"><i class="fas fa-search me-1"></i>Pencarian Nama/NIP</label>
            <input type="text" class="form-control" id="searchName" placeholder="Masukkan nama atau NIP...">
          </div>
          <div class="form-group">
            <label class="form-label"><i class="fas fa-venus-mars me-1"></i>Jenis Kelamin</label>
            <select class="form-control" id="filterGender">
              <option value="">Semua Jenis Kelamin</option>
              <option value="Laki-laki">Laki-laki</option>
              <option value="Perempuan">Perempuan</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label"><i class="fas fa-medal me-1"></i>Pangkat</label>
            <select class="form-control" id="filterPangkat">
              <option value="">Semua Pangkat</option>
              <?php foreach ($pangkat_options as $p): ?>
                <option value="<?= htmlspecialchars($p['pangkat_terakhir']) ?>"><?= htmlspecialchars($p['pangkat_terakhir']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="filter-row">
          <div class="form-group">
            <label class="form-label"><i class="fas fa-layer-group me-1"></i>Golongan</label>
            <select class="form-control" id="filterGolongan">
              <option value="">Semua Golongan</option>
              <?php foreach ($golongan_options as $g): ?>
                <option value="<?= htmlspecialchars($g['golongan']) ?>"><?= htmlspecialchars($g['golongan']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label"><i class="fas fa-briefcase me-1"></i>Jabatan</label>
            <select class="form-control" id="filterJabatan">
              <option value="">Semua Jabatan</option>
              <?php foreach ($jabatan_options as $j): ?>
                <option value="<?= htmlspecialchars($j['jabatan_terakhir']) ?>"><?= htmlspecialchars($j['jabatan_terakhir']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label"><i class="fas fa-graduation-cap me-1"></i>Pendidikan</label>
            <select class="form-control" id="filterPendidikan">
              <option value="">Semua Pendidikan</option>
              <?php foreach ($pendidikan_options as $p): ?>
                <option value="<?= htmlspecialchars($p['pendidikan_terakhir']) ?>"><?= htmlspecialchars($p['pendidikan_terakhir']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="filter-row">
          <div class="form-group">
            <label class="form-label"><i class="fas fa-toggle-on me-1"></i>Status Pegawai</label>
            <select class="form-control" id="filterStatus">
              <option value="">Semua Status</option>
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
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
          <span class="text-muted ms-auto" id="resultCount">Menampilkan <?= $total_rows ?> data</span>
        </div>
      </form>
    </div>
  </div>

  <!-- Table Section -->
  <div class="table-section fade-in">
    <div class="table-header">
      <h5 class="table-title"><i class="fas fa-table me-2"></i>Data Pegawai DUK</h5>
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

    <!-- ============================================================
         BULK ACTION BAR — hanya tampil jika superadmin
         ============================================================ -->
    <?php if ($is_superadmin): ?>
    <div class="bulk-action-bar" id="bulkActionBar">
      <div class="selected-info">
        <i class="fas fa-check-square me-1"></i>
        Terpilih: <span id="selectedCount">0</span> data
      </div>
      <button class="btn-hapus-terpilih" id="btnHapusTerpilih" onclick="hapusTerpilih()" disabled>
        <i class="fas fa-trash me-1"></i>Hapus Terpilih
      </button>
      <button class="btn-hapus-semua" onclick="hapusSemua()">
        <i class="fas fa-trash-alt me-1"></i>Hapus Semua
        <span class="badge-superadmin">SUPERADMIN</span>
      </button>
      <button class="btn btn-sm btn-outline-secondary" onclick="batalPilih()">
        <i class="fas fa-times me-1"></i>Batal
      </button>
    </div>

    <!-- Tombol aktifkan mode pilih (muncul di luar bulk bar) -->
    <div class="mb-2" id="triggerBulkWrap">
      <button class="btn btn-sm btn-outline-danger" onclick="aktifkanModePilih()" id="btnAktifkanPilih">
        <i class="fas fa-check-square me-1"></i>Pilih untuk Hapus
        <span class="badge-superadmin">SUPERADMIN</span>
      </button>
    </div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-hover" id="dukTable">
        <thead>
          <tr>
            <?php if ($is_superadmin): ?>
            <th class="col-check" id="thCheck" style="display:none;">
              <input type="checkbox" id="checkAll" title="Pilih semua">
            </th>
            <?php endif; ?>
            <th>Pegawai</th>
            <th>Pangkat/Gol</th>
            <th>Jabatan</th>
            <th>TTL</th>
            <th>Jenis Kelamin</th>
            <th>Pendidikan</th>
            <th>TMT/Eselon</th>
            <th>Jenis Jabatan</th>
            <th>Status</th>
            <th width="120">Aksi</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <?php
          $result_duk->data_seek(0);
          if ($result_duk && $result_duk->num_rows > 0):
            while ($row = $result_duk->fetch_assoc()):
              $initials = strtoupper(substr($row['nama'], 0, 1));
              if (strpos($row['nama'], ' ') !== false) {
                $nama_parts = explode(' ', $row['nama']);
                $initials = strtoupper(substr($nama_parts[0], 0, 1) . substr(end($nama_parts), 0, 1));
              }
              $genderClass = $row['jenis_kelamin'] === 'Laki-laki' ? 'primary' : 'warning';
              $genderIcon  = $row['jenis_kelamin'] === 'Laki-laki' ? 'fa-mars' : 'fa-venus';

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

              $status        = $row['status_pegawai'] ?? 'aktif';
              $alasan        = $row['alasan_nonaktif'] ?? '';
              $nonaktif_at   = $row['nonaktif_at'] ?? '';
              $tooltip_nonaktif = '';
              if ($status === 'nonaktif' && !empty($nonaktif_at)) {
                $tooltip_nonaktif = 'Nonaktif sejak ' . date('d/m/Y', strtotime($nonaktif_at)) . ' - ' . $alasan;
              }
          ?>
            <tr data-id="<?= $row['id'] ?>"
                data-nama="<?= htmlspecialchars($row['nama']) ?>"
                data-eselon="<?= $row['eselon'] ?>"
                data-jenis-jabatan="<?= $row['jenis_jabatan'] ?? '' ?>"
                data-jft-tingkat="<?= $row['jft_tingkat'] ?? '' ?>"
                data-jfu-kelas="<?= $row['jfu_kelas'] ?? '' ?>"
                data-status="<?= $status ?>"
                <?= $status === 'nonaktif' ? 'style="opacity: 0.7; background-color: #f8f9fa;"' : '' ?>>

              <?php if ($is_superadmin): ?>
              <td class="col-check td-check" style="display:none;">
                <input type="checkbox" class="row-check" value="<?= $row['id'] ?>">
              </td>
              <?php endif; ?>

              <td>
                <div class="employee-info">
                  <div class="employee-avatar" style="<?= $status === 'nonaktif' ? 'background: #6c757d;' : '' ?>">
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
              <td><span class="fw-semibold"><?= htmlspecialchars($row['jabatan_terakhir'] ?: '-') ?></span></td>
              <td><small><?= htmlspecialchars($row['ttl'] ?: '-') ?></small></td>
              <td>
                <span class="badge badge-<?= $genderClass ?>">
                  <i class="fas <?= $genderIcon ?> me-1"></i><?= htmlspecialchars($row['jenis_kelamin']) ?>
                </span>
              </td>
              <td><span class="badge badge-info"><?= htmlspecialchars($row['pendidikan_terakhir'] ?: '-') ?></span></td>
              <td>
                <div>
                  <small><strong>TMT:</strong> <?= htmlspecialchars($row['tmt_pangkat_awal'] ?: '-') ?></small>
                  <br><small><strong>Eselon:</strong> <?= htmlspecialchars($row['eselon'] ?: '-') ?></small>
                </div>
              </td>
              <td><?= $jenis_jabatan_display ?></td>
              <td>
                <?php if ($status === 'aktif'): ?>
                  <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Aktif</span>
                <?php else: ?>
                  <span class="badge bg-secondary"
                        data-bs-toggle="tooltip"
                        title="<?= htmlspecialchars($tooltip_nonaktif) ?>">
                    <i class="fas fa-user-slash me-1"></i>Nonaktif
                  </span>
                  <?php if (!empty($alasan)): ?>
                    <br><small class="text-muted"><?= htmlspecialchars($alasan) ?></small>
                  <?php endif; ?>
                <?php endif; ?>
              </td>
              <td>
                <div class="action-buttons" style="display:flex; flex-direction:column; gap:4px; align-items:stretch;">
                  <a href="detail_duk.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm" title="Lihat Detail">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="form_edit_duk.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm" title="Edit Data">
                    <i class="fas fa-edit"></i>
                  </a>
                  <?php if ($status === 'aktif'): ?>
                    <button type="button" class="btn btn-sm"
                            style="background-color:#fd7e14; color:white;"
                            title="Nonaktifkan Pegawai"
                            onclick="bukaNonaktif(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama'])) ?>')">
                      <i class="fas fa-user-slash"></i>
                    </button>
                  <?php else: ?>
                    <button type="button" class="btn btn-success btn-sm"
                            title="Aktifkan Kembali"
                            onclick="bukaReaktif(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama'])) ?>')">
                      <i class="fas fa-user-check"></i>
                    </button>
                  <?php endif; ?>
                  <a href="proses_hapus_duk.php?id=<?= $row['id'] ?>"
                     class="btn btn-danger btn-sm" title="Hapus Data"
                     onclick="return konfirmasiHapus(event, this.href, '<?= htmlspecialchars($row['nama']) ?>')">
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
              <td colspan="<?= $is_superadmin ? 11 : 10 ?>" class="empty-state">
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

<!-- ============================================================
     MODAL NONAKTIFKAN
     ============================================================ -->
<div class="modal fade" id="modalNonaktif" tabindex="-1" aria-labelledby="modalNonaktifLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#fd7e14; color:white;">
        <h5 class="modal-title" id="modalNonaktifLabel">
          <i class="fas fa-user-slash me-2"></i>Nonaktifkan Pegawai
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="proses_nonaktif_duk.php" method="POST">
        <div class="modal-body">
          <input type="hidden" name="id" id="nonaktif_id">
          <input type="hidden" name="aksi" value="nonaktif">
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Anda akan menonaktifkan pegawai: <strong id="nonaktif_nama"></strong>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Alasan Nonaktif <span class="text-danger">*</span></label>
            <select name="alasan" class="form-select" required>
              <option value="">-- Pilih Alasan --</option>
              <option value="Pensiun">Pensiun</option>
              <option value="Pindah">Pindah</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Keterangan Tambahan <small class="text-muted">(opsional)</small></label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Pensiun per 1 Mei 2026..."></textarea>
          </div>
          <div class="alert alert-danger mb-0">
            <i class="fas fa-info-circle me-2"></i>
            Pegawai ini <strong>tidak dapat diusulkan</strong> untuk kenaikan pangkat, SLKS, maupun pensiun selama <strong>6 bulan</strong>.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Batal</button>
          <button type="submit" class="btn btn-warning text-white"><i class="fas fa-user-slash me-2"></i>Ya, Nonaktifkan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================
     MODAL REAKTIFKAN
     ============================================================ -->
<div class="modal fade" id="modalReaktif" tabindex="-1" aria-labelledby="modalReaktifLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalReaktifLabel">
          <i class="fas fa-user-check me-2"></i>Aktifkan Kembali Pegawai
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="proses_nonaktif_duk.php" method="POST">
        <div class="modal-body">
          <input type="hidden" name="id" id="reaktif_id">
          <input type="hidden" name="aksi" value="reaktif">
          <div class="alert alert-success">
            <i class="fas fa-user-check me-2"></i>
            Aktifkan kembali pegawai: <strong id="reaktif_nama"></strong>?
          </div>
          <div class="mb-3">
            <label class="form-label">Keterangan <small class="text-muted">(opsional)</small></label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Alasan pengaktifan kembali..."></textarea>
          </div>
          <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-2"></i>
            Jika 6 bulan masa nonaktif belum selesai, hanya <strong>Superadmin</strong> yang dapat mengaktifkan kembali.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Batal</button>
          <button type="submit" class="btn btn-success"><i class="fas fa-user-check me-2"></i>Ya, Aktifkan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================
     MODAL KONFIRMASI HAPUS MASSAL
     ============================================================ -->
<div class="modal fade" id="modalKonfirmasiBulk" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="fas fa-exclamation-triangle me-2"></i>
          <span id="modalBulkJudul">Konfirmasi Hapus</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center py-4">
        <i class="fas fa-trash-alt fa-4x text-danger mb-3 d-block"></i>
        <p class="fs-6" id="modalBulkPesan"></p>
        <div class="alert alert-warning text-start mt-3">
          <i class="fas fa-info-circle me-2"></i>
          Data akan dipindahkan ke <strong>Recycle Bin</strong> dan masih bisa dipulihkan.
        </div>
        <!-- Input konfirmasi ketik -->
        <div class="mt-3 text-start">
          <label class="form-label fw-bold text-danger">
            Ketik <code>HAPUS</code> untuk konfirmasi:
          </label>
          <input type="text" id="inputKonfirmasiBulk" class="form-control border-danger"
                 placeholder="Ketik HAPUS di sini...">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-danger" id="btnKonfirmasiBulkOk" onclick="eksekusiHapusBulk()" disabled>
          <i class="fas fa-trash me-2"></i>Ya, Hapus ke Recycle Bin
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================
     Scripts
     ============================================================ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="js/dataduk.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof DUKExporter !== 'undefined') {
        window.dukExporter = new DUKExporter(originalData);
    }

    // Init Bootstrap Tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

    // ============================================================
    // CHECKBOX: Check All
    // ============================================================
    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            // Hanya ceklis baris yang sedang tampil (tidak hidden oleh filter)
            document.querySelectorAll('#tableBody tr:not([style*="display: none"]) .row-check').forEach(cb => {
                cb.checked = this.checked;
                cb.closest('tr').classList.toggle('row-selected', this.checked);
            });
            updateBulkBar();
        });
    }

    // Delegasi event untuk checkbox per baris
    document.getElementById('tableBody').addEventListener('change', function (e) {
        if (e.target.classList.contains('row-check')) {
            e.target.closest('tr').classList.toggle('row-selected', e.target.checked);
            updateBulkBar();
            // Sinkronkan checkAll
            const allVisible   = document.querySelectorAll('#tableBody tr:not([style*="display: none"]) .row-check');
            const allChecked   = document.querySelectorAll('#tableBody tr:not([style*="display: none"]) .row-check:checked');
            if (checkAll) checkAll.checked = allVisible.length > 0 && allVisible.length === allChecked.length;
        }
    });

    // Input konfirmasi bulk — aktifkan tombol hanya jika ketik "HAPUS"
    const inputKonfirmasi = document.getElementById('inputKonfirmasiBulk');
    const btnOk = document.getElementById('btnKonfirmasiBulkOk');
    if (inputKonfirmasi && btnOk) {
        inputKonfirmasi.addEventListener('input', function () {
            btnOk.disabled = this.value.trim() !== 'HAPUS';
        });
    }
});

// ============================================================
// MODE PILIH: aktifkan/nonaktifkan kolom checkbox
// ============================================================
let modePilih = false;

function aktifkanModePilih() {
    modePilih = true;
    // Tampilkan kolom checkbox
    document.getElementById('thCheck').style.display = '';
    document.querySelectorAll('.td-check').forEach(td => td.style.display = '');
    // Tampilkan bulk action bar, sembunyikan trigger button
    document.getElementById('bulkActionBar').classList.add('show');
    document.getElementById('triggerBulkWrap').style.display = 'none';
    updateBulkBar();
}

function batalPilih() {
    modePilih = false;
    // Reset semua checkbox
    document.querySelectorAll('.row-check').forEach(cb => {
        cb.checked = false;
        cb.closest('tr').classList.remove('row-selected');
    });
    const checkAll = document.getElementById('checkAll');
    if (checkAll) checkAll.checked = false;
    // Sembunyikan kolom & bar
    document.getElementById('thCheck').style.display = 'none';
    document.querySelectorAll('.td-check').forEach(td => td.style.display = 'none');
    document.getElementById('bulkActionBar').classList.remove('show');
    document.getElementById('triggerBulkWrap').style.display = '';
}

function updateBulkBar() {
    const checked = document.querySelectorAll('.row-check:checked');
    const count   = checked.length;
    const el      = document.getElementById('selectedCount');
    const btn     = document.getElementById('btnHapusTerpilih');
    if (el)  el.textContent = count;
    if (btn) btn.disabled   = count === 0;
}

// ============================================================
// STATE HAPUS BULK — simpan jenis aksi sebelum konfirmasi
// ============================================================
let _bulkAksi = ''; // 'terpilih' | 'semua'

// ============================================================
// HAPUS TERPILIH
// ============================================================
function hapusTerpilih() {
    const checked = document.querySelectorAll('.row-check:checked');
    if (checked.length === 0) {
        Swal.fire('Perhatian', 'Pilih minimal 1 data terlebih dahulu.', 'warning');
        return;
    }
    _bulkAksi = 'terpilih';
    const jumlah = checked.length;

    // Kumpulkan nama untuk preview (max 3)
    const namaList = [];
    checked.forEach(cb => {
        const nama = cb.closest('tr').dataset.nama || '(tanpa nama)';
        if (namaList.length < 3) namaList.push(`• ${nama}`);
    });
    const extra = jumlah > 3 ? `<br><small class="text-muted">...dan ${jumlah - 3} lainnya</small>` : '';

    document.getElementById('modalBulkJudul').textContent = 'Hapus Data Terpilih';
    document.getElementById('modalBulkPesan').innerHTML =
        `Anda akan memindahkan <strong>${jumlah} data</strong> ke Recycle Bin:<br>
         <small class="text-muted">${namaList.join('<br>')}</small>${extra}`;
    document.getElementById('inputKonfirmasiBulk').value = '';
    document.getElementById('btnKonfirmasiBulkOk').disabled = true;

    new bootstrap.Modal(document.getElementById('modalKonfirmasiBulk')).show();
}

// ============================================================
// HAPUS SEMUA
// ============================================================
function hapusSemua() {
    _bulkAksi = 'semua';
    const totalTampil = document.querySelectorAll('#tableBody tr[data-id]').length;

    document.getElementById('modalBulkJudul').textContent = 'Hapus Semua Data DUK';
    document.getElementById('modalBulkPesan').innerHTML =
        `Anda akan memindahkan <strong>seluruh ${totalTampil} data DUK</strong> ke Recycle Bin.<br>
         <small class="text-danger fw-bold">Tindakan ini akan mengosongkan seluruh tabel DUK!</small>`;
    document.getElementById('inputKonfirmasiBulk').value = '';
    document.getElementById('btnKonfirmasiBulkOk').disabled = true;

    new bootstrap.Modal(document.getElementById('modalKonfirmasiBulk')).show();
}

// ============================================================
// EKSEKUSI HAPUS BULK (setelah konfirmasi)
// ============================================================
function eksekusiHapusBulk() {
    const payload = { aksi: _bulkAksi, ids: [] };

    if (_bulkAksi === 'terpilih') {
        document.querySelectorAll('.row-check:checked').forEach(cb => {
            payload.ids.push(parseInt(cb.value));
        });
        if (payload.ids.length === 0) return;
    }

    // Tutup modal konfirmasi
    bootstrap.Modal.getInstance(document.getElementById('modalKonfirmasiBulk')).hide();

    // Tampilkan loading
    Swal.fire({
        title: 'Memproses...',
        text: 'Sedang memindahkan data ke Recycle Bin.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch('proses_hapus_massal_duk.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                html: data.message,
                confirmButtonText: 'OK'
            }).then(() => {
                if (_bulkAksi === 'semua') {
                    // Reload halaman
                    location.reload();
                } else {
                    // Hapus baris yang sudah didelete dari DOM
                    data.ids.forEach(id => {
                        const row = document.querySelector(`tr[data-id="${id}"]`);
                        if (row) row.remove();
                    });
                    batalPilih();
                    // Update counter result
                    const sisa = document.querySelectorAll('#tableBody tr[data-id]').length;
                    const el   = document.getElementById('resultCount');
                    if (el) el.textContent = `Menampilkan ${sisa} data`;
                }
            });
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    })
    .catch(() => {
        Swal.fire('Error', 'Terjadi kesalahan koneksi. Coba lagi.', 'error');
    });
}

// ============================================================
// Modal nonaktif & reaktif (sama seperti sebelumnya)
// ============================================================
function bukaNonaktif(id, nama) {
    document.getElementById('nonaktif_id').value = id;
    document.getElementById('nonaktif_nama').textContent = nama;
    document.querySelector('#modalNonaktif select[name="alasan"]').value = '';
    document.querySelector('#modalNonaktif textarea[name="keterangan"]').value = '';
    new bootstrap.Modal(document.getElementById('modalNonaktif')).show();
}

function bukaReaktif(id, nama) {
    document.getElementById('reaktif_id').value = id;
    document.getElementById('reaktif_nama').textContent = nama;
    document.querySelector('#modalReaktif textarea[name="keterangan"]').value = '';
    new bootstrap.Modal(document.getElementById('modalReaktif')).show();
}

// ============================================================
// Filter by card (support nonaktif)
// ============================================================
function filterByCard(type) {
    const filterStatus = document.getElementById('filterStatus');
    const filterGender = document.getElementById('filterGender');
    if (filterStatus) filterStatus.value = '';
    if (filterGender) filterGender.value = '';
    if (type === 'laki')      { if (filterGender) filterGender.value = 'Laki-laki'; }
    if (type === 'perempuan') { if (filterGender) filterGender.value = 'Perempuan'; }
    if (type === 'nonaktif')  { if (filterStatus) filterStatus.value = 'nonaktif'; }
    applyFilter();
}
</script>

<?php require_once 'includes/footer.php'; ?>