<?php
session_start();
require_once 'check_session.php';
if (!isAdmin()) { header('Location: dashboard.php?error=Akses ditolak'); exit; }
require_once 'config/koneksi.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';


$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Get data kepala OPD
$query = "SELECT * FROM kepala_opd ORDER BY status DESC, tmt_jabatan DESC";
$result = $koneksi->query($query);
?>

<link rel="stylesheet" href="css/dataduk.css">

<main class="main-content">
  <div class="dashboard-header fade-in">
    <h1 class="dashboard-title">
      <i class="fas fa-user-tie me-2"></i>
      Data Kepala OPD
    </h1>
    <p class="dashboard-subtitle">Manajemen Data Kepala Dinas DPPKBPM</p>
  </div>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="table-section fade-in">
    <div class="table-header">
      <h5 class="table-title">
        <i class="fas fa-table me-2"></i>
        Daftar Kepala OPD
      </h5>
      <a href="form_tambah_kepala_opd.php" class="btn btn-success btn-sm">
        <i class="fas fa-plus me-2"></i>Tambah Kepala OPD
      </a>
    </div>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th width="50">No</th>
            <th>Nama & Gelar</th>
            <th>NIP</th>
            <th>Pangkat/Gol</th>
            <th>Jabatan</th>
            <th>TMT Jabatan</th>
            <th>Status</th>
            <th width="150">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if ($result && $result->num_rows > 0):
            $no = 1;
            while ($row = $result->fetch_assoc()):
              $nama_lengkap = '';
              if (!empty($row['gelar_depan'])) $nama_lengkap .= $row['gelar_depan'] . ' ';
              $nama_lengkap .= $row['nama'];
              if (!empty($row['gelar_belakang'])) $nama_lengkap .= ', ' . $row['gelar_belakang'];
              
              $statusClass = $row['status'] === 'aktif' ? 'success' : 'secondary';
              $statusIcon = $row['status'] === 'aktif' ? 'check-circle' : 'times-circle';
          ?>
            <tr>
              <td><?= $no++ ?></td>
              <td>
                <div>
                  <strong><?= htmlspecialchars($nama_lengkap) ?></strong>
                  <?php if ($row['status'] === 'aktif'): ?>
                    <span class="badge bg-success ms-2">
                      <i class="fas fa-crown me-1"></i>Aktif
                    </span>
                  <?php endif; ?>
                </div>
              </td>
              <td><small><?= htmlspecialchars($row['nip']) ?></small></td>
              <td>
                <div>
                  <strong><?= htmlspecialchars($row['pangkat']) ?></strong>
                  <br><span class="badge badge-primary"><?= htmlspecialchars($row['golongan']) ?></span>
                </div>
              </td>
              <td><?= htmlspecialchars($row['jabatan']) ?></td>
              <td><small><?= date('d-m-Y', strtotime($row['tmt_jabatan'])) ?></small></td>
              <td>
                <span class="badge bg-<?= $statusClass ?>">
                  <i class="fas fa-<?= $statusIcon ?> me-1"></i>
                  <?= ucfirst($row['status']) ?>
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <a href="form_edit_kepala_opd.php?id=<?= $row['id'] ?>" 
                     class="btn btn-warning btn-sm" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                  <button onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars($nama_lengkap) ?>')" 
                          class="btn btn-danger btn-sm" title="Hapus">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php 
            endwhile;
          else:
          ?>
            <tr>
              <td colspan="8" class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5>Belum ada data Kepala OPD</h5>
                <p class="text-muted">Silakan tambahkan data terlebih dahulu</p>
                <a href="form_tambah_kepala_opd.php" class="btn btn-primary">
                  <i class="fas fa-plus me-2"></i>Tambah Data
                </a>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<script>
function confirmDelete(id, nama) {
  if (confirm(`Apakah Anda yakin ingin menghapus data "${nama}"?`)) {
    window.location.href = `proses_hapus_kepala_opd.php?id=${id}`;
  }
}
</script>

<?php require_once 'includes/footer.php'; ?>