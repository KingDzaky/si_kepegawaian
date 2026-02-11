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
?>

<link rel="stylesheet" href="css/form_tambah_duk.css">

<main class="main-content">
  <div class="container-fluid">
    <div class="page-header fade-in">
      <h2><i class="fas fa-user-tie me-3"></i>Tambah Kepala OPD</h2>
      <p class="mb-0">Formulir Penambahan Data Kepala Dinas</p>
    </div>

    <div class="row justify-content-center">
      <div class="col-12 col-xl-8">

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger fade-in">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Error!</strong> <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <div class="form-card fade-in">
          <div class="card-header">
            <i class="fas fa-edit me-2"></i>Form Data Kepala OPD
          </div>
          <div class="card-body">
            <form action="proses_tambah_kepala_opd.php" method="POST" id="kepalaOpdForm">
              
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-user me-2"></i>
                  Informasi Personal
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="gelar_depan" class="form-label">
                        <i class="fas fa-graduation-cap"></i>
                        Gelar Depan
                      </label>
                      <input type="text" name="gelar_depan" id="gelar_depan" 
                             class="form-control" placeholder="Contoh: Dra., Ir., Dr.">
                      <small class="text-muted">Opsional</small>
                    </div>

                    <div class="form-group">
                      <label for="nama" class="form-label">
                        <i class="fas fa-user"></i>
                        Nama Lengkap <span class="required">*</span>
                      </label>
                      <input type="text" name="nama" id="nama" 
                             class="form-control" required placeholder="Masukkan nama lengkap">
                    </div>

                    <div class="form-group">
                      <label for="gelar_belakang" class="form-label">
                        <i class="fas fa-award"></i>
                        Gelar Belakang
                      </label>
                      <input type="text" name="gelar_belakang" id="gelar_belakang" 
                             class="form-control" placeholder="Contoh: M.Si, S.H., M.M.">
                      <small class="text-muted">Opsional</small>
                    </div>

                    <div class="form-group">
                      <label for="nip" class="form-label">
                        <i class="fas fa-id-card"></i>
                        NIP <span class="required">*</span>
                      </label>
                      <input type="text" name="nip" id="nip" 
                             class="form-control" required maxlength="18" 
                             placeholder="198901012021011001">
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="pangkat" class="form-label">
                        <i class="fas fa-medal"></i>
                        Pangkat <span class="required">*</span>
                      </label>
                      <input type="text" name="pangkat" id="pangkat" 
                             class="form-control" required placeholder="Contoh: Pembina Utama Muda">
                    </div>

                    <div class="form-group">
                      <label for="golongan" class="form-label">
                        <i class="fas fa-layer-group"></i>
                        Golongan <span class="required">*</span>
                      </label>
                      <select name="golongan" id="golongan" class="form-select" required>
                        <option value="">-- Pilih Golongan --</option>
                        <option value="IV e">IV e</option>
                        <option value="IV d">IV d</option>
                        <option value="IV c">IV c</option>
                        <option value="IV b">IV b</option>
                        <option value="IV a">IV a</option>
                        <option value="III d">III d</option>
                        <option value="III c">III c</option>
                        <option value="III b">III b</option>
                        <option value="III a">III a</option>
                      </select>
                    </div>

                    <div class="form-group">
                      <label for="jabatan" class="form-label">
                        <i class="fas fa-briefcase"></i>
                        Jabatan <span class="required">*</span>
                      </label>
                      <input type="text" name="jabatan" id="jabatan" 
                             class="form-control" required value="Kepala Dinas">
                    </div>

                    <div class="form-group">
                      <label for="tmt_jabatan" class="form-label">
                        <i class="fas fa-calendar"></i>
                        TMT Jabatan <span class="required">*</span>
                      </label>
                      <input type="date" name="tmt_jabatan" id="tmt_jabatan" 
                             class="form-control" required>
                    </div>

                    <div class="form-group">
                      <label for="status" class="form-label">
                        <i class="fas fa-toggle-on"></i>
                        Status <span class="required">*</span>
                      </label>
                      <select name="status" id="status" class="form-select" required>
                        <option value="aktif">Aktif</option>
                        <option value="non-aktif">Non-Aktif</option>
                      </select>
                      <small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Hanya 1 Kepala OPD yang bisa aktif
                      </small>
                    </div>
                  </div>
                </div>
              </div>

              <div class="btn-group-custom">
                <a href="kepala_opd.php" class="btn btn-secondary">
                  <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-primary">
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

<?php require_once 'includes/footer.php'; ?>
