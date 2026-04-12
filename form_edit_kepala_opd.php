<?php
// ============================================================================
// SECTION 1: SESSION & LOGIC (SEBELUM OUTPUT APAPUN)
// ============================================================================
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';

// ✅ SEMUA VALIDASI & REDIRECT DULU (sebelum include header.php)

// Cek akses admin
if (!isAdmin()) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}

// Validasi ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: kepala_opd.php?error=ID tidak ditemukan');
    exit;
}

// Ambil data dari database
$query = "SELECT * FROM kepala_opd WHERE id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header('Location: kepala_opd.php?error=Data tidak ditemukan');
    exit;
}

$data = $result->fetch_assoc();
$stmt->close();

// ============================================================================
// SECTION 2: INCLUDE HEADER (SETELAH SEMUA LOGIC SELESAI)
// ============================================================================
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Ambil error dari GET jika ada
$error = $_GET['error'] ?? '';
?>

<!-- ============================================================================
     SECTION 3: HTML OUTPUT (SEKARANG BARU BOLEH ADA HTML)
     ============================================================================ -->
<link rel="stylesheet" href="css/form_tambah_duk.css">

<main class="main-content">
  <div class="container-fluid">
    <div class="page-header fade-in">
      <h2><i class="fas fa-edit me-3"></i>Edit Kepala OPD</h2>
      <p class="mb-0">Formulir Edit Data Kepala Dinas</p>
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
            <i class="fas fa-edit me-2"></i>
            Form Edit Kepala OPD: <?= htmlspecialchars($data['nama']) ?>
          </div>
          <div class="card-body">
            <!-- ✅ Form action ke proses_edit_kepala_opd.php -->
            <form action="proses_edit_kepala_opd.php" method="POST" novalidate>
              <input type="hidden" name="id" value="<?= $data['id'] ?>">
              
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-user me-2"></i>
                  Informasi Personal
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group fade-in">
                      <label for="gelar_depan" class="form-label">
                        <i class="fas fa-graduation-cap"></i>
                        Gelar Depan
                      </label>
                      <input type="text" name="gelar_depan" id="gelar_depan" 
                             class="form-control" 
                             value="<?= htmlspecialchars($data['gelar_depan'] ?? '') ?>"
                             placeholder="Contoh: Dr., Drs., Prof.">
                      <small class="form-text text-muted">Opsional</small>
                    </div>

                    <div class="form-group fade-in">
                      <label for="nama" class="form-label">
                        <i class="fas fa-user"></i>
                        Nama Lengkap <span class="required">*</span>
                      </label>
                      <input type="text" name="nama" id="nama" 
                             class="form-control" required 
                             value="<?= htmlspecialchars($data['nama']) ?>"
                             minlength="3" maxlength="100">
                    </div>

                    <div class="form-group fade-in">
                      <label for="gelar_belakang" class="form-label">
                        <i class="fas fa-award"></i>
                        Gelar Belakang
                      </label>
                      <input type="text" name="gelar_belakang" id="gelar_belakang" 
                             class="form-control" 
                             value="<?= htmlspecialchars($data['gelar_belakang'] ?? '') ?>"
                             placeholder="Contoh: S.H., M.Si., M.M.">
                      <small class="form-text text-muted">Opsional</small>
                    </div>

                    <div class="form-group fade-in">
                      <label for="nip" class="form-label">
                        <i class="fas fa-id-card"></i>
                        NIP <span class="required">*</span>
                      </label>
                      <input type="text" name="nip" id="nip" 
                             class="form-control" required 
                             pattern="[0-9]{18}" maxlength="18"
                             value="<?= htmlspecialchars($data['nip']) ?>"
                             placeholder="18 digit angka">
                      <small class="form-text text-muted">18 digit angka</small>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group fade-in">
                      <label for="pangkat" class="form-label">
                        <i class="fas fa-medal"></i>
                        Pangkat <span class="required">*</span>
                      </label>
                      <input type="text" name="pangkat" id="pangkat" 
                             class="form-control" required 
                             value="<?= htmlspecialchars($data['pangkat']) ?>"
                             placeholder="Contoh: Pembina Utama Muda">
                    </div>

                    <div class="form-group fade-in">
                      <label for="golongan" class="form-label">
                        <i class="fas fa-layer-group"></i>
                        Golongan <span class="required">*</span>
                      </label>
                      <select name="golongan" id="golongan" class="form-select" required>
                        <option value="">-- Pilih Golongan --</option>
                        <?php
                        $golongan_list = ['IV e', 'IV d', 'IV c', 'IV b', 'IV a', 
                                          'III d', 'III c', 'III b', 'III a',
                                          'II d', 'II c', 'II b', 'II a'];
                        foreach ($golongan_list as $gol):
                          $selected = ($data['golongan'] === $gol) ? 'selected' : '';
                        ?>
                          <option value="<?= $gol ?>" <?= $selected ?>><?= $gol ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="form-group fade-in">
                      <label for="jabatan" class="form-label">
                        <i class="fas fa-briefcase"></i>
                        Jabatan <span class="required">*</span>
                      </label>
                      <input type="text" name="jabatan" id="jabatan" 
                             class="form-control" required 
                             value="<?= htmlspecialchars($data['jabatan']) ?>"
                             placeholder="Contoh: Kepala Dinas">
                    </div>

                    <div class="form-group fade-in">
                      <label for="tmt_jabatan" class="form-label">
                        <i class="fas fa-calendar"></i>
                        TMT Jabatan
                      </label>
                      <input type="date" name="tmt_jabatan" id="tmt_jabatan" 
                             class="form-control" 
                             value="<?= $data['tmt_jabatan'] ?? '' ?>">
                      <small class="form-text text-muted">Terhitung Mulai Tanggal</small>
                    </div>

                    <div class="form-group fade-in">
                      <label for="status" class="form-label">
                        <i class="fas fa-toggle-on"></i>
                        Status <span class="required">*</span>
                      </label>
                      <select name="status" id="status" class="form-select" required>
                        <option value="aktif" <?= $data['status'] === 'aktif' ? 'selected' : '' ?>>
                          Aktif
                        </option>
                        <option value="non-aktif" <?= $data['status'] === 'non-aktif' ? 'selected' : '' ?>>
                          Non-Aktif
                        </option>
                      </select>
                      <div class="alert alert-info mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        <small>
                          <strong>Catatan:</strong> Hanya 1 Kepala OPD yang bisa aktif. 
                          Jika diubah ke Aktif, yang lain otomatis Non-Aktif.
                        </small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="btn-group-custom">
                <a href="kepala_opd.php" class="btn btn-secondary">
                  <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
                <button type="reset" class="btn btn-warning">
                  <i class="fas fa-undo me-2"></i>Reset
                </button>
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

<!-- ✅ Include alert handler di bawah -->
<?php include 'includes/alert_handler.php'; ?>

<?php require_once 'includes/footer.php'; ?>