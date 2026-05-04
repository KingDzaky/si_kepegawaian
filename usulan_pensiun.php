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
              $reminderStatus = '';
              if ($row['status'] === 'disetujui') {
                  $badges = [];
                  
                  if ($row['reminder_1_tahun_terkirim'] > 0) {
                      $badges[] = '<span class="badge bg-info" title="Reminder 1 Tahun Terkirim"><i class="fas fa-check"></i> 1 Th</span>';
                  } else if ($perlu_reminder_1_tahun) {
                      $badges[] = '<span class="badge bg-warning text-dark" title="Perlu Kirim Reminder 1 Tahun"><i class="fas fa-exclamation"></i> 1 Th</span>';
                  }
                  
                  if ($row['reminder_1_bulan_terkirim'] > 0) {
                      $badges[] = '<span class="badge bg-info" title="Reminder 1 Bulan Terkirim"><i class="fas fa-check"></i> 1 Bl</span>';
                  } else if ($perlu_reminder_1_bulan) {
                      $badges[] = '<span class="badge bg-warning text-dark" title="Perlu Kirim Reminder 1 Bulan"><i class="fas fa-exclamation"></i> 1 Bl</span>';
                  }
                  
                  if ($row['reminder_1_minggu_terkirim'] > 0) {
                      $badges[] = '<span class="badge bg-info" title="Reminder 1 Minggu Terkirim"><i class="fas fa-check"></i> 1 Mg</span>';
                  } else if ($perlu_reminder_1_minggu) {
                      $badges[] = '<span class="badge bg-danger text-white" title="Perlu Kirim Reminder 1 Minggu"><i class="fas fa-exclamation"></i> 1 Mg</span>';
                  }
                  
                  $reminderStatus = !empty($badges) ? implode('<br>', $badges) : '<span class="badge bg-secondary">-</span>';
              } else {
                  $reminderStatus = '<span class="badge bg-secondary">-</span>';
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
                      <?php if ($perlu_reminder_1_tahun && $row['reminder_1_tahun_terkirim'] == 0): ?>
                        <button onclick="kirimReminder(<?= $row['id'] ?>, '1_tahun')" 
                                class="btn btn-info btn-sm mb-1" style="width: 100px;" title="Kirim Reminder 1 Tahun">
                          <i class="fas fa-bell"></i> 1 Tahun
                        </button>
                      <?php endif; ?>
                      
                      <?php if ($perlu_reminder_1_bulan && $row['reminder_1_bulan_terkirim'] == 0): ?>
                        <button onclick="kirimReminder(<?= $row['id'] ?>, '1_bulan')" 
                                class="btn btn-warning btn-sm mb-1" style="width: 100px;" title="Kirim Reminder 1 Bulan">
                          <i class="fas fa-bell"></i> 1 Bulan
                        </button>
                      <?php endif; ?>
                      
                      <?php if ($perlu_reminder_1_minggu && $row['reminder_1_minggu_terkirim'] == 0): ?>
                        <button onclick="kirimReminder(<?= $row['id'] ?>, '1_minggu')" 
                                class="btn btn-danger btn-sm mb-1" style="width: 100px;" title="Kirim Reminder 1 Minggu">
                          <i class="fas fa-bell"></i> 1 Minggu
                        </button>
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
// Store current filter
let currentFilter = 'all';

// Update badge saat halaman load
document.addEventListener('DOMContentLoaded', function() {
  updateBadges();
});

function confirmDelete(id, nomor) {
  if (confirm(`Hapus usulan "${nomor}"?`)) {
    window.location.href = `proses_hapus_usulan_pensiun.php?id=${id}`;
  }
}

// Fungsi kirim reminder
function kirimReminder(id, jenis) {
  const jenisLabel = {
    '1_tahun': '1 Tahun',
    '1_bulan': '1 Bulan',
    '1_minggu': '1 Minggu'
  };
  
  if (!confirm(`Kirim reminder ${jenisLabel[jenis]} sebelum pensiun ke pegawai ini?`)) {
    return;
  }
  
  showAlertWA('info', '<i class="fas fa-spinner fa-spin"></i> Mengirim reminder WhatsApp...');
  
  fetch('api/kirim_reminder_pensiun.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ 
      id_usulan_pensiun: id,
      jenis_reminder: jenis
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showAlertWA('success', '<i class="fas fa-check-circle"></i> ' + data.message);
      setTimeout(() => location.reload(), 2000);
    } else {
      showAlertWA('danger', '<i class="fas fa-times-circle"></i> ' + data.message);
    }
  })
  .catch(error => {
    showAlertWA('danger', '<i class="fas fa-times-circle"></i> Terjadi kesalahan saat mengirim reminder');
    console.error('Error:', error);
  });
}

// Fungsi filter tabel (gabungan pencarian + filter tab)
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
    
    // Cek pencarian
    const matchSearch = searchValue === '' || 
                       nama.includes(searchValue) || 
                       nip.includes(searchValue) || 
                       nomorUsulan.includes(searchValue);
    
    // Cek filter status/tab
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
    
    // Tampilkan jika semua kondisi terpenuhi
    if (matchSearch && matchFilter) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });
  
  // Update badge tampil
  document.getElementById('badgeTampil').textContent = 'Ditampilkan: ' + visibleCount;
  
  // Show/hide empty row
  const emptyRow = document.getElementById('emptyRow');
  if (emptyRow) {
    emptyRow.style.display = visibleCount === 0 && rows.length > 0 ? '' : 'none';
  }
}

// Fungsi filter status (dari tab)
function filterStatus(filter, element) {
  currentFilter = filter;
  
  // Update active nav
  document.querySelectorAll('.nav-pills .nav-link').forEach(link => {
    link.classList.remove('active');
  });
  element.classList.add('active');
  
  // Show/hide info box
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
  
  // Trigger filter dengan pencarian yang ada
  filterTable();
}

// Fungsi reset pencarian
function resetSearch() {
  document.getElementById('searchInput').value = '';
  filterTable();
}

// Fungsi update badges
function updateBadges() {
  const rows = document.querySelectorAll('#tablePensiun tbody tr:not(#emptyRow)');
  
  let total = rows.length;
  let disetujui = 0;
  let diajukan = 0;
  let draft = 0;
  let reminder1Tahun = 0;
  let reminder1Bulan = 0;
  let reminder1Minggu = 0;
  
  rows.forEach(row => {
    const status = row.getAttribute('data-status');
    const reminder = row.getAttribute('data-reminder');
    
    if (status === 'disetujui') disetujui++;
    if (status === 'diajukan') diajukan++;
    if (status === 'draft') draft++;
    if (reminder === 'reminder_1_tahun') reminder1Tahun++;
    if (reminder === 'reminder_1_bulan') reminder1Bulan++;
    if (reminder === 'reminder_1_minggu') reminder1Minggu++;
  });
  
  document.getElementById('badgeTotal').textContent = 'Total: ' + total;
  document.getElementById('badgeDisetujui').textContent = 'Disetujui: ' + disetujui;
  document.getElementById('badgeDiajukan').textContent = 'Diajukan: ' + diajukan;
  document.getElementById('badgeDraft').textContent = 'Draft: ' + draft;
  document.getElementById('badgeReminder1Tahun').textContent = 'Reminder 1 Tahun: ' + reminder1Tahun;
  document.getElementById('badgeReminder1Minggu').textContent = 'Reminder 1 Minggu: ' + reminder1Minggu;
  document.getElementById('badgeTampil').textContent = 'Ditampilkan: ' + total;
  
  // Update badge di tab juga
  document.getElementById('badge1Tahun').textContent = reminder1Tahun;
  document.getElementById('badge1Bulan').textContent = reminder1Bulan;
  document.getElementById('badge1Minggu').textContent = reminder1Minggu;
}

// Helper functions
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


if (!response.success) {
    if (response.type === 'warning') {
        showNotification('warning', response.message); // tampil kuning
    } else {
        showNotification('error', response.message);   // tampil merah
    }
}

</script>

<?php require_once 'includes/footer.php'; ?>