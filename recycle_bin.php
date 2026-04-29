<?php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/soft_delete_functions.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Hanya superadmin yang bisa akses
if (!isAdmin()) { header('Location: dashboard.php?error=Akses ditolak'); exit; }

// Get deleted records
$deleted_duk = getDeletedRecords($koneksi, 'duk');
$deleted_count = count($deleted_duk);
?>

<link rel="stylesheet" href="css/dataduk.css">

<main class="main-content">
  <div class="dashboard-header fade-in">
    <h1 class="dashboard-title">
      <i class="fas fa-trash-restore me-2"></i>
      Recycle Bin - Data Terhapus
    </h1>
    <p class="dashboard-subtitle">Kelola data yang telah dihapus</p>
  </div>

  <!-- Info Alert -->
  <div class="alert alert-info fade-in">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Informasi:</strong> Data yang dihapus akan disimpan selama <strong>5 tahun</strong> sebelum dihapus permanen secara otomatis.
    <br>
    <small class="text-muted">Anda dapat me-restore data yang terhapus dalam jangka waktu tersebut.</small>
  </div>

  <!-- Statistics -->
  <div class="stats-container fade-in">
    <div class="stat-card warning">
      <div class="stat-icon">
        <i class="fas fa-trash"></i>
      </div>
      <h3 class="stat-number"><?= $deleted_count ?></h3>
      <p class="stat-label">Data Terhapus</p>
    </div>
  </div>

  <!-- Table Section -->
  <div class="table-section fade-in">
    <div class="table-header">
      <h5 class="table-title">
        <i class="fas fa-table me-2"></i>
        Daftar Data DUK Terhapus
      </h5>
    </div>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Pegawai</th>
            <th>Pangkat/Golongan</th>
            <th>Jabatan</th>
            <th>Dihapus Oleh</th>
            <th>Tanggal Hapus</th>
            <th>Sisa Waktu</th>
            <th>Alasan</th>
            <th width="200">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($deleted_duk)): ?>
            <?php foreach ($deleted_duk as $row): ?>
            <tr>
              <td>
                <div>
                  <strong><?= htmlspecialchars($row['nama']) ?></strong>
                  <br><small><?= htmlspecialchars($row['nip'] ?: '-') ?></small>
                </div>
              </td>
              <td>
                <strong><?= htmlspecialchars($row['pangkat_terakhir'] ?: '-') ?></strong>
                <br><span class="badge badge-primary"><?= htmlspecialchars($row['golongan'] ?: '-') ?></span>
              </td>
              <td><?= htmlspecialchars($row['jabatan_terakhir'] ?: '-') ?></td>
              <td><?= htmlspecialchars($row['deleted_by_name'] ?: 'System') ?></td>
              <td>
                <small><?= date('d-m-Y H:i', strtotime($row['deleted_at'])) ?></small>
              </td>
              <td>
                <?php 
                $days = (int)$row['days_remaining'];
                $years = floor($days / 365);
                $remaining_days = $days % 365;
                
                if ($days > 0): 
                ?>
                  <span class="badge badge-<?= $days > 365 ? 'success' : 'warning' ?>">
                    <?= $years ?> tahun <?= $remaining_days ?> hari
                  </span>
                <?php else: ?>
                  <span class="badge badge-danger">Akan dihapus permanen</span>
                <?php endif; ?>
              </td>
              <td>
                <small><?= htmlspecialchars($row['delete_reason'] ?: '-') ?></small>
              </td>
              <td>
                <div class="action-buttons">
                  <a href="proses_restore_duk.php?id=<?= $row['id'] ?>" 
                     class="btn btn-success btn-sm" 
                     onclick="return confirm('Restore data ini?')"
                     data-bs-toggle="tooltip" title="Restore Data">
                    <i class="fas fa-undo"></i> Restore
                  </a>
                  <a href="proses_hapus_permanen_duk.php?id=<?= $row['id'] ?>" 
                     class="btn btn-danger btn-sm" 
                     onclick="return confirm('HAPUS PERMANEN? Data tidak bisa dikembalikan!')"
                     data-bs-toggle="tooltip" title="Hapus Permanen">
                    <i class="fas fa-times"></i> Hapus Permanen
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="empty-state">
                <i class="fas fa-inbox"></i>
                <h5>Recycle Bin Kosong</h5>
                <p>Tidak ada data yang terhapus</p>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>