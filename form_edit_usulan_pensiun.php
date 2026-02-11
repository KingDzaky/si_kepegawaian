<?php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';

if (!isAdmin()) {
    header('Location: usulan_pensiun.php?error=Akses ditolak');
    exit;
}

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    header('Location: usulan_pensiun.php?error=ID tidak valid');
    exit;
}

// Ambil data usulan
$query = "SELECT * FROM usulan_pensiun WHERE id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: usulan_pensiun.php?error=Data tidak ditemukan');
    exit;
}

$data = $result->fetch_assoc();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<link rel="stylesheet" href="css/dataduk.css">

<main class="main-content">
  <div class="dashboard-header fade-in">
    <h1 class="dashboard-title">
      <i class="fas fa-edit me-2"></i>
      Edit Usulan Pensiun
    </h1>
    <p class="dashboard-subtitle">Ubah Data Usulan Pensiun Pegawai</p>
  </div>

  <div class="form-section fade-in">
    <form id="formEditUsulan" action="proses_edit_usulan_pensiun.php" method="POST">
      <input type="hidden" name="id" value="<?= $id ?>">
      
      <!-- Informasi Usulan -->
      <div class="card mb-4">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Informasi Usulan</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Nomor Usulan <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nomor_usulan" 
                       value="<?= htmlspecialchars($data['nomor_usulan']) ?>" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Tanggal Usulan <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="tanggal_usulan" 
                       value="<?= $data['tanggal_usulan'] ?>" required>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Data Pegawai (Read-only) -->
      <div class="card mb-4">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0"><i class="fas fa-user me-2"></i>Data Pegawai (Tidak dapat diubah)</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Sumber Data</label>
                <input type="text" class="form-control bg-light" 
                       value="<?= strtoupper($data['sumber_data']) ?>" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">NIP</label>
                <input type="text" class="form-control bg-light" 
                       value="<?= htmlspecialchars($data['nip']) ?>" readonly>
              </div>
            </div>
            <div class="col-md-12">
              <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control bg-light" 
                       value="<?= htmlspecialchars($data['nama']) ?>" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Tanggal Lahir</label>
                <input type="date" class="form-control bg-light" 
                       value="<?= $data['tanggal_lahir'] ?>" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Tanggal Pensiun</label>
                <input type="date" class="form-control bg-light" 
                       value="<?= $data['tanggal_pensiun'] ?>" readonly>
              </div>
            </div>
          </div>
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Info:</strong> Data pegawai tidak dapat diubah. Jika perlu mengubah pegawai, hapus usulan ini dan buat usulan baru.
          </div>
        </div>
      </div>

      <!-- Informasi Pensiun (Editable) -->
      <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
          <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Informasi Pensiun</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Jenis Pensiun <span class="text-danger">*</span></label>
                <select class="form-select" name="jenis_pensiun" required>
                  <option value="BUP" <?= $data['jenis_pensiun'] === 'BUP' ? 'selected' : '' ?>>
                    BUP (Batas Usia Pensiun)
                  </option>
                  <option value="APS" <?= $data['jenis_pensiun'] === 'APS' ? 'selected' : '' ?>>
                    APS (Atas Permintaan Sendiri)
                  </option>
                  <option value="Permintaan Sendiri" <?= $data['jenis_pensiun'] === 'Permintaan Sendiri' ? 'selected' : '' ?>>
                    Permintaan Sendiri
                  </option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Status Usulan <span class="text-danger">*</span></label>
                <select class="form-select" name="status" required>
                  <option value="draft" <?= $data['status'] === 'draft' ? 'selected' : '' ?>>
                    Draft
                  </option>
                  <option value="diajukan" <?= $data['status'] === 'diajukan' ? 'selected' : '' ?>>
                    Diajukan
                  </option>
                  <option value="disetujui" <?= $data['status'] === 'disetujui' ? 'selected' : '' ?>>
                    Disetujui
                  </option>
                  <option value="ditolak" <?= $data['status'] === 'ditolak' ? 'selected' : '' ?>>
                    Ditolak
                  </option>
                </select>
              </div>
            </div>
            <div class="col-md-12">
              <div class="mb-3">
                <label class="form-label">Alasan / Keterangan</label>
                <textarea class="form-control" name="alasan" rows="3" 
                          placeholder="Opsional: Tambahkan alasan atau keterangan"><?= htmlspecialchars($data['alasan']) ?></textarea>
              </div>
            </div>
            <div class="col-md-12">
              <div class="mb-3">
                <label class="form-label">Keterangan Tambahan</label>
                <textarea class="form-control" name="keterangan" rows="2" 
                          placeholder="Opsional: Catatan internal"><?= htmlspecialchars($data['keterangan']) ?></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tombol Submit -->
      <div class="d-flex gap-2 justify-content-end">
        <a href="usulan_pensiun.php" class="btn btn-secondary">
          <i class="fas fa-times me-2"></i>Batal
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save me-2"></i>Update Usulan
        </button>
      </div>
    </form>
  </div>
</main>

<?php 
require_once 'includes/footer.php';
$stmt->close();
$koneksi->close();
?>