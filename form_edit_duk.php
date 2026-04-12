<?php
// form_edit_duk.php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Hanya superadmin dan admin yang bisa akses
if (!isAdmin()) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dataduk.php');
    exit;
}

$id = (int) $_GET['id'];

// Ambil data existing DUK
$stmt = $koneksi->prepare('SELECT * FROM duk WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    header('Location: dataduk.php?error=Data tidak ditemukan');
    exit;
}

$data = $res->fetch_assoc();

// ✅ SESUDAH (benar - reset pointer)
// Ambil daftar Kepala OPD yang aktif
$sql_opd = "SELECT id, nama, nip, pangkat, golongan, jabatan, gelar_depan, gelar_belakang 
            FROM kepala_opd 
            WHERE status = 'aktif' 
            ORDER BY nama ASC";
$kepala_opd_list = $koneksi->query($sql_opd);

function val($field, $data, $default = '') {
    return htmlspecialchars($data[$field] ?? $default);
}
?>

<link rel="stylesheet" href="css/form_edit_duk.css">

<main class="main-content">
  <div class="container-fluid">
    
    <!-- Progress Bar -->
    <div class="form-progress">
      <div class="form-progress-bar" id="progressBar"></div>
    </div>
    
    <!-- Page Header -->
    <div class="page-header fade-in">
      <h2><i class="fas fa-edit me-3"></i>Edit Data DUK</h2>
      <p class="page-subtitle">Formulir Edit Data Urut Kepangkatan - ID: <?= $id ?></p>
    </div>

    <div class="row justify-content-center">
      <div class="col-12 col-xl-10">

        <!-- Change Summary -->
        <div class="change-summary" id="changeSummary">
          <h6><i class="fas fa-info-circle me-2"></i>Perubahan Terdeteksi</h6>
          <p>Field yang telah diubah:</p>
          <ul class="change-list" id="changeList"></ul>
        </div>

        <div class="form-card fade-in">
          <div class="card-header">
            <i class="fas fa-database me-2"></i>
            Edit Data Pegawai
          </div>
          <div class="card-body">
            <form id="editForm" method="post" action="proses_edit_duk.php" novalidate>
              <!-- Hidden ID Field -->
              <input type="hidden" name="id" value="<?= $id ?>">
              
              <!-- Personal Information Section -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-user me-2"></i>
                  Informasi Personal
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group fade-in">
                      <label for="nama" class="form-label">
                        <i class="fas fa-user"></i>
                        Nama Lengkap <span class="required">*</span>
                      </label>
                      <div class="input-group">
                        <input type="text" name="nama" id="nama" class="form-control with-icon" 
                               value="<?= val('nama', $data) ?>" required
                               data-original="<?= htmlspecialchars($data['nama'] ?? '') ?>">
                        <i class="input-icon fas fa-user"></i>
                        <span class="change-indicator"><i class="fas fa-edit"></i></span>
                      </div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="nip" class="form-label">
                        <i class="fas fa-id-card"></i>
                        NIP
                      </label>
                      <div class="input-group">
                        <input type="text" name="nip" id="nip" class="form-control with-icon" 
                               value="<?= val('nip', $data) ?>"
                               data-original="<?= htmlspecialchars($data['nip'] ?? '') ?>">
                        <i class="input-icon fas fa-id-card"></i>
                        <span class="change-indicator"><i class="fas fa-edit"></i></span>
                      </div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="kartu_pegawai" class="form-label">
                        <i class="fas fa-id-card"></i>
                        Kartu Pegawai
                      </label>
                      <div class="input-group">
                        <input type="text" name="kartu_pegawai" id="kartu_pegawai" class="form-control with-icon" 
                               value="<?= val('kartu_pegawai', $data) ?>"
                               data-original="<?= htmlspecialchars($data['kartu_pegawai'] ?? '') ?>">
                        <i class="input-icon fas fa-id-card"></i>
                        <span class="change-indicator"><i class="fas fa-edit"></i></span>
                      </div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="ttl" class="form-label">
                        <i class="fas fa-birthday-cake"></i>
                        Tempat, Tanggal Lahir
                      </label>
                      <div class="input-group">
                        <input type="text" name="ttl" id="ttl" class="form-control with-icon" 
                               value="<?= val('ttl', $data) ?>" placeholder="Banjarmasin, 01-01-1990"
                               data-original="<?= htmlspecialchars($data['ttl'] ?? '') ?>">
                        <i class="input-icon fas fa-birthday-cake"></i>
                        <span class="change-indicator"><i class="fas fa-edit"></i></span>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group fade-in">
                      <label for="jenis_kelamin" class="form-label">
                        <i class="fas fa-venus-mars"></i>
                        Jenis Kelamin
                      </label>
                      <div class="input-group">
                        <select name="jenis_kelamin" id="jenis_kelamin" class="form-select"
                                data-original="<?= htmlspecialchars($data['jenis_kelamin'] ?? '') ?>">
                          <?php $jk = val('jenis_kelamin', $data); ?>
                          <option value="">-- Pilih Jenis Kelamin --</option>
                          <option value="Laki-laki" <?= $jk === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                          <option value="Perempuan" <?= $jk === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                        <span class="change-indicator"><i class="fas fa-edit"></i></span>
                      </div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="pendidikan_terakhir" class="form-label">
                        <i class="fas fa-graduation-cap"></i>
                        Pendidikan Terakhir
                      </label>
                      <div class="input-group">
                        <select name="pendidikan_terakhir" id="pendidikan_terakhir" class="form-select"
                                data-original="<?= htmlspecialchars($data['pendidikan_terakhir'] ?? '') ?>">
                          <?php $pend = val('pendidikan_terakhir', $data); ?>
                          <option value="">-- Pilih Pendidikan --</option>
                          <option value="SD" <?= $pend === 'SD' ? 'selected' : '' ?>>SD</option>
                          <option value="SMP" <?= $pend === 'SMP' ? 'selected' : '' ?>>SMP</option>
                          <option value="SMA" <?= $pend === 'SMA' ? 'selected' : '' ?>>SMA</option>
                          <option value="D1" <?= $pend === 'D1' ? 'selected' : '' ?>>D1</option>
                          <option value="D2" <?= $pend === 'D2' ? 'selected' : '' ?>>D2</option>
                          <option value="D3" <?= $pend === 'D3' ? 'selected' : '' ?>>D3</option>
                          <option value="D4" <?= $pend === 'D4' ? 'selected' : '' ?>>D4</option>
                          <option value="S1" <?= $pend === 'S1' ? 'selected' : '' ?>>S1</option>
                          <option value="S2" <?= $pend === 'S2' ? 'selected' : '' ?>>S2</option>
                          <option value="S3" <?= $pend === 'S3' ? 'selected' : '' ?>>S3</option>
                        </select>
                        <span class="change-indicator"><i class="fas fa-edit"></i></span>
                      </div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="prodi" class="form-label">
                        <i class="fas fa-book"></i>
                        Program Studi
                      </label>
                      <div class="input-group">
                        <input type="text" name="prodi" id="prodi" class="form-control with-icon" 
                               value="<?= val('prodi', $data) ?>"
                               data-original="<?= htmlspecialchars($data['prodi'] ?? '') ?>">
                        <i class="input-icon fas fa-book"></i>
                        <span class="change-indicator"><i class="fas fa-edit"></i></span>
                      </div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="nomor_wa" class="form-label">
                        <i class="fab fa-whatsapp text-success"></i>
                        Nomor WhatsApp <span class="required">*</span>
                      </label>
                      <div class="input-group">
                        <input type="text" name="nomor_wa" id="nomor_wa" class="form-control with-icon" 
                               value="<?= val('nomor_wa', $data) ?>"
                               required pattern="[0-9]{10,15}" maxlength="15"
                               placeholder="081234567890"
                               data-original="<?= htmlspecialchars($data['nomor_wa'] ?? '') ?>">
                        <i class="input-icon fab fa-whatsapp"></i>
                        <span class="change-indicator"><i class="fas fa-edit"></i></span>
                      </div>
                      <small class="form-text text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Format: 08xxx atau 628xxx (10-15 digit)
                      </small>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Career Information Section -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-briefcase me-2"></i>
                  Informasi Kepegawaian
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group fade-in">
                      <label for="pangkat_terakhir" class="form-label">
                        <i class="fas fa-medal"></i>
                        Pangkat Terakhir
                      </label>
                      <div class="input-group">
                        <input type="text" name="pangkat_terakhir" id="pangkat_terakhir" class="form-control with-icon" 
                               value="<?= val('pangkat_terakhir', $data) ?>"
                               data-original="<?= htmlspecialchars($data['pangkat_terakhir'] ?? '') ?>">
                        <i class="input-icon fas fa-medal"></i>
                        <span class="change-indicator"><i class="fas fa-edit"></i></span>
                      </div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="golongan" class="form-label">
                        <i class="fas fa-layer-group"></i>
                        Golongan <span class="required">*</span>
                      </label>
                      <div class="input-group">
                        <select name="golongan" id="golongan" class="form-select"
                                data-original="<?= htmlspecialchars($data['golongan'] ?? '') ?>"
                                required>
                          <?php $gol = val('golongan', $data); ?>
                          <option value="">-- Pilih Golongan --</option>
                          <option value="I/a" <?= $gol === 'I/a' ? 'selected' : '' ?>>I/a</option>
                          <option value="I/b" <?= $gol === 'I/b' ? 'selected' : '' ?>>I/b</option>
                          <option value="I/c" <?= $gol === 'I/c' ? 'selected' : '' ?>>I/c</option>
                          <option value="I/d" <?= $gol === 'I/d' ? 'selected' : '' ?>>I/d</option>
                          <option value="II/a" <?= $gol === 'II/a' ? 'selected' : '' ?>>II/a</option>
                          <option value="II/b" <?= $gol === 'II/b' ? 'selected' : '' ?>>II/b</option>
                          <option value="II/c" <?= $gol === 'II/c' ? 'selected' : '' ?>>II/c</option>
                          <option value="II/d" <?= $gol === 'II/d' ? 'selected' : '' ?>>II/d</option>
                          <option value="III/a" <?= $gol === 'III/a' ? 'selected' : '' ?>>III/a</option>
                          <option value="III/b" <?= $gol === 'III/b' ? 'selected' : '' ?>>III/b</option>
                          <option value="III/c" <?= $gol === 'III/c' ? 'selected' : '' ?>>III/c</option>
                          <option value="III/d" <?= $gol === 'III/d' ? 'selected' : '' ?>>III/d</option>
                          <option value="IV/a" <?= $gol === 'IV/a' ? 'selected' : '' ?>>IV/a</option>
                          <option value="IV/b" <?= $gol === 'IV/b' ? 'selected' : '' ?>>IV/b</option>
                          <option value="IV/c" <?= $gol === 'IV/c' ? 'selected' : '' ?>>IV/c</option>
                          <option value="IV/d" <?= $gol === 'IV/d' ? 'selected' : '' ?>>IV/d</option>
                          <option value="IV/e" <?= $gol === 'IV/e' ? 'selected' : '' ?>>IV/e</option>
                        </select>
                        <span class="change-indicator"><i class="fas fa-edit"></i></span>
                      </div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="tmt_pangkat" class="form-label">
                        <i class="fas fa-calendar-alt"></i>
                        TMT Pangkat
                      </label>
                      <div class="input-group">
                        <input type="date" name="tmt_pangkat" id="tmt_pangkat" class="form-control" 
                               value="<?= val('tmt_pangkat', $data) ?>"
                               data-original="<?= htmlspecialchars($data['tmt_pangkat'] ?? '') ?>">
                        <span class="change-indicator"><i class="fas fa-edit"></i></span>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group fade-in">
                      <label for="jabatan_terakhir" class="form-label">
                        <i class="fas fa-briefcase"></i>
                        Jabatan Terakhir <span class="required">*</span>
                      </label>
                      <div class="input-group">
                        <input type="text" name="jabatan_terakhir" id="jabatan_terakhir" class="form-control with-icon" 
                               value="<?= val('jabatan_terakhir', $data) ?>" required
                               data-original="<?= htmlspecialchars($data['jabatan_terakhir'] ?? '') ?>">
                        <i class="input-icon fas fa-briefcase"></i>
                        <span class="change-indicator"><i class="fas fa-edit"></i></span>
                      </div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="eselon" class="form-label">
                        <i class="fas fa-sitemap"></i>
                        Eselon <span class="required">*</span>
                      </label>
                      <div class="input-group">
                        <select name="eselon" id="eselon" class="form-select" required
                                data-original="<?= htmlspecialchars($data['eselon'] ?? '') ?>">
                          <?php $esel = val('eselon', $data); ?>
                          <option value="">-- Pilih Eselon --</option>
                          <option value="Non-Eselon" <?= $esel === 'Non-Eselon' ? 'selected' : '' ?>>Non-Eselon</option>
                          <option value="IV.b" <?= $esel === 'IV.b' ? 'selected' : '' ?>>IV.b</option>
                          <option value="IV.a" <?= $esel === 'IV.a' ? 'selected' : '' ?>>IV.a</option>
                          <option value="III.b" <?= $esel === 'III.b' ? 'selected' : '' ?>>III.b</option>
                          <option value="III.a" <?= $esel === 'III.a' ? 'selected' : '' ?>>III.a</option>
                          <option value="II.b" <?= $esel === 'II.b' ? 'selected' : '' ?>>II.b</option>
                          <option value="II.a" <?= $esel === 'II.a' ? 'selected' : '' ?>>II.a</option>
                          <option value="I.b" <?= $esel === 'I.b' ? 'selected' : '' ?>>I.b</option>
                          <option value="I.a" <?= $esel === 'I.a' ? 'selected' : '' ?>>I.a</option>
                        </select>
                        <span class="change-indicator"><i class="fas fa-edit"></i></span>
                      </div>
                    </div>

                    <!-- Sub Dropdown untuk Non-Eselon: JFT/JFU -->
                    <div class="form-group fade-in" id="jenis_jabatan_group" style="display: none;">
                      <label for="jenis_jabatan" class="form-label">
                        <i class="fas fa-briefcase"></i>
                        Jenis Jabatan
                      </label>
                      <div class="input-group">
                        <select name="jenis_jabatan" id="jenis_jabatan" class="form-select"
                                data-original="<?= htmlspecialchars($data['jenis_jabatan'] ?? '') ?>">
                          <?php $jenis_jab = val('jenis_jabatan', $data); ?>
                          <option value="">-- Pilih Jenis Jabatan --</option>
                          <option value="JFT" <?= $jenis_jab === 'JFT' ? 'selected' : '' ?>>JFT (Jabatan Fungsional Tertentu)</option>
                          <option value="JFU" <?= $jenis_jab === 'JFU' ? 'selected' : '' ?>>JFU (Jabatan Fungsional Umum)</option>
                        </select>
                        <span class="change-indicator"><i class="fas fa-edit"></i></span>
                      </div>
                    </div>

                    <!-- Sub Dropdown untuk JFT -->
                    <div class="form-group fade-in" id="jft_tingkat_group" style="display: none;">
                      <label for="jft_tingkat" class="form-label">
                        <i class="fas fa-star"></i>
                        Tingkat JFT
                      </label>
                      <div class="input-group">
                        <select name="jft_tingkat" id="jft_tingkat" class="form-select"
                                data-original="<?= htmlspecialchars($data['jft_tingkat'] ?? '') ?>">
                          <?php $jft = val('jft_tingkat', $data); ?>
                          <option value="">-- Pilih Tingkat JFT --</option>
                          <option value="Ahli Pratama" <?= $jft === 'Ahli Pratama' ? 'selected' : '' ?>>Ahli Pratama</option>
                          <option value="Ahli Muda" <?= $jft === 'Ahli Muda' ? 'selected' : '' ?>>Ahli Muda</option>
                          <option value="Ahli Madya" <?= $jft === 'Ahli Madya' ? 'selected' : '' ?>>Ahli Madya</option>
                          <option value="Pemula" <?= $jft === 'Pemula' ? 'selected' : '' ?>>Pemula</option>
                          <option value="Terampil" <?= $jft === 'Terampil' ? 'selected' : '' ?>>Terampil</option>
                          <option value="Mahir" <?= $jft === 'Mahir' ? 'selected' : '' ?>>Mahir</option>
                          <option value="Penyelia" <?= $jft === 'Penyelia' ? 'selected' : '' ?>>Penyelia</option>
                        </select>
                        <span class="change-indicator"><i class="fas fa-edit"></i></span>
                      </div>
                    </div>

                    <!-- Sub Dropdown untuk JFU -->
                    <div class="form-group fade-in" id="jfu_kelas_group" style="display: none;">
                      <label for="jfu_kelas" class="form-label">
                        <i class="fas fa-list-ol"></i>
                        Kelas JFU
                      </label>
                      <div class="input-group">
                        <select name="jfu_kelas" id="jfu_kelas" class="form-select"
                                data-original="<?= htmlspecialchars($data['jfu_kelas'] ?? '') ?>">
                          <?php $jfu = val('jfu_kelas', $data); ?>
                          <option value="">-- Pilih Kelas JFU --</option>
                          <option value="5" <?= $jfu === '5' ? 'selected' : '' ?>>Kelas 5</option>
                          <option value="6" <?= $jfu === '6' ? 'selected' : '' ?>>Kelas 6</option>
                          <option value="7" <?= $jfu === '7' ? 'selected' : '' ?>>Kelas 7</option>
                        </select>
                        <span class="change-indicator"><i class="fas fa-edit"></i></span>
                      </div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="tmt_eselon" class="form-label">
                        <i class="fas fa-calendar-check"></i>
                        TMT Eselon
                      </label>
                      <div class="input-group">
                        <input type="date" name="tmt_eselon" id="tmt_eselon" class="form-control" 
                               value="<?= val('tmt_eselon', $data) ?>"
                               data-original="<?= htmlspecialchars($data['tmt_eselon'] ?? '') ?>">
                        <span class="change-indicator"><i class="fas fa-edit"></i></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

           <!-- Kepala OPD Section (Atasan) -->
<div class="form-section">
  <div class="section-title">
    <i class="fas fa-user-tie me-2"></i>
    Data Atasan / Kepala OPD
    <small class="text-muted ms-2">(Untuk keperluan kenaikan pangkat)</small>
  </div>
  
  <div class="alert alert-info mb-3">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Info:</strong> Pilih Kepala OPD yang akan menjadi atasan langsung pegawai ini
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="form-group fade-in">
        <label for="id_opd" class="form-label">
          <i class="fas fa-user-tie"></i>
          Pilih Kepala OPD
          <span class="tooltip-custom">
            <i class="fas fa-question-circle ms-1"></i>
            <span class="tooltip-text">Data ini akan otomatis digunakan saat membuat usulan kenaikan pangkat</span>
          </span>
        </label>
        <div class="input-group">
          <select name="id_opd" id="id_opd" class="form-select"
                  data-original="<?= htmlspecialchars($data['id_opd'] ?? '') ?>">
            <option value="">-- Pilih Kepala OPD --</option>
            <?php 
            // Ambil ulang data kepala OPD yang aktif
            $sql_opd_fresh = "SELECT id, nama, nip, pangkat, golongan, jabatan, gelar_depan, gelar_belakang 
                              FROM kepala_opd 
                              WHERE status = 'aktif' 
                              ORDER BY nama ASC";
            $kepala_opd_result = $koneksi->query($sql_opd_fresh);
            
            $current_id_opd = !empty($data['id_opd']) ? (int)$data['id_opd'] : 0;
            
            while ($kepala = $kepala_opd_result->fetch_assoc()): 
              $nama_lengkap = '';
              if (!empty($kepala['gelar_depan'])) {
                $nama_lengkap .= $kepala['gelar_depan'] . ' ';
              }
              $nama_lengkap .= $kepala['nama'];
              if (!empty($kepala['gelar_belakang'])) {
                $nama_lengkap .= ', ' . $kepala['gelar_belakang'];
              }
              $info_kepala = $nama_lengkap . ' - ' . $kepala['pangkat'] . ' (' . $kepala['golongan'] . ')';
              $is_selected = ($current_id_opd == $kepala['id']) ? 'selected' : '';
            ?>
            <option value="<?= $kepala['id'] ?>" <?= $is_selected ?>
                    data-nama="<?= htmlspecialchars($nama_lengkap) ?>"
                    data-nip="<?= htmlspecialchars($kepala['nip']) ?>"
                    data-pangkat="<?= htmlspecialchars($kepala['pangkat']) ?>"
                    data-golongan="<?= htmlspecialchars($kepala['golongan']) ?>"
                    data-jabatan="<?= htmlspecialchars($kepala['jabatan']) ?>">
              <?= htmlspecialchars($info_kepala) ?>
            </option>
            <?php endwhile; ?>
          </select>
          <span class="change-indicator"><i class="fas fa-edit"></i></span>
        </div>
        
        <?php if (empty($data['id_opd'])): ?>
        <div class="alert alert-warning mt-2 mb-0">
          <i class="fas fa-exclamation-triangle me-2"></i>
          <strong>Perhatian:</strong> Data Kepala OPD belum dipilih. Silakan pilih untuk melengkapi data.
        </div>
        <?php endif; ?>
        
        <small class="form-text text-muted">
          <i class="fas fa-info-circle me-1"></i>
          Pilih Kepala OPD yang aktif sebagai atasan pegawai ini
        </small>
      </div>
    </div>
  </div>

  <!-- Preview Data Kepala OPD yang Dipilih -->
  <div class="row" id="kepala-opd-preview" style="display: <?= !empty($data['id_opd']) ? 'block' : 'none' ?>;">
    <div class="col-md-12">
      <div class="alert alert-success">
        <h6 class="mb-2"><i class="fas fa-check-circle me-2"></i>Data Kepala OPD Terpilih:</h6>
        <table class="table table-sm table-borderless mb-0">
          <tr>
            <td width="120"><strong>Nama</strong></td>
            <td>: <span id="preview-nama">
              <?php 
              if (!empty($data['id_opd'])) {
                // Ambil data kepala OPD yang sedang dipilih
                $stmt_opd = $koneksi->prepare("SELECT nama, nip, pangkat, golongan, jabatan, gelar_depan, gelar_belakang 
                                                FROM kepala_opd WHERE id = ? AND status = 'aktif'");
                $stmt_opd->bind_param("i", $data['id_opd']);
                $stmt_opd->execute();
                $result_opd = $stmt_opd->get_result();
                if ($result_opd->num_rows > 0) {
                  $opd_data = $result_opd->fetch_assoc();
                  $nama_opd = '';
                  if (!empty($opd_data['gelar_depan'])) $nama_opd .= $opd_data['gelar_depan'] . ' ';
                  $nama_opd .= $opd_data['nama'];
                  if (!empty($opd_data['gelar_belakang'])) $nama_opd .= ', ' . $opd_data['gelar_belakang'];
                  echo htmlspecialchars($nama_opd);
                } else {
                  echo '-';
                }
                $stmt_opd->close();
              } else {
                echo '-';
              }
              ?>
            </span></td>
          </tr>
          <tr>
            <td><strong>NIP</strong></td>
            <td>: <span id="preview-nip">
              <?= isset($opd_data['nip']) ? htmlspecialchars($opd_data['nip']) : '-' ?>
            </span></td>
          </tr>
          <tr>
            <td><strong>Pangkat</strong></td>
            <td>: <span id="preview-pangkat">
              <?= isset($opd_data['pangkat']) ? htmlspecialchars($opd_data['pangkat'] . ' (' . $opd_data['golongan'] . ')') : '-' ?>
            </span></td>
          </tr>
          <tr>
            <td><strong>Jabatan</strong></td>
            <td>: <span id="preview-jabatan">
              <?= isset($opd_data['jabatan']) ? htmlspecialchars($opd_data['jabatan']) : '-' ?>
            </span></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>

              <!-- Action Buttons -->
              <div class="btn-group-custom">
                <a href="dataduk.php" class="btn btn-secondary">
                  <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
                <button type="button" class="btn btn-warning" id="resetBtn">
                  <i class="fas fa-undo me-2"></i>Reset Form
                </button>
                <a href="detail_duk.php?id=<?= $id ?>" class="btn btn-info">
                  <i class="fas fa-eye me-2"></i>Lihat Detail
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                  <i class="fas fa-save me-2"></i>Simpan Perubahan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
// Preview Kepala OPD yang dipilih
document.getElementById('id_opd').addEventListener('change', function() {
  const selectedOption = this.options[this.selectedIndex];
  const previewDiv = document.getElementById('kepala-opd-preview');
  
  if (this.value) {
    // Tampilkan preview
    previewDiv.style.display = 'block';
    
    // Update data preview
    document.getElementById('preview-nama').textContent = selectedOption.dataset.nama || '-';
    document.getElementById('preview-nip').textContent = selectedOption.dataset.nip || '-';
    document.getElementById('preview-pangkat').textContent = 
      (selectedOption.dataset.pangkat || '-') + ' (' + (selectedOption.dataset.golongan || '-') + ')';
    document.getElementById('preview-jabatan').textContent = selectedOption.dataset.jabatan || '-';
  } else {
    // Sembunyikan preview jika tidak ada yang dipilih
    previewDiv.style.display = 'none';
  }
});

// Trigger change event saat load untuk menampilkan preview jika sudah ada data
window.addEventListener('DOMContentLoaded', function() {
  const idOpdSelect = document.getElementById('id_opd');
  if (idOpdSelect && idOpdSelect.value) {
    idOpdSelect.dispatchEvent(new Event('change'));
  }
});
</script>

</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/form_edit_duk.js"></script>

<?php require_once 'includes/footer.php'; ?>