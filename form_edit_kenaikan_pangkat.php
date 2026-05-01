<?php
session_start();
require_once 'check_session.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Hanya superadmin dan admin yang bisa akses
if (!isAdmin()) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}

$error = $_GET['error'] ?? '';
$id = $_GET['id'] ?? 0;

if (!$id) {
  header('Location: kenaikan_pangkat.php?error=ID tidak ditemukan');
  exit;
}

// ✅ Query dengan tempat_lahir dan tanggal_lahir
$query = "SELECT * FROM kenaikan_pangkat WHERE id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  header('Location: kenaikan_pangkat.php?error=Data tidak ditemukan');
  exit;
}

$data = $result->fetch_assoc();

// ✅ TAMBAHKAN Helper untuk format tanggal
require_once 'includes/date_helper.php';

// ✅ Format TTL untuk preview (opsional)
$ttl_preview = '';
if (!empty($data['tempat_lahir']) && !empty($data['tanggal_lahir'])) {
    $ttl_preview = formatTTL($data['tempat_lahir'], $data['tanggal_lahir']);
}
?>

<link rel="stylesheet" href="css/form_tambah_duk.css">

<main class="main-content">
  <div class="container-fluid">
    <div class="page-header fade-in">
      <h2><i class="fas fa-edit me-3"></i>Edit Usulan Kenaikan Pangkat</h2>
      <p class="mb-0">Formulir Edit Usulan <?= htmlspecialchars($data['nomor_usulan']) ?></p>
    </div>

    <div class="row justify-content-center">
      <div class="col-12 col-xl-10">

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger fade-in">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <div class="form-card fade-in">
          <div class="card-body">
            <form action="proses_edit_kenaikan_pangkat.php" method="POST">
              <input type="hidden" name="id" value="<?= $data['id'] ?>">
              
              <!-- Informasi Usulan -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-file-alt me-2"></i>Informasi Usulan
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-hashtag"></i> Nomor Usulan <span class="required">*</span>
                      </label>
                      <input type="text" 
                             name="nomor_usulan" 
                             class="form-control" 
                             value="<?= htmlspecialchars($data['nomor_usulan']) ?>"
                             placeholder="Masukkan nomor usulan"
                             required>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-calendar"></i> Tanggal Usulan <span class="required">*</span>
                      </label>
                      <input type="date" name="tanggal_usulan" class="form-control" 
                             value="<?= $data['tanggal_usulan'] ?>" required>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-list"></i> Jenis Kenaikan <span class="required">*</span>
                      </label>
                      <select name="jenis_kenaikan" class="form-select" required>
                        <option value="Pilihan" <?= $data['jenis_kenaikan'] == 'Pilihan' ? 'selected' : '' ?>>1. PILIHAN</option>
                        <option value="Reguler" <?= $data['jenis_kenaikan'] == 'Reguler' ? 'selected' : '' ?>>2. REGULER</option>
                        <option value="Anumerta" <?= $data['jenis_kenaikan'] == 'Anumerta' ? 'selected' : '' ?>>3. ANUMERTA</option>
                        <option value="Pengabdian" <?= $data['jenis_kenaikan'] == 'Pengabdian' ? 'selected' : '' ?>>4. PENGABDIAN</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Data Pegawai -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-user me-2"></i>Data Pegawai
                </div>
                
                <!-- ✅ PREVIEW TTL (Read-only) -->
                <?php if (!empty($ttl_preview)): ?>
                <div class="alert alert-info mb-3">
                  <i class="fas fa-info-circle me-2"></i>
                  <strong>TTL:</strong> <?= $ttl_preview ?>
                </div>
                <?php endif; ?>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-id-card"></i> NIP
                      </label>
                      <input type="text" name="nip" class="form-control" 
                             value="<?= htmlspecialchars($data['nip']) ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-user"></i> Nama <span class="required">*</span>
                      </label>
                      <input type="text" name="nama" class="form-control" 
                             value="<?= htmlspecialchars($data['nama']) ?>" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-id-badge"></i> Kartu Pegawai <span class="required">*</span>
                      </label>
                      <input type="text" name="kartu_pegawai" class="form-control" 
                             value="<?= htmlspecialchars($data['kartu_pegawai']) ?>" required>
                    </div>
                  </div>
                  
                  <!-- ✅ FIELD TERPISAH: Tempat Lahir & Tanggal Lahir -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-map-marker-alt"></i> Tempat Lahir <span class="required">*</span>
                      </label>
                      <input type="text" name="tempat_lahir" class="form-control" 
                             value="<?= htmlspecialchars($data['tempat_lahir'] ?? '') ?>" 
                             placeholder="Contoh: Banjarmasin"
                             required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-calendar-alt"></i> Tanggal Lahir <span class="required">*</span>
                      </label>
                      <input type="date" name="tanggal_lahir" class="form-control" 
                             value="<?= htmlspecialchars($data['tanggal_lahir'] ?? '') ?>" 
                             required>
                      <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Format: YYYY-MM-DD
                      </small>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-graduation-cap"></i> Pendidikan <span class="required">*</span>
                      </label>
                      <input type="text" name="pendidikan_terakhir" class="form-control" 
                             value="<?= htmlspecialchars($data['pendidikan_terakhir']) ?>" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-book"></i> Program Studi <span class="required">*</span>
                      </label>
                      <input type="text" name="prodi" class="form-control" 
                             value="<?= htmlspecialchars($data['prodi']) ?>" required>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Data LAMA & BARU -->
              <div class="row">
                <div class="col-md-6">
                  <div class="form-section">
                    <div class="section-title bg-danger text-white">
                      <i class="fas fa-arrow-down me-2"></i>Pangkat/Golongan LAMA
                    </div>
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-medal"></i> Pangkat <span class="required">*</span>
                      </label>
                      <input type="text" name="pangkat_lama" class="form-control" 
                             value="<?= htmlspecialchars($data['pangkat_lama']) ?>" required>
                    </div>
                    <div class="row">
                      <div class="col-6">
                        <div class="form-group">
                          <label class="form-label">
                            <i class="fas fa-layer-group"></i> Golongan <span class="required">*</span>
                          </label>
                          <input type="text" name="golongan_lama" class="form-control" 
                                 value="<?= htmlspecialchars($data['golongan_lama']) ?>" required>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="form-group">
                          <label class="form-label">
                            <i class="fas fa-calendar-check"></i> TMT <span class="required">*</span>
                          </label>
                          <input type="date" name="tmt_pangkat_lama" class="form-control" 
                                 value="<?= $data['tmt_pangkat_lama'] ?>" required>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-6">
                        <div class="form-group">
                          <label class="form-label">
                            <i class="fas fa-clock"></i> MK (Tahun) <span class="required">*</span>
                          </label>
                          <input type="number" name="masa_kerja_tahun_lama" class="form-control" 
                                 value="<?= $data['masa_kerja_tahun_lama'] ?>" required min="0">
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="form-group">
                          <label class="form-label">
                            <i class="fas fa-clock"></i> MK (Bulan) <span class="required">*</span>
                          </label>
                          <input type="number" name="masa_kerja_bulan_lama" class="form-control" 
                                 value="<?= $data['masa_kerja_bulan_lama'] ?>" required min="0" max="11">
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-money-bill-wave"></i> Gaji Pokok <span class="required">*</span>
                      </label>
                      <input type="text" name="gaji_pokok_lama" class="form-control money" 
                             value="<?= number_format($data['gaji_pokok_lama'], 0, ',', '.') ?>" required>
                    </div>
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-briefcase"></i> Jabatan <span class="required">*</span>
                      </label>
                      <input type="text" name="jabatan_lama" class="form-control" 
                             value="<?= htmlspecialchars($data['jabatan_lama']) ?>" required>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-section">
                    <div class="section-title bg-success text-white">
                      <i class="fas fa-arrow-up me-2"></i>Pangkat/Golongan BARU (Usulan)
                    </div>
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-medal"></i> Pangkat <span class="required">*</span>
                      </label>
                      <input type="text" name="pangkat_baru" class="form-control" 
                             value="<?= htmlspecialchars($data['pangkat_baru']) ?>" required>
                    </div>
                    <div class="row">
                      <div class="col-6">
                        <div class="form-group">
                          <label class="form-label">
                            <i class="fas fa-layer-group"></i> Golongan <span class="required">*</span>
                          </label>
                          <input type="text" name="golongan_baru" class="form-control" 
                                 value="<?= htmlspecialchars($data['golongan_baru']) ?>" required>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="form-group">
                          <label class="form-label">
                            <i class="fas fa-calendar-check"></i> TMT <span class="required">*</span>
                          </label>
                          <input type="date" name="tmt_pangkat_baru" class="form-control" 
                                 value="<?= $data['tmt_pangkat_baru'] ?>" required>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-6">
                        <div class="form-group">
                          <label class="form-label">
                            <i class="fas fa-clock"></i> MK (Tahun) <span class="required">*</span>
                          </label>
                          <input type="number" name="masa_kerja_tahun_baru" class="form-control" 
                                 value="<?= $data['masa_kerja_tahun_baru'] ?>" required min="0">
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="form-group">
                          <label class="form-label">
                            <i class="fas fa-clock"></i> MK (Bulan) <span class="required">*</span>
                          </label>
                          <input type="number" name="masa_kerja_bulan_baru" class="form-control" 
                                 value="<?= $data['masa_kerja_bulan_baru'] ?>" required min="0" max="11">
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-money-bill-wave"></i> Gaji Pokok <span class="required">*</span>
                      </label>
                      <input type="text" name="gaji_pokok_baru" class="form-control money" 
                             value="<?= number_format($data['gaji_pokok_baru'], 0, ',', '.') ?>" required>
                    </div>
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-briefcase"></i> Jabatan Baru <span class="required">*</span>
                      </label>
                      <input type="text" name="jabatan_baru" class="form-control" 
                             value="<?= htmlspecialchars($data['jabatan_baru']) ?>" required>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Perhitungan Masa Kerja -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-calculator me-2"></i>Perhitungan Masa Kerja
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-clock"></i> MK Dalam Gol/Ruang (Tahun) <span class="required">*</span>
                      </label>
                      <input type="number" name="mk_golongan_tahun" class="form-control" 
                             value="<?= $data['mk_golongan_tahun'] ?>" required min="0">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-clock"></i> MK Dalam Gol/Ruang (Bulan) <span class="required">*</span>
                      </label>
                      <input type="number" name="mk_golongan_bulan" class="form-control" 
                             value="<?= $data['mk_golongan_bulan'] ?>" required min="0" max="11">
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-calendar-alt"></i> Dari s/d
                      </label>
                      <input type="text" name="mk_dari_sampai" class="form-control" 
                             value="<?= htmlspecialchars($data['mk_dari_sampai']) ?>"
                             placeholder="09-09-2021 s/d 01-08-2025">
                    </div>
                  </div>
                </div>
              </div>

              <!-- Atasan Langsung -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-user-tie me-2"></i>Atasan Langsung
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-user"></i> Nama Atasan
                      </label>
                      <input type="text" name="atasan_nama" class="form-control" 
                             value="<?= htmlspecialchars($data['atasan_nama']) ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-id-card"></i> NIP Atasan
                      </label>
                      <input type="text" name="atasan_nip" class="form-control" 
                             value="<?= htmlspecialchars($data['atasan_nip']) ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-medal"></i> Pangkat Atasan
                      </label>
                      <input type="text" name="atasan_pangkat" class="form-control" 
                             value="<?= htmlspecialchars($data['atasan_pangkat']) ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-briefcase"></i> Jabatan Atasan
                      </label>
                      <input type="text" name="atasan_jabatan" class="form-control" 
                             value="<?= htmlspecialchars($data['atasan_jabatan']) ?>" readonly>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Wilayah & SKP -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-map-marked-alt me-2"></i>Wilayah Pembayaran & SKP
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-map-pin"></i> Wilayah Pembayaran
                      </label>
                      <input type="text" name="wilayah_pembayaran" class="form-control" 
                             value="<?= htmlspecialchars($data['wilayah_pembayaran']) ?>">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-calendar"></i> SKP Tahun 1
                      </label>
                      <input type="text" name="skp_tahun_1" class="form-control" 
                             value="<?= htmlspecialchars($data['skp_tahun_1']) ?>">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-star"></i> Nilai SKP 1
                      </label>
                      <input type="text" name="skp_nilai_1" class="form-control" 
                             value="<?= htmlspecialchars($data['skp_nilai_1']) ?>">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-calendar"></i> SKP Tahun 2
                      </label>
                      <input type="text" name="skp_tahun_2" class="form-control" 
                             value="<?= htmlspecialchars($data['skp_tahun_2']) ?>">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-star"></i> Nilai SKP 2
                      </label>
                      <input type="text" name="skp_nilai_2" class="form-control" 
                             value="<?= htmlspecialchars($data['skp_nilai_2']) ?>">
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-info-circle"></i> Status <span class="required">*</span>
                      </label>
                      <select name="status" class="form-select" required>
                        <option value="draft" <?= $data['status'] == 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="diajukan" <?= $data['status'] == 'diajukan' ? 'selected' : '' ?>>Diajukan</option>
                        <option value="disetujui" <?= $data['status'] == 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                        <option value="ditolak" <?= $data['status'] == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="btn-group-custom">
                <a href="kenaikan_pangkat.php" class="btn btn-secondary">
                  <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save me-2"></i>Simpan Perubahan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Script untuk Format Gaji -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format input gaji pokok
    const gajiInputs = document.querySelectorAll('.money');
    
    gajiInputs.forEach(input => {
        // Format saat halaman load
        formatMoney(input);
        
        // Format saat user mengetik
        input.addEventListener('input', function(e) {
            formatMoney(e.target);
        });
    });
    
    function formatMoney(input) {
        let value = input.value.replace(/\D/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('id-ID');
        }
        input.value = value;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>