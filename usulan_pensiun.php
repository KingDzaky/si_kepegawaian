<?php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/wa_functions.php';

// Superadmin, admin, dan kepala dinas bisa akses
if (!hasRole(['superadmin', 'admin', 'kepala_dinas'])) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}

// Tapi hanya admin/superadmin yang bisa tambah/edit/hapus
$can_edit = isAdmin();

// Include header dan sidebar SETELAH koneksi
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Query dengan JOIN ke notifikasi_pensiun DAN hitung hari tersisa
$query = "SELECT 
            up.*,
            DATEDIFF(up.tanggal_pensiun, CURDATE()) as hari_tersisa,
            
            -- Cek notifikasi approval
            (SELECT COUNT(*) FROM notifikasi_pensiun 
             WHERE id_usulan_pensiun = up.id 
             AND jenis_notif = 'approval'
             AND status = 'terkirim') as approval_terkirim,
            
            -- Cek reminder 1 tahun
            (SELECT COUNT(*) FROM notifikasi_pensiun 
             WHERE id_usulan_pensiun = up.id 
             AND jenis_notif = 'reminder_1_tahun'
             AND status = 'terkirim') as reminder_1_tahun_terkirim,
            
            -- Cek reminder 1 bulan
            (SELECT COUNT(*) FROM notifikasi_pensiun 
             WHERE id_usulan_pensiun = up.id 
             AND jenis_notif = 'reminder_1_bulan'
             AND status = 'terkirim') as reminder_1_bulan_terkirim,
            
            -- Cek reminder 1 minggu
            (SELECT COUNT(*) FROM notifikasi_pensiun 
             WHERE id_usulan_pensiun = up.id 
             AND jenis_notif = 'reminder_1_minggu'
             AND status = 'terkirim') as reminder_1_minggu_terkirim
             
          FROM usulan_pensiun up
          ORDER BY up.created_at DESC";
          
$result = $koneksi->query($query);
?>

<link rel="stylesheet" href="css/dataduk.css">

<main class="main-content">
  <div class="dashboard-header fade-in">
    <h1 class="dashboard-title">
      <i class="fas fa-user-clock me-2"></i>
      Usulan Pensiun
    </h1>
    <p class="dashboard-subtitle">Manajemen Usulan Pensiun Pegawai ASN & Penyuluh</p>
  </div>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Alert untuk notifikasi WA -->
  <div id="alertNotifWA"></div>

  <div class="table-section fade-in">
    <div class="table-header">
      <h5 class="table-title">
        <i class="fas fa-table me-2"></i>Daftar Usulan Pensiun
      </h5>
      <?php if ($can_edit): ?>
      <a href="form_tambah_usulan_pensiun.php" class="btn btn-success btn-sm">
        <i class="fas fa-plus me-2"></i>Tambah Usulan
      </a>
      <?php endif; ?>
    </div>

    <!-- Pencarian -->
    <div class="mt-3 mb-3 ms-3 me-3">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-bold">
            <i class="fas fa-search"></i> Cari Pegawai/No. Usulan
          </label>
          <input type="text" class="form-control" id="searchInput" 
                 placeholder="Ketik nama pegawai, NIP, Tahun atau Nomor Usulan..." 
                 onkeyup="filterTable()">
        </div>
        <div class="col-md-6 d-flex align-items-end">
          <button class="btn btn-secondary" onclick="resetSearch()">
            <i class="fas fa-redo"></i> Reset Pencarian
          </button>
        </div>
      </div>
    
    </div>

    <!-- Filter Tab -->
    <div class="mt-3 mb-3 ms-3">
      <ul class="nav nav-pills">
        <li class="nav-item">
          <a class="nav-link active" href="#" data-filter="all" onclick="filterStatus('all', this); return false;">
            <i class="fas fa-list"></i> Semua
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-filter="reminder_1_tahun" onclick="filterStatus('reminder_1_tahun', this); return false;">
            <i class="fas fa-bell"></i> Reminder 1 Tahun 
            <span class="badge bg-info text-white" id="badge1Tahun">0</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-filter="reminder_1_bulan" onclick="filterStatus('reminder_1_bulan', this); return false;">
            <i class="fas fa-bell"></i> Reminder 1 Bulan 
            <span class="badge bg-warning text-dark" id="badge1Bulan">0</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-filter="reminder_1_minggu" onclick="filterStatus('reminder_1_minggu', this); return false;">
            <i class="fas fa-bell"></i> Reminder 1 Minggu 
            <span class="badge bg-danger text-white" id="badge1Minggu">0</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-filter="duk" onclick="filterStatus('duk', this); return false;">
            <i class="fas fa-users"></i> DUK
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-filter="penyuluh" onclick="filterStatus('penyuluh', this); return false;">
            <i class="fas fa-chalkboard-teacher"></i> Penyuluh
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-filter="disetujui" onclick="filterStatus('disetujui', this); return false;">
            <i class="fas fa-check-circle"></i> Disetujui
          </a>
        </li>
      </ul>
    </div>

    <!-- Info box untuk filter reminder -->
    <div id="reminderInfo" class="alert alert-info mx-3" style="display: none;">
      <i class="fas fa-info-circle"></i>
      <strong id="reminderInfoText">Info filter</strong>
    </div>

    <div class="table-responsive">
      <table class="table table-hover" id="tablePensiun">
        <thead>
          <tr>
            <th width="50">No</th>
            <th>Nomor Usulan</th>
            <th>Pegawai</th>
            <th>Sumber</th>
            <th>Tanggal Pensiun</th>
            <th>Sisa Waktu</th>
            <th>Jenis Pensiun</th>
            <th>Status</th>
            <th>Reminder</th>
            <th width="200">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if ($result && $result->num_rows > 0):
            $count_1_tahun = 0;
            $count_1_bulan = 0;
            $count_1_minggu = 0;
            $count_disetujui = 0;
            $count_diajukan = 0;
            $count_draft = 0;
            $no = 1;
            
            while ($row = $result->fetch_assoc()):
              $statusColors = [
                'draft' => 'secondary',
                'diajukan' => 'info',
                'disetujui' => 'success',
                'ditolak' => 'danger'
              ];
              $statusColor = $statusColors[$row['status']] ?? 'secondary';
              
              // Hitung statistik
              switch($row['status']) {
                case 'disetujui': $count_disetujui++; break;
                case 'diajukan': $count_diajukan++; break;
                case 'draft': $count_draft++; break;
              }
              
              // Hitung hari tersisa
              $hari = (int)$row['hari_tersisa'];
              
              // Logika reminder (dengan toleransi ±15 hari)
              $perlu_reminder_1_tahun = ($hari >= 350 && $hari <= 380 && $row['status'] === 'disetujui');
              $perlu_reminder_1_bulan = ($hari >= 20 && $hari <= 40 && $row['status'] === 'disetujui');
              $perlu_reminder_1_minggu = ($hari >= 3 && $hari <= 10 && $row['status'] === 'disetujui');
              
              if ($perlu_reminder_1_tahun) $count_1_tahun++;
              if ($perlu_reminder_1_bulan) $count_1_bulan++;
              if ($perlu_reminder_1_minggu) $count_1_minggu++;
              
              // Format sisa waktu
              if ($hari < 0) {
                  $sisa_waktu = '<span class="badge bg-dark">Sudah Pensiun</span>';
              } else if ($hari == 0) {
                  $sisa_waktu = '<span class="badge bg-danger">Hari Ini!</span>';
              } else {
                  $tahun = floor($hari / 365);
                  $bulan = floor(($hari % 365) / 30);
                  $hari_sisa = $hari % 30;
                  
                  $parts = [];
                  if ($tahun > 0) $parts[] = $tahun . ' tahun';
                  if ($bulan > 0) $parts[] = $bulan . ' bulan';
                  if ($hari_sisa > 0) $parts[] = $hari_sisa . ' hari';
                  
                  $sisa_waktu = '<span class="badge bg-primary">' . implode(' ', $parts) . '</span>';
              }
              
              // Status Reminder
              // Status Reminder
              $reminderStatus = '';
              if ($row['status'] === 'disetujui') {
                  $badges = [];
                  
                  // Sudah lewat masa pensiun
                  if ($hari < 0) {
                      $reminderStatus = '<span class="badge bg-dark">Sudah Pensiun</span>';
                  } else {
                      // --- 1 Tahun ---
                      if ($row['reminder_1_tahun_terkirim'] > 0) {
                          $badges[] = '<span class="badge bg-success"><i class="fas fa-check"></i> 1 Th Terkirim</span>';
                      } elseif ($hari > 380) {
                          // Belum waktunya, tapi beri tanda "belum dijadwalkan"
                          $sisa_tahun = round($hari / 365, 1);
                          $badges[] = '<span class="badge bg-light text-secondary border" title="Reminder 1 tahun belum saatnya dikirim">
                                          <i class="fas fa-hourglass-start"></i> 1 Th (~' . $sisa_tahun . ' th lagi)
                                      </span>';
                      } elseif ($perlu_reminder_1_tahun) {
                          $badges[] = '<span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> 1 Th Belum Terkirim</span>';
                      }
                      
                      // --- 1 Bulan ---
                      if ($row['reminder_1_bulan_terkirim'] > 0) {
                          $badges[] = '<span class="badge bg-success"><i class="fas fa-check"></i> 1 Bl Terkirim</span>';
                      } elseif ($hari > 40 && $hari <= 380) {
                          // Sudah lewat window 1 tahun tapi belum masuk window 1 bulan
                          $sisa_bulan = round($hari / 30);
                          $badges[] = '<span class="badge bg-light text-secondary border" title="Reminder 1 bulan belum saatnya">
                                          <i class="fas fa-hourglass-start"></i> 1 Bl (~' . $sisa_bulan . ' bl lagi)
                                      </span>';
                      } elseif ($perlu_reminder_1_bulan) {
                          $badges[] = '<span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> 1 Bl Belum Terkirim</span>';
                      }
                      
                      // --- 1 Minggu ---
                      if ($row['reminder_1_minggu_terkirim'] > 0) {
                          $badges[] = '<span class="badge bg-success"><i class="fas fa-check"></i> 1 Mg Terkirim</span>';
                      } elseif ($hari > 10 && $hari <= 40) {
                          $badges[] = '<span class="badge bg-light text-secondary border" title="Reminder 1 minggu belum saatnya">
                                          <i class="fas fa-hourglass-start"></i> 1 Mg (~' . $hari . ' hari lagi)
                                      </span>';
                      } elseif ($perlu_reminder_1_minggu) {
                          $badges[] = '<span class="badge bg-danger text-white"><i class="fas fa-clock"></i> 1 Mg Belum Terkirim</span>';
                      }
                      
                      $reminderStatus = !empty($badges) 
                          ? implode('<br>', $badges) 
                          : '<span class="badge bg-secondary">Menunggu Approval</span>';
                  }
              } else {
                  // Belum disetujui
                  $reminderStatus = !empty($badges) 
                          ? '<div style="display:flex; flex-direction:column; gap:4px;">' . implode('', $badges) . '</div>'
                          : '<span class="badge bg-secondary">Menunggu Approval</span>';
              }
              
              // Data attributes untuk filter
              $data_reminder = '';
              if ($perlu_reminder_1_tahun) $data_reminder = 'reminder_1_tahun';
              else if ($perlu_reminder_1_bulan) $data_reminder = 'reminder_1_bulan';
              else if ($perlu_reminder_1_minggu) $data_reminder = 'reminder_1_minggu';
          ?>
            <tr data-status="<?= $row['status'] ?>" 
                data-sumber="<?= $row['sumber_data'] ?>"
                data-reminder="<?= $data_reminder ?>"
                data-nama="<?= strtolower(htmlspecialchars($row['nama'])) ?>"
                data-nip="<?= strtolower(htmlspecialchars($row['nip'])) ?>"
                data-nomor-usulan="<?= strtolower(htmlspecialchars($row['nomor_usulan'])) ?>">
                
              <td class="text-center"><strong><?= $no++ ?></strong></td>
              <td>
                <strong><?= htmlspecialchars($row['nomor_usulan']) ?></strong>
                <br><small class="text-muted"><?= date('d-m-Y', strtotime($row['tanggal_usulan'])) ?></small>
              </td>
              <td>
                <div>
                  <strong><?= htmlspecialchars($row['nama']) ?></strong>
                  <br><small><?= htmlspecialchars($row['nip']) ?></small>
                  <br><small class="text-muted"><?= htmlspecialchars($row['ttl']) ?></small>
                </div>
              </td>
              <td>
                <?php if ($row['sumber_data'] == 'duk'): ?>
                  <span class="badge bg-primary"><i class="fas fa-users"></i> DUK</span>
                <?php else: ?>
                  <span class="badge bg-info"><i class="fas fa-chalkboard-teacher"></i> Penyuluh</span>
                <?php endif; ?>
              </td>
              <td>
                <strong><?= date('d-m-Y', strtotime($row['tanggal_pensiun'])) ?></strong>
                <br><small class="text-muted">Umur: <?= floor($hari < 0 ? 0 : (60 - ($hari/365))) ?> tahun</small>
              </td>
              <td><?= $sisa_waktu ?></td>
              <td><span class="badge bg-secondary"><?= htmlspecialchars($row['jenis_pensiun']) ?></span></td>
              <td><span class="badge bg-<?= $statusColor ?>"><?= ucfirst($row['status']) ?></span></td>
              <td class="text-center"><?= $reminderStatus ?></td>
              <td>
                <div class="action-buttons">
                  <!-- Tombol Kirim Reminder -->
                  <?php if ($row['status'] === 'disetujui' && !empty($row['nomor_wa'])): ?>
                    <div class="mb-2">
                      <small class="d-block text-muted mb-1"><strong>Reminder:</strong></small>
                      
                      <?php if ($perlu_reminder_1_tahun): ?>
                        <?php if ($row['reminder_1_tahun_terkirim'] == 0): ?>
                          <button onclick="kirimReminder(<?= $row['id'] ?>, '1_tahun')" 
                                  class="btn btn-info btn-sm mb-1" style="width: 110px;">
                            <i class="fas fa-bell"></i> 1 Tahun
                          </button>
                        <?php else: ?>
                          <span class="badge bg-success d-block mb-1" style="width: 110px; padding: 6px;">
                            <i class="fas fa-check"></i> 1 Th Terkirim
                          </span>
                        <?php endif; ?>
                      <?php endif; ?>
                      
                      <?php if ($perlu_reminder_1_bulan): ?>
                        <?php if ($row['reminder_1_bulan_terkirim'] == 0): ?>
                          <button onclick="kirimReminder(<?= $row['id'] ?>, '1_bulan')" 
                                  class="btn btn-warning btn-sm mb-1" style="width: 110px;">
                            <i class="fas fa-bell"></i> 1 Bulan
                          </button>
                        <?php else: ?>
                          <span class="badge bg-success d-block mb-1" style="width: 110px; padding: 6px;">
                            <i class="fas fa-check"></i> 1 Bl Terkirim
                          </span>
                        <?php endif; ?>
                      <?php endif; ?>
                      
                      <?php if ($perlu_reminder_1_minggu): ?>
                        <?php if ($row['reminder_1_minggu_terkirim'] == 0): ?>
                          <button onclick="kirimReminder(<?= $row['id'] ?>, '1_minggu')" 
                                  class="btn btn-danger btn-sm mb-1" style="width: 110px;">
                            <i class="fas fa-bell"></i> 1 Minggu
                          </button>
                        <?php else: ?>
                          <span class="badge bg-success d-block mb-1" style="width: 110px; padding: 6px;">
                            <i class="fas fa-check"></i> 1 Mg Terkirim
                          </span>
                        <?php endif; ?>
                      <?php endif; ?>
                      
                    </div>
                  <?php endif; ?>
                  
                  <!-- Export Berkas -->
                  <div class="mb-2">
                    <small class="d-block text-muted mb-1"><strong>Export:</strong></small>
                    <a href="export_berkas_pensiun.php?id=<?= $row['id'] ?>" 
                       class="btn btn-primary btn-sm mb-1" style="width: 100px;" title="Export Semua Berkas" target="_blank">
                      <i class="fas fa-file-pdf"></i> Semua
                    </a>
                    
                    <?php if ($row['sumber_data'] === 'penyuluh'): ?>
                      <a href="export_berkas_pensiun.php?id=<?= $row['id'] ?>&jenis=pengantar" 
                         class="btn btn-secondary btn-sm mb-1" style="width: 100px;" title="Surat Pengantar PKB" target="_blank">
                        <i class="fas fa-file"></i> PKB
                      </a>
                    <?php else: ?>
                      <a href="export_berkas_pensiun.php?id=<?= $row['id'] ?>&jenis=pengantar" 
                         class="btn btn-secondary btn-sm mb-1" style="width: 100px;" title="Surat Pengantar" target="_blank">
                        <i class="fas fa-file"></i> Pengantar
                      </a>
                      <a href="export_berkas_pensiun.php?id=<?= $row['id'] ?>&jenis=pernyataan" 
                         class="btn btn-secondary btn-sm mb-1" style="width: 100px;" title="Surat Pernyataan" target="_blank">
                        <i class="fas fa-file"></i> Pernyataan
                      </a>
                    <?php endif; ?>
                  </div>
                  
                  <!-- Edit & Hapus -->
                  <?php if ($can_edit): ?>
                  <div>
                    <small class="d-block text-muted mb-1"><strong>Aksi:</strong></small>
                    <a href="form_edit_usulan_pensiun.php?id=<?= $row['id'] ?>" 
                       class="btn btn-warning btn-sm" style="width: 48px;" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <button onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nomor_usulan']) ?>')" 
                            class="btn btn-danger btn-sm" style="width: 48px;" title="Hapus">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php 
            endwhile;
            $total = $result->num_rows;
          else:
            $total = 0;
            $count_1_tahun = 0;
            $count_1_bulan = 0;
            $count_1_minggu = 0;
            $count_disetujui = 0;
            $count_diajukan = 0;
            $count_draft = 0;
          ?>
            <tr id="emptyRow">
              <td colspan="10" class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5>Belum ada usulan pensiun</h5>
                <?php if ($can_edit): ?>
                <a href="form_tambah_usulan_pensiun.php" class="btn btn-primary mt-2">
                  <i class="fas fa-plus me-2"></i>Buat Usulan
                </a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<script>
let currentFilter = 'all';

document.addEventListener('DOMContentLoaded', function() {
  updateBadges();
});

function confirmDelete(id, nomor) {
  if (confirm(`Hapus usulan "${nomor}"?`)) {
    window.location.href = `proses_hapus_usulan_pensiun.php?id=${id}`;
  }
}

function kirimReminder(id, jenis) {
  const jenisLabel = {
    '1_tahun': '1 Tahun',
    '1_bulan': '1 Bulan',
    '1_minggu': '1 Minggu'
  };
  
  Swal.fire({
    title: 'Kirim Reminder?',
    text: `Kirim reminder ${jenisLabel[jenis]} sebelum pensiun ke pegawai ini via WhatsApp?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#28a745',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="fas fa-paper-plane"></i> Ya, Kirim!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (!result.isConfirmed) return;

    Swal.fire({
      title: 'Mengirim...',
      text: 'Sedang mengirim reminder WhatsApp',
      icon: 'info',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => Swal.showLoading()
    });

    fetch('api/kirim_reminder_pensiun.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        id_usulan_pensiun: id,
        jenis_reminder: jenis
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Terkirim!',
          text: data.message,
          confirmButtonColor: '#28a745',
          timer: 3000,
          timerProgressBar: true
        }).then(() => {
          window.location.href = 'usulan_pensiun.php';
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: data.message,
          confirmButtonColor: '#dc3545'
        });
      }
    })
    .catch(error => {
      console.error('Error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: 'Terjadi kesalahan koneksi, coba lagi.',
        confirmButtonColor: '#dc3545'
      });
    });
  });
}

function filterTable() {
  const searchValue = document.getElementById('searchInput').value.toLowerCase().trim();
  const rows = document.querySelectorAll('#tablePensiun tbody tr:not(#emptyRow)');
  let visibleCount = 0;
  
  rows.forEach(row => {
    const nama = row.getAttribute('data-nama') || '';
    const nip = row.getAttribute('data-nip') || '';
    const nomorUsulan = row.getAttribute('data-nomor-usulan') || '';
    const rowStatus = row.getAttribute('data-status');
    const rowSumber = row.getAttribute('data-sumber');
    const rowReminder = row.getAttribute('data-reminder');
    
    const matchSearch = searchValue === '' || 
                       nama.includes(searchValue) || 
                       nip.includes(searchValue) || 
                       nomorUsulan.includes(searchValue);
    
    let matchFilter = false;
    if (currentFilter === 'all') {
      matchFilter = true;
    } else if (currentFilter === 'reminder_1_tahun') {
      matchFilter = (rowReminder === 'reminder_1_tahun');
    } else if (currentFilter === 'reminder_1_bulan') {
      matchFilter = (rowReminder === 'reminder_1_bulan');
    } else if (currentFilter === 'reminder_1_minggu') {
      matchFilter = (rowReminder === 'reminder_1_minggu');
    } else if (currentFilter === 'duk' || currentFilter === 'penyuluh') {
      matchFilter = (rowSumber === currentFilter);
    } else {
      matchFilter = (rowStatus === currentFilter);
    }
    
    if (matchSearch && matchFilter) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });
  
  const emptyRow = document.getElementById('emptyRow');
  if (emptyRow) {
    emptyRow.style.display = visibleCount === 0 ? '' : 'none';
  }
}

function filterStatus(filter, element) {
  currentFilter = filter;
  
  document.querySelectorAll('.nav-pills .nav-link').forEach(link => {
    link.classList.remove('active');
  });
  element.classList.add('active');
  
  const infoBox = document.getElementById('reminderInfo');
  const infoText = document.getElementById('reminderInfoText');
  
  if (filter === 'reminder_1_tahun') {
    infoBox.style.display = 'block';
    infoText.textContent = 'Menampilkan ASN yang akan pensiun sekitar 1 tahun lagi (±15 hari dari 365 hari)';
  } else if (filter === 'reminder_1_bulan') {
    infoBox.style.display = 'block';
    infoText.textContent = 'Menampilkan ASN yang akan pensiun sekitar 1 bulan lagi (±15 hari dari 30 hari)';
  } else if (filter === 'reminder_1_minggu') {
    infoBox.style.display = 'block';
    infoText.textContent = 'Menampilkan ASN yang akan pensiun sekitar 1 minggu lagi (3-10 hari)';
  } else {
    infoBox.style.display = 'none';
  }
  
  filterTable();
}

function resetSearch() {
  document.getElementById('searchInput').value = '';
  filterTable();
}

function updateBadges() {
  const rows = document.querySelectorAll('#tablePensiun tbody tr:not(#emptyRow)');
  
  let reminder1Tahun = 0;
  let reminder1Bulan = 0;
  let reminder1Minggu = 0;
  
  rows.forEach(row => {
    const reminder = row.getAttribute('data-reminder');
    if (reminder === 'reminder_1_tahun') reminder1Tahun++;
    if (reminder === 'reminder_1_bulan') reminder1Bulan++;
    if (reminder === 'reminder_1_minggu') reminder1Minggu++;
  });

  // Hanya update elemen yang benar-benar ada di HTML
  const b1t = document.getElementById('badge1Tahun');
  const b1b = document.getElementById('badge1Bulan');
  const b1m = document.getElementById('badge1Minggu');
  
  if (b1t) b1t.textContent = reminder1Tahun;
  if (b1b) b1b.textContent = reminder1Bulan;
  if (b1m) b1m.textContent = reminder1Minggu;
}

function showAlertWA(type, message) {
  const alertClass = type === 'success' ? 'alert-success' : 
                     type === 'danger' ? 'alert-danger' : 'alert-info';
  
  const html = `
    <div class="alert ${alertClass} alert-dismissible fade show">
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  `;
  
  document.getElementById('alertNotifWA').innerHTML = html;
  
  setTimeout(() => {
    const alert = document.querySelector('#alertNotifWA .alert');
    if (alert) {
      alert.classList.remove('show');
      setTimeout(() => alert.remove(), 150);
    }
  }, 5000);
}
</script>

<?php require_once 'includes/footer.php'; ?>