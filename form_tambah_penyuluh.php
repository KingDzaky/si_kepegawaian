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

// Ambil error dari query string kalau ada
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>

<main class="main-content">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-lg-10 col-xl-8">

        <!-- Enhanced Header -->
        <div class="form-header">
          <div class="header-content">
            <div class="header-icon">
              <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="header-text">
              <h2 class="form-title">Tambah Data Penyuluh</h2>
              <p class="form-subtitle">Sistem Administrasi Kepegawaian </p>
            </div>
          </div>
          <div class="progress-indicator">
            <div class="progress-bar">
              <div class="progress-fill" id="formProgress"></div>
            </div>
            <small class="progress-text">Lengkapi semua field yang diperlukan</small>
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

        <?php if (!empty($success)): ?>
          <div class="alert alert-success alert-dismissible fade show custom-alert" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            Data berhasil ditambahkan!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <!-- Enhanced Form Card -->
        <div class="form-card">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-edit me-2"></i>
              Form Input Data Penyuluh 
            </h5>
            <div class="card-tools">
              <span class="badge bg-info">Formulir Baru</span>
            </div>
          </div>

          <div class="card-body">
            <form action="proses_tambah_penyuluh.php" method="POST" id="penyuluhForm" autocomplete="off" novalidate>
              
              <!-- Section 1: Data Pribadi -->
              <div class="form-section active" id="section1">
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
                             placeholder="Contoh: Banjarmasin, 15 Januari 1985">
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
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                      </select>
                      <div class="input-feedback"></div>
                    </div>
                  </div>
                </div>

                <!-- FIELD BARU: Nomor WhatsApp -->
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nomor_wa" class="form-label">
                        <i class="fab fa-whatsapp me-1 text-success"></i>Nomor WhatsApp
                      </label>
                      <input type="text" 
                             name="nomor_wa" 
                             id="nomor_wa" 
                             class="form-control enhanced-input"
                             placeholder="Contoh: 6281234567890"
                             maxlength="15">
                      <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Format: 62xxx (untuk notifikasi pensiun & kenaikan pangkat)
                      </small>
                      <div class="input-feedback"></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Section 2: Data Kepangkatan -->
              <div class="form-section" id="section2">
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
                        <option value="Juru Muda">Juru Muda</option>
                        <option value="Juru Muda Tingkat I">Juru Muda Tingkat I</option>
                        <option value="Juru">Juru</option>
                        <option value="Juru Tingkat I">Juru Tingkat I</option>
                        <option value="Pengatur Muda">Pengatur Muda</option>
                        <option value="Pengatur Muda Tingkat I">Pengatur Muda Tingkat I</option>
                        <option value="Pengatur">Pengatur</option>
                        <option value="Pengatur Tingkat I">Pengatur Tingkat I</option>
                        <option value="Penata Muda">Penata Muda</option>
                        <option value="Penata Muda Tingkat I">Penata Muda Tingkat I</option>
                        <option value="Penata">Penata</option>
                        <option value="Penata Tingkat I">Penata Tingkat I</option>
                        <option value="Pembina">Pembina</option>
                        <option value="Pembina Tingkat I">Pembina Tingkat I</option>
                        <option value="Pembina Utama Muda">Pembina Utama Muda</option>
                        <option value="Pembina Utama Madya">Pembina Utama Madya</option>
                        <option value="Pembina Utama">Pembina Utama</option>
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
                        <option value="I/a">I/a</option>
                        <option value="I/b">I/b</option>
                        <option value="I/c">I/c</option>
                        <option value="I/d">I/d</option>
                        <option value="II/a">II/a</option>
                        <option value="II/b">II/b</option>
                        <option value="II/c">II/c</option>
                        <option value="II/d">II/d</option>
                        <option value="III/a">III/a</option>
                        <option value="III/b">III/b</option>
                        <option value="III/c">III/c</option>
                        <option value="III/d">III/d</option>
                        <option value="IV/a">IV/a</option>
                        <option value="IV/b">IV/b</option>
                        <option value="IV/c">IV/c</option>
                        <option value="IV/d">IV/d</option>
                        <option value="IV/e">IV/e</option>
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
                             class="form-control enhanced-input">
                      <div class="input-feedback"></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Section 3: Jabatan & Pendidikan -->
              <div class="form-section" id="section3">
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
                             placeholder="Contoh: Penyuluh Ahli Pertama">
                      <div class="input-feedback"></div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="pendidikan_terakhir" class="form-label">
                        <i class="fas fa-graduation-cap me-1"></i>Pendidikan Terakhir
                      </label>
                      <select name="pendidikan_terakhir" 
                              id="pendidikan_terakhir" 
                              class="form-select enhanced-select">
                        <option value="">-- Pilih Pendidikan --</option>
                        <option value="SD">SD</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA/SMK">SMA/SMK</option>
                        <option value="D1">Diploma I (D1)</option>
                        <option value="D2">Diploma II (D2)</option>
                        <option value="D3">Diploma III (D3)</option>
                        <option value="D4">Diploma IV (D4)</option>
                        <option value="S1">Sarjana (S1)</option>
                        <option value="S2">Magister (S2)</option>
                        <option value="S3">Doktor (S3)</option>
                      </select>
                      <div class="input-feedback"></div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="prodi" class="form-label">
                        <i class="fas fa-book me-1"></i>
                        Program Studi/Jurusan <span class="required">*</span>
                      </label>
                      <input type="text" name="prodi" id="prodi" class="form-control enhanced-input"
                            required maxlength="100"
                            placeholder="Contoh: Teknik Informatika">
                      <div class="input-feedback"></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Form Actions -->
              <div class="form-actions">
                <div class="action-buttons">
                  <button type="button" class="btn btn-secondary btn-lg" id="cancelBtn">
                    <i class="fas fa-times me-2"></i>
                    Batal
                  </button>
                  <button type="button" class="btn btn-info btn-lg" id="previewBtn">
                    <i class="fas fa-eye me-2"></i>
                    Preview
                  </button>
                  <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                    <i class="fas fa-save me-2"></i>
                    <span class="btn-text">Simpan Data</span>
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
                    Pastikan semua data telah terisi dengan benar sebelum menyimpan
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
          Preview Data Penyuluh
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="previewContent">
        <!-- Content will be populated by JavaScript -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-primary" id="confirmSubmit">
          <i class="fas fa-check me-2"></i>Konfirmasi & Simpan
        </button>
      </div>
    </div>
  </div>
</div>

<style>
/* Gunakan CSS yang sama dengan form_tambah_duk.php */
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
  width: 0%;
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
  opacity: 1;
  transition: all 0.3s ease;
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
  position: relative;
}

.form-label {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.form-label.required::after,
.form-label .required::after {
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

.enhanced-input.is-invalid,
.enhanced-select.is-invalid {
  border-color: #dc3545;
  background: #fff8f8;
}

.input-feedback {
  font-size: 0.875rem;
  margin-top: 0.25rem;
  min-height: 1.25rem;
}

.input-feedback.valid {
  color: #28a745;
}

.input-feedback.invalid {
  color: #dc3545;
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
  position: relative;
  overflow: hidden;
}

#submitBtn {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e);
  border: none;
  transition: all 0.3s ease;
}

#submitBtn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

#submitBtn.loading .btn-text {
  opacity: 0;
}

#submitBtn.loading .btn-loader {
  opacity: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}

.btn-loader {
  opacity: 0;
  display: none;
  transition: opacity 0.3s ease;
}

.form-summary {
  text-align: center;
}

.preview-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  margin-bottom: 0.5rem;
}

.preview-label {
  font-weight: 600;
  color: #495057;
}

.preview-value {
  color: #212529;
  font-weight: 500;
}

.preview-empty {
  color: #6c757d;
  font-style: italic;
}

@media (max-width: 768px) {
  .form-header {
    padding: 1.5rem;
  }
  
  .header-content {
    flex-direction: column;
    text-align: center;
  }
  
  .header-icon {
    margin-right: 0;
    margin-bottom: 1rem;
  }
  
  .form-title {
    font-size: 1.5rem;
  }
  
  .card-body {
    padding: 1.5rem;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .btn-lg {
    width: 100%;
  }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.form-section {
  animation: fadeInUp 0.5s ease-out;
}
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    let formValid = false;
    let isSubmitting = false;
    
    // Form validation and progress tracking
    function updateFormProgress() {
        const totalFields = $('#penyuluhForm').find('input[required], select[required]').length;
        const filledFields = $('#penyuluhForm').find('input[required]:valid, select[required]').filter(function() {
            return $(this).val().trim() !== '';
        }).length;
        
        const progress = (filledFields / totalFields) * 100;
        $('#formProgress').css('width', progress + '%');
        
        if (progress === 100) {
            $('.progress-text').text('Semua field wajib telah terisi');
            formValid = true;
        } else {
            $('.progress-text').text(`${filledFields} dari ${totalFields} field wajib telah terisi`);
            formValid = false;
        }
        
        $('#submitBtn').prop('disabled', !formValid);
    }
    
    // Real-time validation
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
                        if (!/^62\d{9,13}$/.test(value)) {
                            isValid = false;
                            message = 'Format: 62xxx (10-15 digit)';
                        } else {
                            message = 'Nomor WA valid';
                        }
                    }
                    break;
                    
                case 'ttl':
                    if (value !== '' && !value.includes(',')) {
                        isValid = false;
                        message = 'Format: Tempat, Tanggal Lahir';
                    } else if (value !== '') {
                        message = 'Format TTL valid';
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
    
    // Event handlers
    $('#penyuluhForm input, #penyuluhForm select').on('input change blur', function() {
        validateField($(this));
        updateFormProgress();
    });
    
    // Form submission
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
    
    // Preview functionality
    $('#previewBtn').click(function() {
        if (!formValid) {
            alert('Lengkapi field wajib terlebih dahulu');
            return;
        }
        showPreview();
    });
    
    // Confirm submit from modal
    $('#confirmSubmit').click(function() {
        $('#previewModal').modal('hide');
        submitForm();
    });
    
    // Cancel button
    $('#cancelBtn').click(function() {
        if (confirm('Apakah Anda yakin ingin membatalkan? Data yang telah diisi akan hilang.')) {
            window.location.href = 'penyuluh.php';
        }
    });
    
    // Form submission function
    function submitForm() {
        isSubmitting = true;
        const $submitBtn = $('#submitBtn');
        $submitBtn.addClass('loading').prop('disabled', true);
        
        $('#penyuluhForm')[0].submit();
    }
    
    // Preview function
    function showPreview() {
        const previewData = [];
        
        $('#penyuluhForm').find('input, select').each(function() {
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
                <div class="preview-item">
                    <span class="preview-label">${item.label}:</span>
                    <span class="${valueClass}">${item.value}</span>
                </div>
            `;
        });
        previewHtml += '</div>';
        
        $('#previewContent').html(previewHtml);
        $('#previewModal').modal('show');
    }
    
    // Input formatting
    $('#nip').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length > 18) {
            value = value.substring(0, 18);
        }
        $(this).val(value);
    });
    
    // Nomor WA formatting
    $('#nomor_wa').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length > 15) {
            value = value.substring(0, 15);
        }
        // Auto-add 62 prefix
        if (value && !value.startsWith('62')) {
            if (value.startsWith('0')) {
                value = '62' + value.substring(1);
            } else if (value.startsWith('8')) {
                value = '62' + value;
            }
        }
        $(this).val(value);
    });
    
    // TTL formatting helper
    $('#ttl').on('blur', function() {
        let value = $(this).val().trim();
        if (value && !value.includes(',')) {
            if (value.match(/\d{4}-\d{2}-\d{2}/)) {
                $(this).val(`Banjarmasin, ${value}`);
            }
        }
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
    
    // Initialize
    updateFormProgress();
    
    // Keyboard shortcuts
    $(document).keydown(function(e) {
        // Ctrl + S to save
        if (e.ctrlKey && e.which === 83) {
            e.preventDefault();
            if (formValid && !isSubmitting) {
                $('#penyuluhForm').submit();
            }
        }
        
        // Ctrl + P for preview
        if (e.ctrlKey && e.which === 80) {
            e.preventDefault();
            $('#previewBtn').click();
        }
        
        // Escape to cancel
        if (e.which === 27) {
            $('#cancelBtn').click();
        }
    });
    
    // Prevent accidental page leave
    window.addEventListener('beforeunload', function(e) {
        const hasData = $('#penyuluhForm').find('input, select').toArray().some(el => $(el).val().trim() !== '');
        if (hasData && !isSubmitting) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>