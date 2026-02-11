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

// Query dengan JOIN ke notifikasi_wa DAN hitung hari tersisa
$query = "SELECT 
            kp.*,
            nw.id as notif_id,
            nw.status as status_notif,
            nw.tanggal_kirim,
            d.nomor_wa,
            DATEDIFF(kp.tmt_pangkat_baru, CURDATE()) as hari_tersisa,
            (SELECT COUNT(*) FROM notifikasi_wa 
             WHERE id_kenaikan_pangkat = kp.id 
             AND status = 'terkirim' 
             AND pesan LIKE '%PENGINGAT KENAIKAN PANGKAT%') as reminder_terkirim
          FROM kenaikan_pangkat kp
          LEFT JOIN notifikasi_wa nw ON kp.id = nw.id_kenaikan_pangkat 
                AND nw.status = 'terkirim' 
                AND nw.pesan LIKE '%NOTIFIKASI KENAIKAN PANGKAT%'
          LEFT JOIN duk d ON kp.nip = d.nip
          ORDER BY kp.created_at DESC";
$result = $koneksi->query($query);
?>

<link rel="stylesheet" href="css/dataduk.css">

<main class="main-content">
  <div class="dashboard-header fade-in">
    <h1 class="dashboard-title">
      <i class="fas fa-file-signature me-2"></i>
      Usulan Kenaikan Pangkat
    </h1>
    <p class="dashboard-subtitle">Manajemen Usulan Kenaikan Pangkat Pegawai</p>
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
        <i class="fas fa-table me-2"></i>Daftar Usulan
      </h5>
      <?php if ($can_edit): ?>
      <a href="form_tambah_kenaikan_pangkat.php" class="btn btn-success btn-sm">
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
             placeholder="Ketik nama pegawai, NIP, atau nomor usulan..." 
             onkeyup="filterTable()">
    </div>
    <div class="col-md-6 d-flex align-items-end">
      <button class="btn btn-secondary" onclick="resetSearch()">
        <i class="fas fa-redo"></i> Reset Pencarian
      </button>
    </div>
  </div>
</div>

    <!-- TAMBAHAN: Filter Tab -->
    <div class="mt-3 mb-3 ms-3">
      <ul class="nav nav-pills">
        <li class="nav-item">
          <a class="nav-link active" href="#" data-filter="all" onclick="filterStatus('all', this); return false;">
            <i class="fas fa-list"></i> Semua
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-filter="perlu_reminder" onclick="filterStatus('perlu_reminder', this); return false;">
            <i class="fas fa-bell"></i> Perlu Reminder 
            <span class="badge bg-warning text-dark" id="badgeReminder">0</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-filter="disetujui" onclick="filterStatus('disetujui', this); return false;">
            <i class="fas fa-check-circle"></i> Disetujui
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-filter="diajukan" onclick="filterStatus('diajukan', this); return false;">
            <i class="fas fa-clock"></i> Diajukan
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-filter="draft" onclick="filterStatus('draft', this); return false;">
            <i class="fas fa-file"></i> Draft
          </a>
        </li>
      </ul>
    </div>

    <!-- Info box untuk filter reminder -->
    <div id="reminderInfo" class="alert alert-info" style="display: none;">
      <i class="fas fa-info-circle"></i>
      <strong>Info:</strong> Menampilkan ASN yang akan naik pangkat sekitar 1 tahun lagi (±30 hari). 
      Kirim reminder untuk mengingatkan mereka mempersiapkan berkas.
    </div>

    <div class="table-responsive">
      <table class="table table-hover" id="tableKenaikanPangkat">
        <thead>
          <tr>
            <th width="50">No</th>
            <th>No. Usulan</th>
            <th>Pegawai</th>
            <th>Pangkat Lama</th>
            <th>Pangkat Baru</th>
            <th>Jenis</th>
            <th>Status</th>
            <th>Notifikasi WA</th>
            <th>Reminder</th>
            <th width="200">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if ($result && $result->num_rows > 0):
            $count_reminder = 0;
            $no = 1;
            while ($row = $result->fetch_assoc()):
              $statusColors = [
                'draft' => 'secondary',
                'diajukan' => 'info',
                'disetujui' => 'success',
                'ditolak' => 'danger'
              ];
              $statusColor = $statusColors[$row['status']] ?? 'secondary';
              
              // Hitung status reminder
              $hari = (int)$row['hari_tersisa'];
              $perlu_reminder = ($hari >= 335 && $hari <= 395 && $row['status'] === 'disetujui');
              if ($perlu_reminder) $count_reminder++;
              
              // Status notifikasi WA (approval)
              $notifWA = '';
              if ($row['status_notif'] === 'terkirim') {
                  $tanggal_kirim = date('d/m/Y H:i', strtotime($row['tanggal_kirim']));
                  $notifWA = '
                    <div class="text-center">
                      <span class="badge bg-success mb-1">
                        <i class="fas fa-check-circle"></i> Terkirim
                      </span>
                      <br><small class="text-muted">' . $tanggal_kirim . '</small>
                      <br>
                      <button class="btn btn-info btn-sm mt-1" onclick="lihatDetailNotif(' . $row['id'] . ')">
                        <i class="fas fa-eye"></i> Detail
                      </button>
                    </div>
                  ';
              } else if (!empty($row['nomor_wa'])) {
                  if ($row['status'] === 'disetujui' || $row['status'] === 'ditolak') {
                      $notifWA = '
                        <button class="btn btn-primary btn-sm" onclick="kirimNotifWA(' . $row['id'] . ')">
                          <i class="fab fa-whatsapp"></i> Kirim Notifikasi
                        </button>
                      ';
                  } else {
                      $notifWA = '<small class="text-muted">Menunggu persetujuan</small>';
                  }
              } else {
                  $notifWA = '
                    <span class="badge bg-warning text-dark">
                      <i class="fas fa-exclamation-triangle"></i> No. WA belum ada
                    </span>
                    <br>
                    <a href="dataduk.php" class="btn btn-sm btn-outline-primary mt-1" style="font-size: 11px;">
                      <i class="fas fa-edit"></i> Isi Nomor
                    </a>
                  ';
              }
              
              // TAMBAHAN: Status Reminder
              $reminderStatus = '';
              $reminderBtn = '';
              if ($row['status'] === 'disetujui') {
                  if ($row['reminder_terkirim'] > 0) {
                      $reminderStatus = '<span class="badge bg-success"><i class="fas fa-check"></i> Terkirim</span>';
                  } else if ($perlu_reminder) {
                      $reminderStatus = '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation"></i> Perlu Kirim</span>';
                  } else {
                      $reminderStatus = '<span class="badge bg-secondary">-</span>';
                  }
                  
                  // Tombol kirim reminder
                  if (!empty($row['nomor_wa'])) {
                      $reminderBtn = '<br><button class="btn btn-warning btn-sm mt-1" onclick="kirimReminder(' . $row['id'] . ')" title="Kirim Reminder Persiapan Berkas">
                        <i class="fas fa-bell"></i> Kirim Reminder
                      </button>';
                  } else {
                      $reminderBtn = '<br><button class="btn btn-secondary btn-sm mt-1" disabled title="Nomor WA tidak ada">
                        <i class="fas fa-bell-slash"></i> No WA
                      </button>';
                  }
              } else {
                  $reminderStatus = '<span class="badge bg-secondary">-</span>';
              }
          ?>
            <tr data-status="<?= $row['status'] ?>" data-reminder="<?= $perlu_reminder ? '1' : '0' ?>">
              <td class="text-center"><strong><?= $no++ ?></strong></td>
              <td>
                <strong><?= htmlspecialchars($row['nomor_usulan']) ?></strong>
                <br><small class="text-muted"><?= date('d-m-Y', strtotime($row['tanggal_usulan'])) ?></small>
              </td>
              <td>
                <div>
                  <strong><?= htmlspecialchars($row['nama']) ?></strong>
                  <br><small><?= htmlspecialchars($row['nip']) ?></small>
                </div>
              </td>
              <td>
                <strong><?= htmlspecialchars($row['pangkat_lama']) ?></strong>
                <br><span class="badge badge-primary"><?= htmlspecialchars($row['golongan_lama']) ?></span>
              </td>
              <td>
                <strong><?= htmlspecialchars($row['pangkat_baru']) ?></strong>
                <br><span class="badge badge-primary"><?= htmlspecialchars($row['golongan_baru']) ?></span>
              </td>
              <td><span class="badge bg-info"><?= htmlspecialchars($row['jenis_kenaikan']) ?></span></td>
              <td><span class="badge bg-<?= $statusColor ?>"><?= ucfirst($row['status']) ?></span></td>
              <td><?= $notifWA ?></td>
              <td class="text-center">
                <?= $reminderStatus ?>
                <?= $reminderBtn ?>
              </td>
              <td>
                <div class="action-buttons">
                  <a href="export_kenaikan_pangkat_pdf.php?id=<?= $row['id'] ?>" 
                     class="btn btn-info btn-sm" title="Export PDF" target="_blank">
                    <i class="fas fa-file-pdf"></i>
                  </a>
                  <?php if ($can_edit): ?>
                  <a href="form_edit_kenaikan_pangkat.php?id=<?= $row['id'] ?>" 
                     class="btn btn-warning btn-sm" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                  <button onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nomor_usulan']) ?>')" 
                          class="btn btn-danger btn-sm" title="Hapus">
                    <i class="fas fa-trash"></i>
                  </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php 
            endwhile;
            // Update badge count
            echo "<script>document.getElementById('badgeReminder').textContent = '{$count_reminder}';</script>";
          else:
          ?>
            <tr>
              <td colspan="10" class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5>Belum ada usulan kenaikan pangkat</h5>
                <?php if ($can_edit): ?>
                <a href="form_tambah_kenaikan_pangkat.php" class="btn btn-primary mt-2">
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

<!-- Modal Detail Notifikasi -->
<div class="modal fade" id="modalDetailNotif" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">
          <i class="fas fa-info-circle"></i> Detail Notifikasi WhatsApp
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detailNotifBody">
        <div class="text-center">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
function confirmDelete(id, nomor) {
  if (confirm(`Hapus usulan "${nomor}"?`)) {
    window.location.href = `proses_hapus_kenaikan_pangkat.php?id=${id}`;
  }
}

// Fungsi kirim notifikasi WhatsApp (approval)
function kirimNotifWA(id) {
  if (!confirm('Yakin ingin mengirim notifikasi WhatsApp ke pegawai ini?')) {
    return;
  }
  
  showAlertWA('info', '<i class="fas fa-spinner fa-spin"></i> Mengirim notifikasi WhatsApp...');
  
  fetch('api/kirim_notifikasi.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'id_kenaikan_pangkat=' + id
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
    showAlertWA('danger', '<i class="fas fa-times-circle"></i> Terjadi kesalahan saat mengirim notifikasi');
    console.error('Error:', error);
  });
}

// TAMBAHAN: Fungsi kirim reminder
function kirimReminder(id) {
  if (!confirm('Kirim reminder persiapan berkas kenaikan pangkat ke pegawai ini?')) {
    return;
  }
  
  showAlertWA('info', '<i class="fas fa-spinner fa-spin"></i> Mengirim reminder WhatsApp...');
  
  fetch('api/kirim_reminder.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ id_kenaikan_pangkat: id })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      let msg = '<i class="fas fa-check-circle"></i> ' + data.message;
      if (data.data && data.data.sudah_pernah_terkirim) {
        msg += '<br><small class="text-muted">Catatan: Reminder sudah pernah dikirim sebelumnya</small>';
      }
      showAlertWA('success', msg);
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

// Fungsi lihat detail notifikasi
function lihatDetailNotif(id) {
  const modal = new bootstrap.Modal(document.getElementById('modalDetailNotif'));
  modal.show();
  
  document.getElementById('detailNotifBody').innerHTML = `
    <div class="text-center">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
  `;
  
  fetch('api/get_detail_notifikasi.php?id=' + id)
    .then(response => {
      if (!response.ok) {
        throw new Error('HTTP error! status: ' + response.status);
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        const d = data.data;
        const html = `
          <div class="row">
            <div class="col-md-6">
              <table class="table table-sm table-borderless">
                <tr>
                  <td width="40%"><strong>Nama</strong></td>
                  <td>: ${d.nama}</td>
                </tr>
                <tr>
                  <td><strong>NIP</strong></td>
                  <td>: ${d.nip}</td>
                </tr>
                <tr>
                  <td><strong>Nomor WA</strong></td>
                  <td>: ${d.nomor_wa}</td>
                </tr>
                <tr>
                  <td><strong>Status</strong></td>
                  <td>: <span class="badge bg-success">${d.status}</span></td>
                </tr>
              </table>
            </div>
            <div class="col-md-6">
              <table class="table table-sm table-borderless">
                <tr>
                  <td width="40%"><strong>Tanggal Kirim</strong></td>
                  <td>: ${formatDateTime(d.tanggal_kirim)}</td>
                </tr>
                <tr>
                  <td><strong>Keterangan</strong></td>
                  <td>: ${d.keterangan || '-'}</td>
                </tr>
              </table>
            </div>
          </div>
          <hr>
          <h6><i class="fas fa-comment-dots"></i> Isi Pesan:</h6>
          <div class="alert alert-light border">
            <pre style="white-space: pre-wrap; font-family: inherit; margin: 0;">${escapeHtml(d.pesan)}</pre>
          </div>
        `;
        document.getElementById('detailNotifBody').innerHTML = html;
      } else {
        document.getElementById('detailNotifBody').innerHTML = `
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> ${data.message || 'Gagal memuat detail notifikasi'}
          </div>
        `;
      }
    })
    .catch(error => {
      console.error('Error:', error);
      document.getElementById('detailNotifBody').innerHTML = `
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-circle"></i> Terjadi kesalahan: ${error.message}
        </div>
      `;
    });
}

// TAMBAHAN: Fungsi filter status
function filterStatus(filter, element) {
  // Update active nav
  document.querySelectorAll('.nav-pills .nav-link').forEach(link => {
    link.classList.remove('active');
  });
  element.classList.add('active');
  
  // Show/hide info box
  const infoBox = document.getElementById('reminderInfo');
  if (filter === 'perlu_reminder') {
    infoBox.style.display = 'block';
  } else {
    infoBox.style.display = 'none';
  }
  
  // Filter table rows
  const rows = document.querySelectorAll('#tableKenaikanPangkat tbody tr');
  rows.forEach(row => {
    if (filter === 'all') {
      row.style.display = '';
    } else if (filter === 'perlu_reminder') {
      // Show only rows with data-reminder="1"
      if (row.getAttribute('data-reminder') === '1') {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    } else {
      // Filter by status
      if (row.getAttribute('data-status') === filter) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    }
  });
}

// Variable untuk menyimpan filter status yang aktif
let currentStatusFilter = 'all';

// Fungsi filter tabel berdasarkan pencarian
function filterTable() {
  const input = document.getElementById('searchInput');
  const filter = input.value.toLowerCase();
  const table = document.getElementById('tableKenaikanPangkat');
  const rows = table.getElementsByTagName('tr');

  for (let i = 1; i < rows.length; i++) {
    const row = rows[i];
    const cells = row.getElementsByTagName('td');
    
    if (cells.length > 0) {
      // Cek kolom nomor usulan (index 1), nama (index 2), dan NIP (dalam cell 2)
      const nomorUsulan = cells[1]?.textContent || '';
      const namaNip = cells[2]?.textContent || '';
      
      const matchSearch = nomorUsulan.toLowerCase().includes(filter) || 
                         namaNip.toLowerCase().includes(filter);
      
      // Terapkan filter pencarian DAN filter status
      const status = row.getAttribute('data-status');
      const isReminder = row.getAttribute('data-reminder') === '1';
      
      let matchStatus = false;
      if (currentStatusFilter === 'all') {
        matchStatus = true;
      } else if (currentStatusFilter === 'perlu_reminder') {
        matchStatus = isReminder;
      } else {
        matchStatus = status === currentStatusFilter;
      }
      
      // Tampilkan row jika match pencarian DAN match status
      if (matchSearch && matchStatus) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    }
  }
}

// Fungsi reset pencarian
function resetSearch() {
  document.getElementById('searchInput').value = '';
  filterTable();
}

// Fungsi filter status (diperbarui)
function filterStatus(filter, element) {
  // Simpan filter status yang aktif
  currentStatusFilter = filter;
  
  // Update active nav
  document.querySelectorAll('.nav-pills .nav-link').forEach(link => {
    link.classList.remove('active');
  });
  element.classList.add('active');
  
  // Show/hide info box
  const infoBox = document.getElementById('reminderInfo');
  if (filter === 'perlu_reminder') {
    infoBox.style.display = 'block';
  } else {
    infoBox.style.display = 'none';
  }
  
  // Terapkan filter ulang (akan menggunakan currentStatusFilter)
  filterTable();
}


// Helper functions
function escapeHtml(text) {
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, m => map[m]);
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

function formatDateTime(dateString) {
  if (!dateString) return '-';
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  return `${day}/${month}/${year} ${hours}:${minutes}`;
}
</script>

<?php require_once 'includes/footer.php'; ?>