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

// Ambil ID dari URL
$id = $_GET['id'] ?? 0;

if (!$id || !is_numeric($id)) {
    header('Location: penyuluh.php?error=ID tidak valid');
    exit;
}

// Query untuk mengambil data penyuluh berdasarkan ID
$sql = "SELECT * FROM penyuluh WHERE id = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: penyuluh.php?error=Data tidak ditemukan');
    exit;
}

$penyuluh = $result->fetch_assoc();
$stmt->close();

// Ambil error dari query string kalau ada
$error = $_GET['error'] ?? '';
?>

<main class="main-content">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-lg-10 col-xl-8">

        <!-- Enhanced Header -->
        <div class="form-header">
          <div class="header-content">
            <div class="header-icon">
              <i class="fas fa-edit"></i>
            </div>
            <div class="header-text">
              <h2 class="form-title">Edit Data Penyuluh</h2>
              <p class="form-subtitle">Sistem Informasi Penyuluh DPPKBPM</p>
            </div>
          </div>
          <div class="progress-indicator">
            <div class="progress-bar">
              <div class="progress-fill" id="formProgress"></div>
            </div>
            <small class="progress-text">Update informasi penyuluh</small>
          </div>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger alert-dismissible fade show custom-alert" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <!-- Enhanced Form Card -->
        <div class="form-card">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-edit me-2"></i>
              Form Edit Data Penyuluh
            </h5>
            <div class="card-tools">
              <span class="badge bg-warning">Edit Mode</span>
            </div>
          </div>

          <div class="card-body">
            <form action="proses_edit_penyuluh.php" method="POST" id="penyuluhForm" autocomplete="off" novalidate>
              
              <!-- Hidden ID -->
              <input type="hidden" name="id" value="<?= $penyuluh['id'] ?>">
              
              <!-- Section 1: Data Pribadi -->
              <div class="form-section active">
                <div class="section-header">
                  <h6 class="section-title">
                    <i class="fas fa-user me-2"></i>
                    Data Pribadi Penyuluh
                  </h6>
                  <div class="section-divider"></div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nama" class="form-label required">
                        <i class="fas fa-user me-1"></i>Nama Lengkap
                      </label>
                      <input type="text" 
                             name="nama" 
                             id="nama" 
                             class="form-control enhanced-input" 
                             placeholder="Masukkan nama lengkap penyuluh"
                             value="<?= htmlspecialchars($penyuluh['nama']) ?>"
                             required>
                      <div class="input-feedback"></div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nip" class="form-label">
                        <i class="fas fa-id-card me-1"></i>NIP
                      </label>
                      <input type="text" 
                             name="nip" 
                             id="nip" 
                             class="form-control enhanced-input"
                             placeholder="Nomor Induk Pegawai"
                             value="<?= htmlspecialchars($penyuluh['nip']) ?>"
                             maxlength="18">
                      <div class="input-feedback"></div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-8">
                    <div class="form-group">
                      <label for="ttl" class="form-label">
                        <i class="fas fa-map-marker-alt me-1"></i>Tempat, Tanggal Lahir
                      </label>
                      <input type="text" 
                             name="ttl" 
                             id="ttl" 
                             class="form-control enhanced-input" 
                             placeholder="Contoh: Banjarmasin, 15 Januari 1985"
                             value="<?= htmlspecialchars($penyuluh['ttl']) ?>">
                      <div class="input-feedback"></div>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="jenis_kelamin" class="form-label required">
                        <i class="fas fa-venus-mars me-1"></i>Jenis Kelamin
                      </label>
                      <select name="jenis_kelamin" 
                              id="jenis_kelamin" 
                              class="form-select enhanced-select" 
                              required>
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="Laki-laki" <?= $penyuluh['jenis_kelamin'] === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="Perempuan" <?= $penyuluh['jenis_kelamin'] === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                      </select>
                      <div class="input-feedback"></div>
                    </div>
                  </div>
                </div>

                <!-- Field Nomor WhatsApp -->
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="nomor_wa" class="form-label">
                        <i class="fab fa-whatsapp me-1"></i>Nomor WhatsApp
                      </label>
                      <input type="text" 
                             name="nomor_wa" 
                             id="nomor_wa" 
                             class="form-control enhanced-input"
                             placeholder="Contoh: 081234567890"
                             value="<?= htmlspecialchars($penyuluh['nomor_wa'] ?? '') ?>"
                             maxlength="15">
                      <small class="form-text text-muted">
                        <i class="fas fa-info-circle me-1"></i>Format: 08xxxxxxxxxx (tanpa spasi atau tanda hubung)
                      </small>
                      <div class="input-feedback"></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Section 2: Data Kepangkatan -->
              <div class="form-section">
                <div class="section-header">
                  <h6 class="section-title">
                    <i class="fas fa-medal me-2"></i>
                    Data Kepangkatan
                  </h6>
                  <div class="section-divider"></div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="pangkat_terakhir" class="form-label">
                        <i class="fas fa-star me-1"></i>Pangkat Terakhir
                      </label>
                      <select name="pangkat_terakhir" 
                              id="pangkat_terakhir" 
                              class="form-select enhanced-select">
                        <option value="">-- Pilih Pangkat --</option>
                        <?php
                        $pangkat_list = ['Juru Muda', 'Juru Muda Tingkat I', 'Juru', 'Juru Tingkat I', 'Pengatur Muda', 'Pengatur Muda Tingkat I', 'Pengatur', 'Pengatur Tingkat I', 'Penata Muda', 'Penata Muda Tingkat I', 'Penata', 'Penata Tingkat I', 'Pembina', 'Pembina Tingkat I', 'Pembina Utama Muda', 'Pembina Utama Madya', 'Pembina Utama'];
                        foreach($pangkat_list as $pangkat):
                        ?>
                          <option value="<?= $pangkat ?>" <?= $penyuluh['pangkat_terakhir'] === $pangkat ? 'selected' : '' ?>>
                            <?= $pangkat ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <div class="input-feedback"></div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="golongan" class="form-label">
                        <i class="fas fa-layer-group me-1"></i>Golongan
                      </label>
                      <select name="golongan" 
                              id="golongan" 
                              class="form-select enhanced-select">
                        <option value="">-- Pilih Golongan --</option>
                        <?php
                        $golongan_list = ['I/a', 'I/b', 'I/c', 'I/d', 'II/a', 'II/b', 'II/c', 'II/d', 'III/a', 'III/b', 'III/c', 'III/d', 'IV/a', 'IV/b', 'IV/c', 'IV/d', 'IV/e'];
                        foreach($golongan_list as $gol):
                        ?>
                          <option value="<?= $gol ?>" <?= $penyuluh['golongan'] === $gol ? 'selected' : '' ?>>
                            <?= $gol ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <div class="input-feedback"></div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="tmt_pangkat" class="form-label">
                        <i class="fas fa-calendar-alt me-1"></i>T.M.T Pangkat
                      </label>
                      <input type="date" 
                             name="tmt_pangkat" 
                             id="tmt_pangkat" 
                             class="form-control enhanced-input"
                             value="<?= htmlspecialchars($penyuluh['tmt_pangkat']) ?>">
                      <div class="input-feedback"></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Section 3: Jabatan & Pendidikan -->
              <div class="form-section">
                <div class="section-header">
                  <h6 class="section-title">
                    <i class="fas fa-briefcase me-2"></i>
                    Jabatan & Pendidikan
                  </h6>
                  <div class="section-divider"></div>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="jabatan_terakhir" class="form-label">
                        <i class="fas fa-user-tie me-1"></i>Jabatan Terakhir
                      </label>
                      <input type="text" 
                             name="jabatan_terakhir" 
                             id="jabatan_terakhir" 
                             class="form-control enhanced-input"
                             placeholder="Contoh: Penyuluh Pertanian Ahli Pertama"
                             value="<?= htmlspecialchars($penyuluh['jabatan_terakhir']) ?>">
                      <div class="input-feedback"></div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="pendidikan_terakhir" class="form-label">
                        <i class="fas fa-graduation-cap me-1"></i>Pendidikan Terakhir
                      </label>
                      <select name="pendidikan_terakhir" 
                              id="pendidikan_terakhir" 
                              class="form-select enhanced-select">
                        <option value="">-- Pilih Pendidikan --</option>
                        <?php
                        $pendidikan_list = ['SD', 'SMP', 'SMA/SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S1 Pertanian', 'S1 Penyuluhan', 'S2', 'S3'];
                        foreach($pendidikan_list as $pend):
                        ?>
                          <option value="<?= $pend ?>" <?= $penyuluh['pendidikan_terakhir'] === $pend ? 'selected' : '' ?>>
                            <?= $pend ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <div class="input-feedback"></div>
                    </div>
                  </div>

                  <!-- KOLOM PRODI -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="prodi" class="form-label">
                        <i class="fas fa-book-open me-1"></i>Program Studi
                      </label>
                      <input type="text" 
                             name="prodi" 
                             id="prodi" 
                             class="form-control enhanced-input"
                             placeholder="Contoh: Teknik Informatika, Agribisnis"
                             value="<?= htmlspecialchars($penyuluh['prodi'] ?? '') ?>">
                      <div class="input-feedback"></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Form Actions -->
              <div class="form-actions">
                <div class="action-buttons">
                  <a href="penyuluh.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times me-2"></i>
                    Batal
                  </a>
                  <button type="button" class="btn btn-info btn-lg" id="previewBtn">
                    <i class="fas fa-eye me-2"></i>
                    Preview
                  </button>
                  <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                    <i class="fas fa-save me-2"></i>
                    <span class="btn-text">Update Data</span>
                    <div class="btn-loader">
                      <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                    </div>
                  </button>
                </div>
                
                <div class="form-summary">
                  <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Pastikan semua perubahan data sudah benar sebelum menyimpan
                  </small>
                </div>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</main>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-eye me-2"></i>
          Preview Perubahan Data Penyuluh
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="previewContent">
        <!-- Content will be populated by JavaScript -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-primary" id="confirmSubmit">
          <i class="fas fa-check me-2"></i>Simpan Perubahan
        </button>
      </div>
    </div>
  </div>
</div>

<style>
/* Gunakan CSS yang sama dengan form_tambah_penyuluh.php */
.main-content {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: 100vh;
  padding: 2rem 0;
}

.form-header {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
  border-radius: 20px;
  padding: 2rem;
  margin-bottom: 2rem;
  color: white;
  box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.header-content {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.header-icon {
  width: 60px;
  height: 60px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 1rem;
  backdrop-filter: blur(10px);
}

.header-icon i {
  font-size: 28px;
}

.form-title {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
}

.form-subtitle {
  margin: 0;
  opacity: 0.9;
  font-size: 1.1rem;
}

.progress-indicator {
  margin-top: 1rem;
}

.progress-bar {
  height: 6px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #43e97b, #38f9d7);
  border-radius: 3px;
  width: 100%;
  transition: width 0.3s ease;
}

.progress-text {
  opacity: 0.8;
}

.custom-alert {
  border-radius: 15px;
  border: none;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
}

.form-card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
  border: none;
  overflow: hidden;
}

.card-header {
  background: linear-gradient(135deg, #2c3e50, #34495e);
  color: white;
  padding: 1.5rem 2rem;
  border-bottom: none;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.card-tools .badge {
  font-size: 0.85rem;
  padding: 0.5rem 1rem;
  border-radius: 20px;
}

.card-body {
  padding: 2rem;
}

.form-section {
  margin-bottom: 2rem;
}

.section-header {
  margin-bottom: 1.5rem;
}

.section-title {
  color: #2c3e50;
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.section-divider {
  height: 3px;
  background: linear-gradient(90deg, #667eea, #764ba2);
  border-radius: 2px;
  width: 60px;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.form-label.required::after {
  content: ' *';
  color: #e74c3c;
  font-weight: bold;
}

.enhanced-input,
.enhanced-select {
  border: 2px solid #e9ecef;
  border-radius: 12px;
  padding: 12px 16px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: #f8f9fa;
}

.enhanced-input:focus,
.enhanced-select:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
  background: white;
  outline: none;
}

.enhanced-input.is-valid,
.enhanced-select.is-valid {
  border-color: #28a745;
  background: #f8fff9;
}

.form-actions {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid #e9ecef;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-bottom: 1rem;
}

.btn-lg {
  padding: 12px 24px;
  font-size: 1.1rem;
  border-radius: 12px;
  font-weight: 600;
  min-width: 140px;
}

#submitBtn {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e);
  border: none;
}

#submitBtn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.form-summary {
  text-align: center;
}

.input-feedback {
  margin-top: 0.25rem;
  font-size: 0.875rem;
}

.input-feedback.valid {
  color: #28a745;
}

.input-feedback.invalid {
  color: #dc3545;
}

@media (max-width: 768px) {
  .action-buttons {
    flex-direction: column;
  }
  .btn-lg {
    width: 100%;
  }
}
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    let isSubmitting = false;
    
    // Validation functions
    function validateField($field) {
        const value = $field.val().trim();
        const $feedback = $field.siblings('.input-feedback');
        let isValid = true;
        let message = '';
        
        if ($field.prop('required') && value === '') {
            isValid = false;
            message = 'Field ini wajib diisi';
        } else if (value !== '') {
            const fieldName = $field.attr('name');
            
            switch(fieldName) {
                case 'nama':
                    if (value.length < 3) {
                        isValid = false;
                        message = 'Nama minimal 3 karakter';
                    } else {
                        message = 'Nama valid';
                    }
                    break;
                    
                case 'nip':
                    if (value !== '' && !/^\d{18}$/.test(value)) {
                        isValid = false;
                        message = 'NIP harus 18 digit angka';
                    } else if (value !== '') {
                        message = 'NIP valid';
                    }
                    break;
                
                case 'nomor_wa':
                    if (value !== '') {
                        // Validasi format nomor WA Indonesia
                        if (!/^08\d{8,13}$/.test(value)) {
                            isValid = false;
                            message = 'Format: 08xxxxxxxxxx (10-15 digit)';
                        } else {
                            message = 'Nomor WhatsApp valid';
                        }
                    }
                    break;
                    
                default:
                    if (value !== '') {
                        message = 'Data valid';
                    }
            }
        }
        
        $field.removeClass('is-valid is-invalid');
        $feedback.removeClass('valid invalid').empty();
        
        if (value !== '') {
            if (isValid) {
                $field.addClass('is-valid');
                $feedback.addClass('valid').html(`<i class="fas fa-check-circle me-1"></i>${message}`);
            } else {
                $field.addClass('is-invalid');
                $feedback.addClass('invalid').html(`<i class="fas fa-exclamation-circle me-1"></i>${message}`);
            }
        }
        
        return isValid;
    }
    
    $('#penyuluhForm input, #penyuluhForm select').on('input change blur', function() {
        validateField($(this));
    });
    
    $('#penyuluhForm').on('submit', function(e) {
        e.preventDefault();
        
        if (isSubmitting) return;
        
        let allValid = true;
        $(this).find('input, select').each(function() {
            if (!validateField($(this))) {
                allValid = false;
            }
        });
        
        if (!allValid) {
            alert('Mohon lengkapi semua field yang diperlukan');
            return;
        }
        
        submitForm();
    });
    
    $('#previewBtn').click(function() {
        showPreview();
    });
    
    $('#confirmSubmit').click(function() {
        $('#previewModal').modal('hide');
        submitForm();
    });
    
    function submitForm() {
        isSubmitting = true;
        const $submitBtn = $('#submitBtn');
        $submitBtn.addClass('loading').prop('disabled', true);
        $('#penyuluhForm')[0].submit();
    }
    
    function showPreview() {
        const previewData = [];
        
        $('#penyuluhForm').find('input:not([type="hidden"]), select').each(function() {
            const $field = $(this);
            const name = $field.attr('name');
            const value = $field.val();
            const label = $field.closest('.form-group').find('label').text().replace('*', '').trim();
            
            if (name && label) {
                previewData.push({
                    label: label,
                    value: value || '-',
                    isEmpty: !value
                });
            }
        });
        
        let previewHtml = '<div class="preview-data">';
        previewData.forEach(item => {
            const valueClass = item.isEmpty ? 'preview-empty' : 'preview-value';
            previewHtml += `
                <div class="preview-item" style="display: flex; justify-content: space-between; padding: 0.75rem; background: #f8f9fa; border-radius: 8px; margin-bottom: 0.5rem;">
                    <span style="font-weight: 600;">${item.label}:</span>
                    <span class="${valueClass}">${item.value}</span>
                </div>
            `;
        });
        previewHtml += '</div>';
        
        $('#previewContent').html(previewHtml);
        $('#previewModal').modal('show');
    }
    
    // Input formatting untuk NIP
    $('#nip').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length > 18) {
            value = value.substring(0, 18);
        }
        $(this).val(value);
    });
    
    // Input formatting untuk nomor WA
    $('#nomor_wa').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length > 15) {
            value = value.substring(0, 15);
        }
        $(this).val(value);
    });
    
    // Dynamic pangkat-golongan matching
    $('#pangkat_terakhir').change(function() {
        const pangkat = $(this).val();
        const golonganMap = {
            'Juru Muda': 'I/a',
            'Juru Muda Tingkat I': 'I/b',
            'Juru': 'I/c',
            'Juru Tingkat I': 'I/d',
            'Pengatur Muda': 'II/a',
            'Pengatur Muda Tingkat I': 'II/b',
            'Pengatur': 'II/c',
            'Pengatur Tingkat I': 'II/d',
            'Penata Muda': 'III/a',
            'Penata Muda Tingkat I': 'III/b',
            'Penata': 'III/c',
            'Penata Tingkat I': 'III/d',
            'Pembina': 'IV/a',
            'Pembina Tingkat I': 'IV/b',
            'Pembina Utama Muda': 'IV/c',
            'Pembina Utama Madya': 'IV/d',
            'Pembina Utama': 'IV/e'
        };
        
        if (golonganMap[pangkat] && !$('#golongan').val()) {
            $('#golongan').val(golonganMap[pangkat]);
            validateField($('#golongan'));
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>