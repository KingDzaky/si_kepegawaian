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
$success = $_GET['success'] ?? '';
?>

<link rel="stylesheet" href="css/form_tambah_duk.css">

<main class="main-content">
  <div class="container-fluid">
    
    <!-- Progress Bar -->
    <div class="form-progress">
      <div class="form-progress-bar" id="progressBar"></div>
    </div>
    
    <!-- Page Header -->
    <div class="page-header fade-in">
      <h2><i class="fas fa-user-plus me-3"></i>Tambah Data DUK</h2>
      <p class="mb-0">Formulir Penambahan Data Urut Kepangkatan</p>
    </div>

    <div class="row justify-content-center">
      <div class="col-12 col-xl-10">

        <!-- Alert Messages -->
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger fade-in" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Error!</strong> <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
          <div class="alert alert-success fade-in" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Berhasil!</strong> <?= htmlspecialchars($success) ?>
          </div>
        <?php endif; ?>

        <div class="form-card fade-in">
          <div class="card-header">
            <i class="fas fa-edit me-2"></i>
            Form Data Pegawai
          </div>
          <div class="card-body">
            <form id="dukForm" action="proses_tambah_duk.php" method="POST" novalidate>
              
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
                               required minlength="3" maxlength="100"
                               placeholder="Masukkan nama lengkap">
                        <i class="input-icon fas fa-user"></i>
                      </div>
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">Nama valid!</div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="nip" class="form-label">
                        <i class="fas fa-id-card"></i>
                        NIP
                        <span class="tooltip-custom">
                          <i class="fas fa-question-circle ms-1"></i>
                          <span class="tooltip-text">Nomor Induk Pegawai (18 digit)</span>
                        </span>
                      </label>
                      <div class="input-group">
                        <input type="text" name="nip" id="nip" class="form-control with-icon"
                               pattern="[0-9]{18}" maxlength="18"
                               placeholder="198901012021011001">
                        <i class="input-icon fas fa-id-card"></i>
                      </div>
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">NIP valid!</div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="kartu_pegawai" class="form-label">
                        <i class="fas fa-id-badge"></i>
                        Kartu Pegawai <span class="required">*</span>
                      </label>
                      <div class="input-group">
                        <input type="text" name="kartu_pegawai" id="kartu_pegawai" class="form-control with-icon"
                              required maxlength="50"
                              placeholder="Masukkan nomor kartu pegawai">
                        <i class="input-icon fas fa-id-badge"></i>
                      </div>
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">Nomor kartu pegawai valid!</div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="ttl" class="form-label">
                        <i class="fas fa-birthday-cake"></i>
                        Tempat, Tanggal Lahir <span class="required">*</span>
                      </label>
                      <div class="input-group">
                        <input type="text" name="ttl" id="ttl" class="form-control with-icon" 
                               required placeholder="Banjarmasin, 01-01-1990">
                        <i class="input-icon fas fa-birthday-cake"></i>
                      </div>
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">TTL valid!</div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group fade-in">
                      <label for="jenis_kelamin" class="form-label">
                        <i class="fas fa-venus-mars"></i>
                        Jenis Kelamin <span class="required">*</span>
                      </label>
                      <select name="jenis_kelamin" id="jenis_kelamin" class="form-select" required>
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                      </select>
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">Pilihan valid!</div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="pendidikan_terakhir" class="form-label">
                        <i class="fas fa-graduation-cap"></i>
                        Pendidikan Terakhir <span class="required">*</span>
                      </label>
                      <select name="pendidikan_terakhir" id="pendidikan_terakhir" class="form-select" required>
                        <option value="">-- Pilih Pendidikan --</option>
                        <option value="SD">SD</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA">SMA</option>
                        <option value="D1">D1</option>
                        <option value="D2">D2</option>
                        <option value="D3">D3</option>
                        <option value="D4">D4</option>
                        <option value="S1">S1</option>
                        <option value="S2">S2</option>
                        <option value="S3">S3</option>
                      </select>
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">Pilihan valid!</div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="prodi" class="form-label">
                        <i class="fas fa-book"></i>
                        Program Studi/Jurusan <span class="required">*</span>
                      </label>
                      <div class="input-group">
                        <input type="text" name="prodi" id="prodi" class="form-control with-icon"
                              required maxlength="100"
                              placeholder="Contoh: Teknik Informatika">
                        <i class="input-icon fas fa-book"></i>
                      </div>
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">Program studi valid!</div>
                    </div>

                    <!-- NOMOR WHATSAPP - BARU -->
                    <div class="form-group fade-in">
                      <label for="nomor_wa" class="form-label">
                        <i class="fab fa-whatsapp text-success"></i>
                        Nomor WhatsApp <span class="required">*</span>
                        <span class="tooltip-custom">
                          <i class="fas fa-question-circle ms-1"></i>
                          <span class="tooltip-text">Untuk notifikasi kenaikan pangkat</span>
                        </span>
                      </label>
                      <div class="input-group">
                        <input type="text" name="nomor_wa" id="nomor_wa" class="form-control with-icon"
                              required pattern="[0-9]{10,15}" maxlength="15"
                              placeholder="081234567890">
                        <i class="input-icon fab fa-whatsapp"></i>
                      </div>
                      <small class="form-text text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Format: 08xxx atau 628xxx (10-15 digit)
                      </small>
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">Nomor WhatsApp valid!</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- OPD Section -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-building me-2"></i>
                  Informasi OPD
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group fade-in">
                      <label class="form-label">
                        <i class="fas fa-user-tie"></i>
                        Kepala OPD <span class="required">*</span>
                      </label>
                      <select name="id_opd" id="id_opd" class="form-select" required>
                        <option value="">-- Pilih Kepala OPD --</option>
                        <?php
                        $query_opd = "SELECT id, nama, jabatan, gelar_depan, gelar_belakang FROM kepala_opd WHERE status = 'aktif' ORDER BY nama";
                        $result_opd = $koneksi->query($query_opd);
                        while ($opd = $result_opd->fetch_assoc()):
                            $nama_lengkap = (!empty($opd['gelar_depan']) ? $opd['gelar_depan'] . ' ' : '') . 
                                            $opd['nama'] . 
                                            (!empty($opd['gelar_belakang']) ? ', ' . $opd['gelar_belakang'] : '');
                        ?>
                          <option value="<?= $opd['id'] ?>">
                            <?= htmlspecialchars($nama_lengkap . ' - ' . $opd['jabatan']) ?>
                          </option>
                        <?php endwhile; ?>
                      </select>
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">Pilihan valid!</div>
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
                        Pangkat Terakhir <span class="required">*</span>
                      </label>
                      <div class="input-group">
                        <input type="text" name="pangkat_terakhir" id="pangkat_terakhir" class="form-control with-icon"
                               required placeholder="Contoh: Penata Muda">
                        <i class="input-icon fas fa-medal"></i>
                      </div>
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">Pangkat valid!</div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="golongan" class="form-label">
                        <i class="fas fa-layer-group"></i>
                        Golongan <span class="required">*</span>
                      </label>
                      <select name="golongan" id="golongan" class="form-select" required>
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
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">Golongan valid!</div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="tmt_pangkat" class="form-label">
                        <i class="fas fa-calendar-alt"></i>
                        T.M.T Pangkat <span class="required">*</span>
                      </label>
                      <div class="input-group">
                        <input type="date" name="tmt_pangkat" id="tmt_pangkat" class="form-control with-icon" required>
                        <i class="input-icon fas fa-calendar-alt"></i>
                      </div>
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">Tanggal valid!</div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group fade-in">
                      <label for="jabatan_terakhir" class="form-label">
                        <i class="fas fa-user-tie"></i>
                        Jabatan Terakhir <span class="required">*</span>
                      </label>
                      <div class="input-group">
                        <input type="text" name="jabatan_terakhir" id="jabatan_terakhir" class="form-control with-icon"
                               required placeholder="Contoh: Staff Administrasi">
                        <i class="input-icon fas fa-user-tie"></i>
                      </div>
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">Jabatan valid!</div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="eselon" class="form-label">
                        <i class="fas fa-sitemap"></i>
                        Eselon <span class="required">*</span>
                      </label>
                      <select name="eselon" id="eselon" class="form-select" required>
                        <option value="">-- Pilih Eselon --</option>
                        <option value="Non-Eselon">Non-Eselon</option>
                        <option value="IV.b">IV.b</option>
                        <option value="IV.a">IV.a</option>
                        <option value="III.b">III.b</option>
                        <option value="III.a">III.a</option>
                        <option value="II.b">II.b</option>
                        <option value="II.a">II.a</option>
                        <option value="I.b">I.b</option>
                        <option value="I.a">I.a</option>
                      </select>
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">Eselon valid!</div>
                    </div>

                    <!-- Sub Dropdown untuk Non-Eselon: JFT/JFU -->
                    <div class="form-group fade-in" id="jenis_jabatan_group" style="display: none;">
                      <label for="jenis_jabatan" class="form-label">
                        <i class="fas fa-briefcase"></i>
                        Jenis Jabatan <span class="required">*</span>
                      </label>
                      <select name="jenis_jabatan" id="jenis_jabatan" class="form-select">
                        <option value="">-- Pilih Jenis Jabatan --</option>
                        <option value="JFT">JFT (Jabatan Fungsional Tertentu)</option>
                        <option value="JFU">JFU (Jabatan Fungsional Umum)</option>
                      </select>
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">Pilihan valid!</div>
                    </div>

                    <!-- Sub Dropdown untuk JFT -->
                    <div class="form-group fade-in" id="jft_tingkat_group" style="display: none;">
                      <label for="jft_tingkat" class="form-label">
                        <i class="fas fa-star"></i>
                        Tingkat JFT <span class="required">*</span>
                      </label>
                      <select name="jft_tingkat" id="jft_tingkat" class="form-select">
                        <option value="">-- Pilih Tingkat JFT --</option>
                        <option value="Ahli Pratama">Ahli Pratama</option>
                        <option value="Ahli Muda">Ahli Muda</option>
                        <option value="Ahli Madya">Ahli Madya</option>
                        <option value="Pemula">Pemula</option>
                        <option value="Terampil">Terampil</option>
                        <option value="Mahir">Mahir</option>
                        <option value="Penyelia">Penyelia</option>
                      </select>
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">Pilihan valid!</div>
                    </div>

                    <!-- Sub Dropdown untuk JFU -->
                    <div class="form-group fade-in" id="jfu_kelas_group" style="display: none;">
                      <label for="jfu_kelas" class="form-label">
                        <i class="fas fa-list-ol"></i>
                        Kelas JFU <span class="required">*</span>
                      </label>
                      <select name="jfu_kelas" id="jfu_kelas" class="form-select">
                        <option value="">-- Pilih Kelas JFU --</option>
                        <option value="5">Kelas 5</option>
                        <option value="6">Kelas 6</option>
                        <option value="7">Kelas 7</option>
                      </select>
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">Pilihan valid!</div>
                    </div>

                    <div class="form-group fade-in">
                      <label for="tmt_eselon" class="form-label">
                        <i class="fas fa-calendar-check"></i>
                        T.M.T Eselon <span class="required">*</span>
                      </label>
                      <div class="input-group">
                        <input type="date" name="tmt_eselon" id="tmt_eselon" class="form-control with-icon" required>
                        <i class="input-icon fas fa-calendar-check"></i>
                      </div>
                      <div class="invalid-feedback"></div>
                      <div class="valid-feedback">Tanggal valid!</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="btn-group-custom">
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                  <i class="fas fa-undo me-2"></i>Reset
                </button>
                <a href="dataduk.php" class="btn btn-secondary">
                  <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                  <i class="fas fa-save me-2"></i>Simpan Data
                </button>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</main>

<script>
// Script validation tetap sama, hanya tambah validasi nomor WA
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('dukForm');
  
  // Real-time validation untuk semua input
  const inputs = form.querySelectorAll('input, select');
  inputs.forEach(input => {
    input.addEventListener('blur', function() {
      validateField(this);
    });
    
    input.addEventListener('input', function() {
      if (this.classList.contains('is-invalid')) {
        validateField(this);
      }
    });
  });
  
  // Form submit validation
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
      if (!validateField(field)) {
        isValid = false;
      }
    });
    
    if (!isValid) {
      showNotification('Mohon lengkapi semua field yang wajib diisi!', 'danger');
      
      const firstError = form.querySelector('.is-invalid');
      if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstError.focus();
      }
      
      return false;
    }
    
    showNotification('Menyimpan data...', 'info');
    form.submit();
  });
});

function validateField(field) {
  const value = field.value.trim();
  const fieldName = field.getAttribute('name');
  const isRequired = field.hasAttribute('required');
  const feedbackElement = field.parentElement.querySelector('.invalid-feedback');
  
  field.classList.remove('is-invalid', 'is-valid');
  
  if (!isRequired) {
    if (value !== '') {
      field.classList.add('is-valid');
    }
    return true;
  }
  
  if (value === '') {
    field.classList.add('is-invalid');
    if (feedbackElement) {
      feedbackElement.textContent = 'Field ini wajib diisi!';
    }
    return false;
  }
  
  // Validasi khusus
  switch(fieldName) {
    case 'nama':
      if (value.length < 3) {
        field.classList.add('is-invalid');
        if (feedbackElement) feedbackElement.textContent = 'Nama minimal 3 karakter!';
        return false;
      }
      break;
      
    case 'nip':
      if (value !== '' && !/^[0-9]{18}$/.test(value)) {
        field.classList.add('is-invalid');
        if (feedbackElement) feedbackElement.textContent = 'NIP harus 18 digit angka!';
        return false;
      }
      break;
      
    case 'nomor_wa':
      // Hapus karakter non-digit untuk validasi
      const cleanNumber = value.replace(/[^0-9]/g, '');
      if (cleanNumber.length < 10 || cleanNumber.length > 15) {
        field.classList.add('is-invalid');
        if (feedbackElement) feedbackElement.textContent = 'Nomor WA harus 10-15 digit!';
        return false;
      }
      // Auto format saat valid
      field.value = cleanNumber;
      break;
      
    case 'prodi':
      if (value.length < 3) {
        field.classList.add('is-invalid');
        if (feedbackElement) feedbackElement.textContent = 'Program studi minimal 3 karakter!';
        return false;
      }
      break;
  }
  
  field.classList.add('is-valid');
  return true;
}

// Multilevel Dropdown Logic (sama seperti sebelumnya)
document.getElementById('eselon').addEventListener('change', function() {
  const eselonValue = this.value;
  const jenisJabatanGroup = document.getElementById('jenis_jabatan_group');
  const jftTingkatGroup = document.getElementById('jft_tingkat_group');
  const jfuKelasGroup = document.getElementById('jfu_kelas_group');
  
  const jenisJabatanSelect = document.getElementById('jenis_jabatan');
  const jftTingkatSelect = document.getElementById('jft_tingkat');
  const jfuKelasSelect = document.getElementById('jfu_kelas');
  
  jenisJabatanGroup.style.display = 'none';
  jftTingkatGroup.style.display = 'none';
  jfuKelasGroup.style.display = 'none';
  
  jenisJabatanSelect.value = '';
  jftTingkatSelect.value = '';
  jfuKelasSelect.value = '';
  
  jenisJabatanSelect.classList.remove('is-invalid', 'is-valid');
  jftTingkatSelect.classList.remove('is-invalid', 'is-valid');
  jfuKelasSelect.classList.remove('is-invalid', 'is-valid');
  
  jenisJabatanSelect.removeAttribute('required');
  jftTingkatSelect.removeAttribute('required');
  jfuKelasSelect.removeAttribute('required');
  
  if (eselonValue === 'Non-Eselon') {
    jenisJabatanGroup.style.display = 'block';
    jenisJabatanSelect.setAttribute('required', 'required');
  }
  
  validateField(this);
});

document.getElementById('jenis_jabatan').addEventListener('change', function() {
  const jenisValue = this.value;
  const jftTingkatGroup = document.getElementById('jft_tingkat_group');
  const jfuKelasGroup = document.getElementById('jfu_kelas_group');
  
  const jftTingkatSelect = document.getElementById('jft_tingkat');
  const jfuKelasSelect = document.getElementById('jfu_kelas');
  
  jftTingkatGroup.style.display = 'none';
  jfuKelasGroup.style.display = 'none';
  
  jftTingkatSelect.value = '';
  jfuKelasSelect.value = '';
  
  jftTingkatSelect.classList.remove('is-invalid', 'is-valid');
  jfuKelasSelect.classList.remove('is-invalid', 'is-valid');
  
  jftTingkatSelect.removeAttribute('required');
  jfuKelasSelect.removeAttribute('required');
  
  if (jenisValue === 'JFT') {
    jftTingkatGroup.style.display = 'block';
    jftTingkatSelect.setAttribute('required', 'required');
  } else if (jenisValue === 'JFU') {
    jfuKelasGroup.style.display = 'block';
    jfuKelasSelect.setAttribute('required', 'required');
  }
  
  validateField(this);
});

function resetForm() {
  const form = document.getElementById('dukForm');
  form.reset();
  
  document.getElementById('jenis_jabatan_group').style.display = 'none';
  document.getElementById('jft_tingkat_group').style.display = 'none';
  document.getElementById('jfu_kelas_group').style.display = 'none';
  
  document.getElementById('jenis_jabatan').removeAttribute('required');
  document.getElementById('jft_tingkat').removeAttribute('required');
  document.getElementById('jfu_kelas').removeAttribute('required');
  
  const allFields = form.querySelectorAll('input, select');
  allFields.forEach(field => {
    field.classList.remove('is-invalid', 'is-valid');
  });
  
  showNotification('Form berhasil direset!', 'info');
}

function showNotification(message, type = 'info') {
  const existingNotif = document.querySelector('.notification-toast');
  if (existingNotif) existingNotif.remove();
  
  const notification = document.createElement('div');
  notification.className = `alert alert-${type} notification-toast`;
  
  const icons = {
    'success': 'check-circle',
    'danger': 'exclamation-circle',
    'warning': 'exclamation-triangle',
    'info': 'info-circle'
  };
  
  notification.innerHTML = `
    <i class="fas fa-${icons[type]} me-2"></i>
    ${message}
  `;
  
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.classList.add('show');
  }, 10);
  
  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => notification.remove(), 300);
  }, 3000);
}

// Progress bar animation
window.addEventListener('scroll', function() {
  const windowHeight = window.innerHeight;
  const documentHeight = document.documentElement.scrollHeight - windowHeight;
  const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
  const scrollPercentage = (scrollTop / documentHeight) * 100;
  
  document.getElementById('progressBar').style.width = scrollPercentage + '%';
});

// Fade in animation on load
window.addEventListener('load', function() {
  const fadeElements = document.querySelectorAll('.fade-in');
  fadeElements.forEach((element, index) => {
    setTimeout(() => {
      element.style.opacity = '1';
      element.style.transform = 'translateY(0)';
    }, index * 50);
  });
});

// Auto-format NIP input
document.getElementById('nip').addEventListener('input', function(e) {
  let value = e.target.value.replace(/[^0-9]/g, '');
  if (value.length > 18) {
    value = value.substr(0, 18);
  }
  e.target.value = value;
});

// Auto-format Nomor WA input
document.getElementById('nomor_wa').addEventListener('input', function(e) {
  let value = e.target.value.replace(/[^0-9]/g, '');
  if (value.length > 15) {
    value = value.substr(0, 15);
  }
  e.target.value = value;
});

// Validasi real-time untuk nomor WA
document.getElementById('nomor_wa').addEventListener('blur', function() {
  const value = this.value.trim();
  const feedbackElement = this.parentElement.querySelector('.invalid-feedback');
  
  this.classList.remove('is-invalid', 'is-valid');
  
  if (value === '') {
    this.classList.add('is-invalid');
    if (feedbackElement) {
      feedbackElement.textContent = 'Nomor WhatsApp wajib diisi!';
    }
    return;
  }
  
  const cleanNumber = value.replace(/[^0-9]/g, '');
  
  if (cleanNumber.length < 10 || cleanNumber.length > 15) {
    this.classList.add('is-invalid');
    if (feedbackElement) {
      feedbackElement.textContent = 'Nomor WhatsApp harus 10-15 digit!';
    }
    return;
  }
  
  // Validasi format Indonesia
  if (!cleanNumber.startsWith('08') && !cleanNumber.startsWith('628')) {
    this.classList.add('is-invalid');
    if (feedbackElement) {
      feedbackElement.textContent = 'Format nomor tidak valid! Gunakan 08xxx atau 628xxx';
    }
    return;
  }
  
  this.classList.add('is-valid');
  this.value = cleanNumber;
});

// Prevent form submit on Enter key (except on submit button)
document.getElementById('dukForm').addEventListener('keypress', function(e) {
  if (e.key === 'Enter' && e.target.type !== 'submit') {
    e.preventDefault();
    return false;
  }
});

// Konfirmasi sebelum meninggalkan halaman jika ada perubahan
let formChanged = false;
document.getElementById('dukForm').addEventListener('change', function() {
  formChanged = true;
});

window.addEventListener('beforeunload', function(e) {
  if (formChanged) {
    e.preventDefault();
    e.returnValue = '';
    return '';
  }
});

// Reset formChanged saat submit
document.getElementById('dukForm').addEventListener('submit', function() {
  formChanged = false;
});
</script>

<style>
.notification-toast {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  min-width: 300px;
  opacity: 0;
  transform: translateX(400px);
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.notification-toast.show {
  opacity: 1;
  transform: translateX(0);
}

.form-progress {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: #e9ecef;
  z-index: 1000;
}

.form-progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #4e73df, #224abe);
  transition: width 0.3s ease;
  width: 0;
}

.fade-in {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.5s ease, transform 0.5s ease;
}

.input-icon {
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
  pointer-events: none;
  z-index: 5;
}

.form-control.with-icon {
  padding-right: 45px;
}

.tooltip-custom {
  position: relative;
  display: inline-block;
  cursor: help;
}

.tooltip-text {
  visibility: hidden;
  width: 200px;
  background-color: #333;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 8px;
  position: absolute;
  z-index: 1;
  bottom: 125%;
  left: 50%;
  margin-left: -100px;
  opacity: 0;
  transition: opacity 0.3s;
  font-size: 12px;
}

.tooltip-custom:hover .tooltip-text {
  visibility: visible;
  opacity: 1;
}

.required {
  color: #e74a3b;
  font-weight: bold;
}

.form-control:focus,
.form-select:focus {
  border-color: #4e73df;
  box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

.form-control.is-valid,
.form-select.is-valid {
  border-color: #1cc88a;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%231cc88a' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right calc(0.375em + 0.1875rem) center;
  background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
  padding-right: calc(1.5em + 0.75rem);
}

.form-control.is-invalid,
.form-select.is-invalid {
  border-color: #e74a3b;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23e74a3b'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23e74a3b' stroke='none'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right calc(0.375em + 0.1875rem) center;
  background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
  padding-right: calc(1.5em + 0.75rem);
}
</style>

<?php
require_once 'includes/footer.php';
?>