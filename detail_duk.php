<?php
session_start();
require_once 'check_session.php';
if (!isAdmin()) { header('Location: dashboard.php?error=Akses ditolak'); exit; }
require_once 'config/koneksi.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$id = $_GET['id'] ?? 0;
if (!$id || !is_numeric($id)) {
    header('Location: dataduk.php?error=ID tidak valid');
    exit;
}

// ── Data DUK ─────────────────────────────────────────────────────────────────
$stmt = $koneksi->prepare("SELECT * FROM duk WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) { header('Location: dataduk.php?error=Data tidak ditemukan'); exit; }
$duk = $result->fetch_assoc();
$stmt->close();

// ── Riwayat Kenaikan Pangkat ──────────────────────────────────────────────────
$stmt_kp = $koneksi->prepare(
    "SELECT id, nomor_usulan, tanggal_usulan, pangkat_lama, golongan_lama,
            pangkat_baru, golongan_baru, tmt_pangkat_baru, jabatan_baru, status, jenis_kenaikan
     FROM kenaikan_pangkat
     WHERE nip = ? AND deleted_at IS NULL
     ORDER BY tmt_pangkat_baru ASC"
);
$stmt_kp->bind_param("s", $duk['nip']);
$stmt_kp->execute();
$riwayat_kp = $stmt_kp->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_kp->close();

// ── Usulan Pensiun ────────────────────────────────────────────────────────────
$stmt_up = $koneksi->prepare(
    "SELECT id, nomor_usulan, tanggal_usulan, tanggal_pensiun, jenis_pensiun, status,
            DATEDIFF(tanggal_pensiun, CURDATE()) as hari_tersisa
     FROM usulan_pensiun
     WHERE nip = ? AND deleted_at IS NULL
     ORDER BY created_at DESC LIMIT 1"
);
$stmt_up->bind_param("s", $duk['nip']);
$stmt_up->execute();
$pensiun = $stmt_up->get_result()->fetch_assoc();
$stmt_up->close();

// ── Helpers ───────────────────────────────────────────────────────────────────
function hitungUsia($ttl) {
    preg_match('/(\d{4})/', $ttl, $m);
    if (!empty($m[1])) { $u = date('Y') - $m[1]; return ($u > 0 && $u < 100) ? $u : null; }
    return null;
}
function masaKerja($tmt) {
    if (empty($tmt)) return null;
    $i = (new DateTime($tmt))->diff(new DateTime());
    return $i->y . ' tahun ' . $i->m . ' bulan';
}
$bulan_indo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
               7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
function formatTglIndo($tgl, $bulan_indo) {
    if (empty($tgl)) return '-';
    $ts = strtotime($tgl);
    return date('d', $ts) . ' ' . $bulan_indo[(int)date('m', $ts)] . ' ' . date('Y', $ts);
}

$usia             = hitungUsia($duk['ttl']);
$masaKerjaPangkat = masaKerja($duk['tmt_pangkat']);
$masaKerjaEselon  = masaKerja($duk['tmt_eselon']);
$status_pegawai   = $duk['status_pegawai'] ?? 'aktif';

// Inisial avatar
$parts    = array_filter(explode(' ', $duk['nama']));
$initials = strtoupper(substr(reset($parts), 0, 1) . substr(end($parts), 0, 1));

// Jenis jabatan display
$jenis_jabatan_display = htmlspecialchars($duk['eselon'] ?: 'Non-Eselon');
if ($duk['eselon'] === 'Non-Eselon') {
    if ($duk['jenis_jabatan'] === 'JFT' && !empty($duk['jft_tingkat']))
        $jenis_jabatan_display = 'JFT – ' . htmlspecialchars($duk['jft_tingkat']);
    elseif ($duk['jenis_jabatan'] === 'JFU' && !empty($duk['jfu_kelas']))
        $jenis_jabatan_display = 'JFU – Kelas ' . htmlspecialchars($duk['jfu_kelas']);
}
?>

<link rel="stylesheet" href="css/detail_duk.css">

<!-- Tambahan minimal: hanya yang tidak ada di detail_duk.css -->
<style>
/* Modal */
.modal-overlay {
  display: none; position: fixed; inset: 0; z-index: 9999;
  background: rgba(0,0,0,.6); backdrop-filter: blur(3px);
  align-items: center; justify-content: center;
}
.modal-overlay.show { display: flex; }
.modal-box {
  background: #fff; border-radius: 16px; padding: 28px;
  max-width: 460px; width: 92%;
  box-shadow: 0 20px 60px rgba(0,0,0,.25);
  animation: popIn .25s cubic-bezier(.34,1.56,.64,1);
}
@keyframes popIn { from{transform:scale(.85);opacity:0} to{transform:scale(1);opacity:1} }
.modal-box h5 { color: #2c3e50; margin: 0 0 8px; font-size: 1.1rem; }
.modal-box p  { color: #6c757d; font-size: .9rem; margin: 0 0 16px; line-height: 1.6; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 8px; }

/* Share preview */
.share-preview {
  background: #f8f9fa; border: 1px solid #dee2e6;
  border-radius: 10px; padding: 16px; margin-bottom: 16px; font-size: .88rem;
}
.share-preview .sp-name { font-size: 1rem; font-weight: 700; color: #212529; margin-bottom: 6px; }
.share-preview .sp-row  { color: #495057; margin: 3px 0; }

/* Toast */
.toast-copied {
  position: fixed; bottom: 28px; left: 50%;
  transform: translateX(-50%) translateY(60px);
  background: #28a745; color: #fff;
  padding: 10px 24px; border-radius: 999px;
  font-size: .88rem; font-weight: 600; opacity: 0;
  transition: all .3s cubic-bezier(.34,1.56,.64,1);
  z-index: 10000; pointer-events: none;
}
.toast-copied.show { opacity: 1; transform: translateX(-50%) translateY(0); }

/* Delete input */
.delete-input {
  width: 100%; padding: 10px 14px; border-radius: 8px;
  border: 1.5px solid #dee2e6; font-size: .9rem;
  margin-bottom: 4px; outline: none; transition: border-color .2s;
}
.delete-input:focus { border-color: #dc3545; box-shadow: 0 0 0 3px rgba(220,53,69,.1); }

/* Status / badge kecil */
.badge-sm {
  display: inline-block; padding: 2px 10px; border-radius: 999px;
  font-size: .72rem; font-weight: 600;
}
.badge-sm.draft     { background: #e9ecef; color: #6c757d; }
.badge-sm.diajukan  { background: #cce5ff; color: #004085; }
.badge-sm.disetujui { background: #d4edda; color: #155724; }
.badge-sm.ditolak   { background: #f8d7da; color: #721c24; }

/* Countdown pensiun */
.pensiun-countdown { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; }
.countdown-unit {
  background: #fff3cd; border: 1px solid #ffc107;
  border-radius: 8px; padding: 8px 14px; text-align: center; min-width: 60px;
}
.countdown-unit .cnt-num { font-size: 1.3rem; font-weight: 800; color: #856404; display: block; }
.countdown-unit .cnt-lbl { font-size: .62rem; color: #856404; text-transform: uppercase; }
</style>

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
              <a href="dataduk.php"><i class="fas fa-users"></i> Data DUK</a>
            </li>
            <li class="breadcrumb-item active">Detail Pegawai</li>
          </ol>
        </nav>
        <div class="page-actions">
          <a href="dataduk.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
          </a>
          <a href="form_edit_duk.php?id=<?= $id ?>" class="btn btn-warning btn-sm">
            <i class="fas fa-edit me-1"></i>Edit Data
          </a>
        </div>
      </div>
    </div>

    <div class="row">

      <!-- ── Profile Card (kiri) ────────────────────────────────────────── -->
      <div class="col-lg-4 col-xl-3">
        <div class="profile-card">
          <div class="profile-header">
            <div class="profile-avatar">
              <div class="avatar-circle"><?= $initials ?></div>
              <div class="status-indicator online"
                   data-bs-toggle="tooltip"
                   title="<?= $status_pegawai === 'aktif' ? 'Pegawai Aktif' : 'Pegawai Nonaktif' ?>"
                   style="background: <?= $status_pegawai === 'aktif' ? '#28a745' : '#dc3545' ?>">
              </div>
            </div>
            <h4 class="profile-name"><?= htmlspecialchars($duk['nama']) ?></h4>
            <p class="profile-nip">
              <i class="fas fa-id-card me-1"></i>
              <?= $duk['nip'] ?: 'NIP belum diisi' ?>
            </p>
            <div class="profile-badges">
              <span class="badge <?= $duk['jenis_kelamin'] === 'Laki-laki' ? 'badge-primary' : 'badge-warning' ?>">
                <i class="fas fa-<?= $duk['jenis_kelamin'] === 'Laki-laki' ? 'mars' : 'venus' ?> me-1"></i>
                <?= htmlspecialchars($duk['jenis_kelamin'] ?? '-') ?>
              </span>
              <?php if ($usia): ?>
              <span class="badge badge-info">
                <i class="fas fa-birthday-cake me-1"></i><?= $usia ?> Tahun
              </span>
              <?php endif; ?>
              <span class="badge <?= $status_pegawai === 'aktif' ? 'badge-success' : '' ?>"
                    <?= $status_pegawai === 'nonaktif' ? 'style="background:linear-gradient(135deg,#dc3545,#c82333)"' : '' ?>>
                <i class="fas fa-<?= $status_pegawai === 'aktif' ? 'check-circle' : 'user-slash' ?> me-1"></i>
                <?= $status_pegawai === 'aktif' ? 'Aktif' : 'Nonaktif' ?>
              </span>
            </div>
          </div>

          <div class="profile-stats">
            <div class="stat-item">
              <div class="stat-icon bg-primary"><i class="fas fa-star"></i></div>
              <div class="stat-content">
                <h6>Pangkat</h6>
                <p><?= htmlspecialchars($duk['pangkat_terakhir'] ?: '-') ?></p>
              </div>
            </div>
            <div class="stat-item">
              <div class="stat-icon bg-success"><i class="fas fa-layer-group"></i></div>
              <div class="stat-content">
                <h6>Golongan</h6>
                <p><?= htmlspecialchars($duk['golongan'] ?: '-') ?></p>
              </div>
            </div>
            <div class="stat-item">
              <div class="stat-icon bg-warning"><i class="fas fa-crown"></i></div>
              <div class="stat-content">
                <h6>Eselon</h6>
                <p><?= htmlspecialchars($duk['eselon'] ?: 'Non-Eselon') ?></p>
              </div>
            </div>
            <?php if ($pensiun): ?>
            <div class="stat-item">
              <div class="stat-icon" style="background:linear-gradient(135deg,#fd7e14,#e8560a)">
                <i class="fas fa-user-clock"></i>
              </div>
              <div class="stat-content">
                <h6>Pensiun</h6>
                <p><?= formatTglIndo($pensiun['tanggal_pensiun'], $bulan_indo) ?></p>
              </div>
            </div>
            <?php endif; ?>
          </div>

          <div class="profile-actions">
            <button class="btn btn-outline-primary btn-block" onclick="openShare()">
              <i class="fas fa-share-alt me-2"></i>Bagikan Profil
            </button>
            <button class="btn btn-outline-danger btn-block" onclick="openDelete()">
              <i class="fas fa-trash me-2"></i>Hapus Data
            </button>
          </div>
        </div>
      </div>

      <!-- ── Detail Tabs (kanan) ────────────────────────────────────────── -->
      <div class="col-lg-8 col-xl-9">
        <div class="detail-card">
          <div class="card-header">
            <ul class="nav nav-tabs card-tabs" role="tablist">
              <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-personal">
                  <i class="fas fa-user me-2"></i>Data Pribadi
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-career">
                  <i class="fas fa-briefcase me-2"></i>Karir & Jabatan
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-education">
                  <i class="fas fa-graduation-cap me-2"></i>Pendidikan
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-timeline">
                  <i class="fas fa-history me-2"></i>Timeline
                  <?php $total_event = count($riwayat_kp) + ($pensiun ? 1 : 0); ?>
                  <?php if ($total_event > 0): ?>
                  <span class="badge badge-info" style="font-size:.65rem;padding:2px 7px;margin-left:4px;">
                    <?= $total_event ?>
                  </span>
                  <?php endif; ?>
                </button>
              </li>
            </ul>
          </div>

          <div class="card-body">
            <div class="tab-content">

              <!-- ── Data Pribadi ───────────────────────────────────────── -->
              <div class="tab-pane fade show active" id="tab-personal">
                <div class="info-section">
                  <h6 class="section-title">
                    <i class="fas fa-user-circle me-2"></i>Informasi Pribadi
                  </h6>
                  <div class="info-grid">
                    <div class="info-item">
                      <div class="info-label"><i class="fas fa-user me-2"></i>Nama Lengkap</div>
                      <div class="info-value"><?= htmlspecialchars($duk['nama']) ?></div>
                    </div>
                    <div class="info-item">
                      <div class="info-label"><i class="fas fa-id-card me-2"></i>NIP</div>
                      <div class="info-value"><?= htmlspecialchars($duk['nip'] ?: '-') ?></div>
                    </div>
                    <div class="info-item">
                      <div class="info-label"><i class="fas fa-id-badge me-2"></i>Kartu Pegawai</div>
                      <div class="info-value"><?= htmlspecialchars($duk['kartu_pegawai'] ?: '-') ?></div>
                    </div>
                    <div class="info-item">
                      <div class="info-label"><i class="fas fa-map-marker-alt me-2"></i>Tempat, Tanggal Lahir</div>
                      <div class="info-value">
                        <?= htmlspecialchars($duk['ttl'] ?: '-') ?>
                        <?php if ($usia): ?>
                          <small class="text-muted d-block">Usia: <?= $usia ?> tahun</small>
                        <?php endif; ?>
                      </div>
                    </div>
                    <div class="info-item">
                      <div class="info-label"><i class="fas fa-venus-mars me-2"></i>Jenis Kelamin</div>
                      <div class="info-value"><?= htmlspecialchars($duk['jenis_kelamin'] ?? '-') ?></div>
                    </div>
                    <div class="info-item">
                      <div class="info-label"><i class="fab fa-whatsapp me-2"></i>Nomor WhatsApp</div>
                      <div class="info-value">
                        <?php if (!empty($duk['nomor_wa'])): ?>
                          <a href="https://wa.me/<?= $duk['nomor_wa'] ?>" target="_blank" class="text-success">
                            <?= htmlspecialchars($duk['nomor_wa']) ?>
                            <i class="fas fa-external-link-alt ms-1" style="font-size:.7rem"></i>
                          </a>
                        <?php else: ?>-<?php endif; ?>
                      </div>
                    </div>
                    <div class="info-item">
                      <div class="info-label"><i class="fas fa-toggle-on me-2"></i>Status Pegawai</div>
                      <div class="info-value">
                        <span class="badge <?= $status_pegawai === 'aktif' ? 'badge-success' : '' ?>"
                              <?= $status_pegawai === 'nonaktif' ? 'style="background:linear-gradient(135deg,#dc3545,#c82333)"' : '' ?>>
                          <?= $status_pegawai === 'aktif' ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                        <?php if ($status_pegawai === 'nonaktif' && !empty($duk['alasan_nonaktif'])): ?>
                          <small class="text-muted d-block mt-1">
                            Alasan: <?= htmlspecialchars($duk['alasan_nonaktif']) ?>
                            <?php if (!empty($duk['nonaktif_at'])): ?>
                              · sejak <?= formatTglIndo($duk['nonaktif_at'], $bulan_indo) ?>
                            <?php endif; ?>
                          </small>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ── Karir & Jabatan ────────────────────────────────────── -->
              <div class="tab-pane fade" id="tab-career">
                <div class="info-section">
                  <h6 class="section-title"><i class="fas fa-medal me-2"></i>Kepangkatan</h6>
                  <div class="career-timeline">
                    <div class="timeline-item">
                      <div class="timeline-marker bg-primary"><i class="fas fa-star"></i></div>
                      <div class="timeline-content">
                        <h6>Pangkat Terakhir</h6>
                        <p><strong><?= htmlspecialchars($duk['pangkat_terakhir'] ?: '-') ?></strong></p>
                        <small class="text-muted">
                          <i class="fas fa-calendar me-1"></i>
                          TMT: <?= formatTglIndo($duk['tmt_pangkat'], $bulan_indo) ?>
                          <?php if ($masaKerjaPangkat): ?> · Masa kerja: <?= $masaKerjaPangkat ?><?php endif; ?>
                        </small>
                      </div>
                    </div>
                    <div class="timeline-item">
                      <div class="timeline-marker bg-success"><i class="fas fa-layer-group"></i></div>
                      <div class="timeline-content">
                        <h6>Golongan</h6>
                        <p><strong><?= htmlspecialchars($duk['golongan'] ?: '-') ?></strong></p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="info-section">
                  <h6 class="section-title"><i class="fas fa-briefcase me-2"></i>Jabatan & Eselon</h6>
                  <div class="career-timeline">
                    <div class="timeline-item">
                      <div class="timeline-marker bg-info"><i class="fas fa-user-tie"></i></div>
                      <div class="timeline-content">
                        <h6>Jabatan Terakhir</h6>
                        <p><strong><?= htmlspecialchars($duk['jabatan_terakhir'] ?: '-') ?></strong></p>
                      </div>
                    </div>
                    <div class="timeline-item">
                      <div class="timeline-marker bg-warning"><i class="fas fa-crown"></i></div>
                      <div class="timeline-content">
                        <h6>Eselon / Jenis Jabatan</h6>
                        <p><strong><?= $jenis_jabatan_display ?></strong></p>
                        <small class="text-muted">
                          <i class="fas fa-calendar me-1"></i>
                          TMT: <?= formatTglIndo($duk['tmt_eselon'], $bulan_indo) ?>
                          <?php if ($masaKerjaEselon): ?> · Masa kerja: <?= $masaKerjaEselon ?><?php endif; ?>
                        </small>
                      </div>
                    </div>
                  </div>
                </div>

                <?php if ($pensiun): ?>
                <div class="info-section">
                  <h6 class="section-title"><i class="fas fa-user-clock me-2"></i>Usulan Pensiun</h6>
                  <div class="info-item">
                    <div class="info-label">
                      <?= htmlspecialchars($pensiun['nomor_usulan']) ?> &nbsp;
                      <span class="badge-sm <?= $pensiun['status'] ?>"><?= ucfirst($pensiun['status']) ?></span>
                    </div>
                    <div class="info-value">
                      Pensiun: <strong><?= formatTglIndo($pensiun['tanggal_pensiun'], $bulan_indo) ?></strong>
                      · <?= htmlspecialchars($pensiun['jenis_pensiun']) ?>
                    </div>
                    <?php
                    $hari = (int)$pensiun['hari_tersisa'];
                    if ($hari > 0):
                      $th = floor($hari / 365); $bl = floor(($hari % 365) / 30); $hr = $hari % 30;
                    ?>
                    <div class="pensiun-countdown">
                      <?php if ($th > 0): ?>
                      <div class="countdown-unit">
                        <span class="cnt-num"><?= $th ?></span><span class="cnt-lbl">Tahun</span>
                      </div>
                      <?php endif; ?>
                      <?php if ($bl > 0): ?>
                      <div class="countdown-unit">
                        <span class="cnt-num"><?= $bl ?></span><span class="cnt-lbl">Bulan</span>
                      </div>
                      <?php endif; ?>
                      <div class="countdown-unit">
                        <span class="cnt-num"><?= $hr ?></span><span class="cnt-lbl">Hari</span>
                      </div>
                    </div>
                    <?php else: ?>
                      <div class="mt-2">
                        <span class="badge-sm ditolak">
                          <i class="fas fa-flag-checkered me-1"></i>Sudah melewati tanggal pensiun
                        </span>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
                <?php endif; ?>
              </div>

              <!-- ── Pendidikan ─────────────────────────────────────────── -->
              <div class="tab-pane fade" id="tab-education">
                <div class="info-section">
                  <h6 class="section-title">
                    <i class="fas fa-graduation-cap me-2"></i>Pendidikan Terakhir
                  </h6>
                  <div class="education-card">
                    <div class="education-icon"><i class="fas fa-university"></i></div>
                    <div class="education-content">
                      <p class="text-muted mb-0">Tingkat Pendidikan</p>
                      <h5><?= htmlspecialchars($duk['pendidikan_terakhir'] ?: '-') ?></h5>
                    </div>
                  </div>
                  <div class="education-card">
                    <div class="education-icon"><i class="fas fa-book"></i></div>
                    <div class="education-content">
                      <p class="text-muted mb-0">Program Studi</p>
                      <h5><?= htmlspecialchars($duk['prodi'] ?: '-') ?></h5>
                    </div>
                  </div>
                  <?php if (!empty($duk['jenis_jabatan'])): ?>
                  <div class="education-card">
                    <div class="education-icon"><i class="fas fa-briefcase"></i></div>
                    <div class="education-content">
                      <p class="text-muted mb-0">Jenis Jabatan Fungsional</p>
                      <h5><?= $jenis_jabatan_display ?></h5>
                    </div>
                  </div>
                  <?php endif; ?>
                </div>
              </div>

              <!-- ── Timeline ───────────────────────────────────────────── -->
              <div class="tab-pane fade" id="tab-timeline">
                <div class="info-section">
                  <h6 class="section-title">
                    <i class="fas fa-history me-2"></i>Riwayat Karir Pegawai
                  </h6>

                  <div class="timeline-vertical">

                    <!-- Terdaftar -->
                    <div class="timeline-event">
                      <div class="timeline-date"><?= date('Y', strtotime($duk['created_at'])) ?></div>
                      <div class="timeline-marker bg-success"><i class="fas fa-plus"></i></div>
                      <div class="timeline-content">
                        <h6>Terdaftar di Sistem DUK</h6>
                        <p><?= formatTglIndo($duk['created_at'], $bulan_indo) ?></p>
                        <small class="text-muted">Data pegawai pertama kali dimasukkan ke sistem kepegawaian</small>
                      </div>
                    </div>

                    <!-- Riwayat Kenaikan Pangkat (dari DB) -->
                    <?php foreach ($riwayat_kp as $kp): ?>
                    <div class="timeline-event">
                      <div class="timeline-date"><?= date('Y', strtotime($kp['tmt_pangkat_baru'])) ?></div>
                      <div class="timeline-marker" style="background:linear-gradient(135deg,#17a2b8,#138496)">
                        <i class="fas fa-arrow-up"></i>
                      </div>
                      <div class="timeline-content">
                        <h6>Kenaikan Pangkat – <?= htmlspecialchars($kp['jenis_kenaikan']) ?></h6>
                        <p>
                          <?= htmlspecialchars($kp['pangkat_lama']) ?> (<?= htmlspecialchars($kp['golongan_lama']) ?>)
                          &rarr;
                          <strong><?= htmlspecialchars($kp['pangkat_baru']) ?> (<?= htmlspecialchars($kp['golongan_baru']) ?>)</strong>
                        </p>
                        <small class="text-muted">
                          TMT: <?= formatTglIndo($kp['tmt_pangkat_baru'], $bulan_indo) ?>
                          · Jabatan: <?= htmlspecialchars($kp['jabatan_baru']) ?>
                          <br>No. <?= htmlspecialchars($kp['nomor_usulan']) ?>
                        </small>
                        <div class="mt-1">
                          <span class="badge-sm <?= $kp['status'] ?>"><?= ucfirst($kp['status']) ?></span>
                        </div>
                      </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Nonaktif (jika ada) -->
                    <?php if ($status_pegawai === 'nonaktif' && !empty($duk['nonaktif_at'])): ?>
                    <div class="timeline-event">
                      <div class="timeline-date"><?= date('Y', strtotime($duk['nonaktif_at'])) ?></div>
                      <div class="timeline-marker" style="background:linear-gradient(135deg,#dc3545,#c82333)">
                        <i class="fas fa-user-slash"></i>
                      </div>
                      <div class="timeline-content">
                        <h6>Dinonaktifkan</h6>
                        <p><?= formatTglIndo($duk['nonaktif_at'], $bulan_indo) ?></p>
                        <small class="text-muted">Alasan: <?= htmlspecialchars($duk['alasan_nonaktif'] ?? '-') ?></small>
                        <div class="mt-1">
                          <span class="badge-sm ditolak">Nonaktif</span>
                        </div>
                      </div>
                    </div>
                    <?php endif; ?>

                    <!-- Pensiun (jika ada) -->
                    <?php if ($pensiun): ?>
                    <div class="timeline-event">
                      <div class="timeline-date"><?= date('Y', strtotime($pensiun['tanggal_pensiun'])) ?></div>
                      <div class="timeline-marker" style="background:linear-gradient(135deg,#fd7e14,#e8560a)">
                        <i class="fas fa-flag-checkered"></i>
                      </div>
                      <div class="timeline-content">
                        <h6>Rencana Pensiun – <?= htmlspecialchars($pensiun['jenis_pensiun']) ?></h6>
                        <p>Tanggal: <strong><?= formatTglIndo($pensiun['tanggal_pensiun'], $bulan_indo) ?></strong></p>
                        <small class="text-muted">
                          No. <?= htmlspecialchars($pensiun['nomor_usulan']) ?>
                          <?php $h = (int)$pensiun['hari_tersisa']; ?>
                          <?php if ($h > 0): ?> · ⏳ <?= $h ?> hari lagi<?php else: ?> · Sudah melewati tanggal pensiun<?php endif; ?>
                        </small>
                        <div class="mt-1">
                          <span class="badge-sm <?= $pensiun['status'] ?>"><?= ucfirst($pensiun['status']) ?></span>
                        </div>
                      </div>
                    </div>
                    <?php endif; ?>

                    <!-- Kosong -->
                    <?php if (empty($riwayat_kp) && !$pensiun && $status_pegawai === 'aktif'): ?>
                    <div class="timeline-event">
                      <div class="timeline-date">-</div>
                      <div class="timeline-marker" style="background:#6c757d"><i class="fas fa-clock"></i></div>
                      <div class="timeline-content">
                        <h6 class="text-muted">Belum ada riwayat</h6>
                        <small class="text-muted">Kenaikan pangkat dan usulan pensiun belum tercatat</small>
                      </div>
                    </div>
                    <?php endif; ?>

                  </div><!-- /timeline-vertical -->
                </div>
              </div>

            </div><!-- /tab-content -->
          </div><!-- /card-body -->
        </div><!-- /detail-card -->
      </div><!-- /col -->

    </div><!-- /row -->
  </div><!-- /container -->
</main>

<!-- ── MODAL BAGIKAN ──────────────────────────────────────────────── -->
<div class="modal-overlay" id="modalShare">
  <div class="modal-box">
    <h5><i class="fas fa-share-alt me-2 text-info"></i>Bagikan Profil Pegawai</h5>
    <p>Salin teks ringkasan atau kirim langsung via WhatsApp.</p>
    <div class="share-preview" id="sharePreviewBox">
      <div class="sp-name">👤 <?= htmlspecialchars($duk['nama']) ?></div>
      <div class="sp-row"><strong>NIP:</strong> <?= htmlspecialchars($duk['nip'] ?: '-') ?></div>
      <div class="sp-row"><strong>Pangkat/Gol:</strong> <?= htmlspecialchars($duk['pangkat_terakhir'] ?: '-') ?> / <?= htmlspecialchars($duk['golongan'] ?: '-') ?></div>
      <div class="sp-row"><strong>Jabatan:</strong> <?= htmlspecialchars($duk['jabatan_terakhir'] ?: '-') ?></div>
      <div class="sp-row"><strong>Eselon:</strong> <?= htmlspecialchars($duk['eselon'] ?: '-') ?></div>
      <div class="sp-row"><strong>TTL:</strong> <?= htmlspecialchars($duk['ttl'] ?: '-') ?></div>
      <div class="sp-row"><strong>Pendidikan:</strong> <?= htmlspecialchars($duk['pendidikan_terakhir'] ?: '-') ?> – <?= htmlspecialchars($duk['prodi'] ?: '-') ?></div>
      <div class="sp-row"><strong>Status:</strong> <?= $status_pegawai === 'aktif' ? 'Aktif' : 'Nonaktif (' . htmlspecialchars($duk['alasan_nonaktif'] ?? '') . ')' ?></div>
    </div>
    <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
      <button class="btn btn-outline-primary btn-sm" onclick="copyShareText()">
        <i class="fas fa-copy me-1"></i>Salin Teks
      </button>
      <?php if (!empty($duk['nomor_wa'])): ?>
      <button class="btn btn-outline-success btn-sm" onclick="shareWhatsApp()">
        <i class="fab fa-whatsapp me-1"></i>Kirim WhatsApp
      </button>
      <?php endif; ?>
    </div>
    <div class="modal-actions">
      <button class="btn btn-secondary btn-sm" onclick="closeModal('modalShare')">
        <i class="fas fa-times me-1"></i>Tutup
      </button>
    </div>
  </div>
</div>

<!-- ── MODAL HAPUS ──────────────────────────────────────────────────── -->
<div class="modal-overlay" id="modalDelete">
  <div class="modal-box">
    <h5><i class="fas fa-trash me-2 text-danger"></i>Hapus Data Pegawai</h5>
    <p>
      Anda akan menghapus data <strong><?= htmlspecialchars($duk['nama']) ?></strong>.<br>
      Data akan masuk ke <strong>Recycle Bin</strong> dan dapat dipulihkan dalam 5 tahun.<br><br>
      Ketik <strong class="text-danger">HAPUS</strong> untuk konfirmasi:
    </p>
    <input type="text" id="deleteConfirmInput" class="delete-input"
           placeholder="Ketik HAPUS di sini...">
    <small class="text-muted d-block mb-3">Tombol hapus aktif setelah mengetik HAPUS</small>
    <div class="modal-actions">
      <button class="btn btn-secondary btn-sm" onclick="closeModal('modalDelete')">
        <i class="fas fa-times me-1"></i>Batal
      </button>
      <button class="btn btn-danger btn-sm" id="btnConfirmDelete" onclick="doDelete()" disabled>
        <i class="fas fa-trash me-1"></i>Ya, Hapus
      </button>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="toast-copied" id="toastCopied">✓ Berhasil disalin!</div>

<script>
const DUK_ID = <?= (int)$id ?>;

// Modal
function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', e => { if (e.target === el) el.classList.remove('show'); });
});

// Share
function openShare() { openModal('modalShare'); }
function buildShareText() { return document.getElementById('sharePreviewBox').innerText; }
function copyShareText() {
  navigator.clipboard.writeText(buildShareText()).then(() => {
    const t = document.getElementById('toastCopied');
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
  });
}
function shareWhatsApp() {
  const text = encodeURIComponent('*Profil Pegawai*\n\n' + buildShareText());
  window.open('https://wa.me/?text=' + text, '_blank');
}

// Delete
function openDelete() {
  document.getElementById('deleteConfirmInput').value = '';
  document.getElementById('btnConfirmDelete').disabled = true;
  openModal('modalDelete');
}
document.getElementById('deleteConfirmInput').addEventListener('input', function() {
  document.getElementById('btnConfirmDelete').disabled = (this.value !== 'HAPUS');
});
function doDelete() {
  window.location.href = 'proses_hapus_duk.php?id=' + DUK_ID;
}

// Tooltips
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
});
</script>

<?php require_once 'includes/footer.php'; ?>