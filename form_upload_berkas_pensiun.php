<?php
// form_upload_berkas_pensiun.php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/helper_berkas.php';

if (!isAdmin()) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: usulan_pensiun.php');
    exit;
}

// Ambil data usulan
$stmt = $koneksi->prepare("SELECT * FROM usulan_pensiun WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$up = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$up) {
    header('Location: usulan_pensiun.php?error=Data tidak ditemukan');
    exit;
}

if ($up['status'] !== 'disetujui') {
    header('Location: usulan_pensiun.php?error=Usulan belum disetujui, upload berkas belum bisa dilakukan');
    exit;
}

// Jenis berkas wajib BEDA tergantung sumber_data
// (HARUS SAMA dengan logika di proses_upload_berkas_pensiun.php dan
// constanta jumlah wajib di usulan_pensiun.php)
if ($up['sumber_data'] === 'penyuluh') {
    $jenis_wajib = ['Surat Pengantar PKB'];
} else {
    $jenis_wajib = ['Surat Pengantar', 'Surat Pernyataan'];
}

// Ambil berkas wajib yang sudah diupload
$stmt2 = $koneksi->prepare("SELECT * FROM berkas_pensiun WHERE id_usulan_pensiun = ? AND jenis_berkas IN (" . implode(',', array_fill(0, count($jenis_wajib), '?')) . ")");
$types = 'i' . str_repeat('s', count($jenis_wajib));
$params = array_merge([$id], $jenis_wajib);
$stmt2->bind_param($types, ...$params);
$stmt2->execute();
$result_berkas = $stmt2->get_result();

$berkas_terupload = [];
while ($row = $result_berkas->fetch_assoc()) {
    $berkas_terupload[$row['jenis_berkas']] = $row;
}
$stmt2->close();

// Ambil dokumen tambahan (di luar jenis wajib)
$stmt3 = $koneksi->prepare("SELECT * FROM berkas_pensiun WHERE id_usulan_pensiun = ? AND jenis_berkas NOT IN (" . implode(',', array_fill(0, count($jenis_wajib), '?')) . ") ORDER BY tanggal_upload DESC");
$stmt3->bind_param($types, ...$params);
$stmt3->execute();
$dokumen_tambahan = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt3->close();

$jumlah_lengkap = count(array_intersect($jenis_wajib, array_keys($berkas_terupload)));
$total_wajib    = count($jenis_wajib);

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<link rel="stylesheet" href="css/dataduk.css">

<main class="main-content">
  <div class="dashboard-header fade-in d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h1 class="dashboard-title">
        <i class="fas fa-file-upload me-2"></i>
        Upload Berkas Usulan Pensiun
      </h1>
      <p class="dashboard-subtitle">
        <?= htmlspecialchars($up['nama']) ?> — <?= htmlspecialchars($up['nomor_usulan']) ?>
        <?php if ($up['sumber_data'] === 'penyuluh'): ?>
          <span class="badge bg-info ms-2"><i class="fas fa-chalkboard-teacher"></i> Penyuluh</span>
        <?php else: ?>
          <span class="badge bg-primary ms-2"><i class="fas fa-users"></i> DUK</span>
        <?php endif; ?>
      </p>
    </div>
    <?php if (!empty($berkas_terupload) || !empty($dokumen_tambahan)): ?>
      <a href="download_folder_berkas.php?id=<?= $id ?>&jenis=pensiun" class="btn btn-outline-dark">
        <i class="fas fa-file-archive"></i> Download Semua (ZIP)
      </a>
    <?php endif; ?>
  </div>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['success']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_GET['error']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="table-section fade-in">
    <div class="table-header">
      <h5 class="table-title">
        <i class="fas fa-tasks me-2"></i>Progress Kelengkapan Berkas
      </h5>
      <?php if ($jumlah_lengkap === $total_wajib): ?>
        <span class="badge bg-success" style="font-size:14px;padding:8px 14px;">
          <i class="fas fa-check-circle"></i> <?= $jumlah_lengkap ?>/<?= $total_wajib ?> Lengkap
        </span>
      <?php else: ?>
        <span class="badge bg-warning text-dark" style="font-size:14px;padding:8px 14px;">
          <i class="fas fa-clock"></i> <?= $jumlah_lengkap ?>/<?= $total_wajib ?> Berkas Lengkap
        </span>
      <?php endif; ?>
    </div>

    <!-- SATU FORM untuk semua berkas wajib + dokumen tambahan, submit sekali -->
    <form action="proses_upload_berkas_pensiun.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id_usulan_pensiun" value="<?= $id ?>">

      <div class="p-4">
        <?php foreach ($jenis_wajib as $jenis): ?>
          <?php $sudah_ada = isset($berkas_terupload[$jenis]); ?>
          <div class="card mb-3" style="border-left: 4px solid <?= $sudah_ada ? '#28a745' : '#ffc107' ?>;">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
              <div>
                <h6 class="mb-1">
                  <?php if ($sudah_ada): ?>
                    <i class="fas fa-check-circle text-success me-2"></i>
                  <?php else: ?>
                    <i class="fas fa-circle text-warning me-2"></i>
                  <?php endif; ?>
                  <?= htmlspecialchars($jenis) ?>
                </h6>
                <?php if ($sudah_ada): ?>
                  <small class="text-muted">
                    <?= htmlspecialchars($berkas_terupload[$jenis]['nama_file']) ?>
                    &mdash; diupload <?= date('d/m/Y H:i', strtotime($berkas_terupload[$jenis]['tanggal_upload'])) ?>
                  </small>
                <?php else: ?>
                  <small class="text-muted">Belum diupload</small>
                <?php endif; ?>
              </div>

              <div class="d-flex gap-2 align-items-center">
                <?php if ($sudah_ada): ?>
                  <button type="button" class="btn btn-outline-info btn-sm"
                          onclick="lihatBerkasModal(<?= $berkas_terupload[$jenis]['id'] ?>, 'pensiun', '<?= htmlspecialchars(addslashes($jenis)) ?>')">
                    <i class="fas fa-eye"></i> Lihat
                  </button>
                <?php endif; ?>

                <input type="file" name="berkas[<?= htmlspecialchars($jenis) ?>]"
                       accept=".pdf,.jpg,.jpeg,.png" class="form-control form-control-sm" style="max-width:220px;">
              </div>
            </div>
          </div>
        <?php endforeach; ?>

        <!-- Dokumen Tambahan (opsional, di luar jenis wajib) -->
        <div class="card mb-3 border-secondary">
          <div class="card-body">
            <h6 class="mb-3"><i class="fas fa-paperclip me-2"></i>Dokumen Tambahan (opsional)</h6>

            <?php if (!empty($dokumen_tambahan)): ?>
              <div class="mb-3">
                <?php foreach ($dokumen_tambahan as $dok): ?>
                  <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                    <div>
                      <strong><?= htmlspecialchars($dok['jenis_berkas']) ?></strong>
                      <br><small class="text-muted"><?= htmlspecialchars($dok['nama_file']) ?> &mdash; <?= date('d/m/Y H:i', strtotime($dok['tanggal_upload'])) ?></small>
                    </div>
                    <button type="button" class="btn btn-outline-info btn-sm"
                            onclick="lihatBerkasModal(<?= $dok['id'] ?>, 'pensiun', '<?= htmlspecialchars(addslashes($dok['jenis_berkas'])) ?>')">
                      <i class="fas fa-eye"></i> Lihat
                    </button>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div id="tambahanWrapper"></div>

            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="tambahDokumen()">
              <i class="fas fa-plus"></i> Tambah Dokumen Lain
            </button>
          </div>
        </div>

        <div class="alert alert-light border mt-3">
          <i class="fas fa-info-circle"></i>
          Format file: PDF, JPG, atau PNG. Maksimal 5MB per file.
          Boleh isi sebagian dulu — yang dikosongkan tidak akan diubah.
        </div>

        <button type="submit" class="btn btn-primary mt-2">
          <i class="fas fa-upload"></i> Upload Semua
        </button>
        <a href="usulan_pensiun.php" class="btn btn-secondary mt-2">
          <i class="fas fa-arrow-left"></i> Kembali ke Daftar Usulan
        </a>
      </div>
    </form>
  </div>

  <!-- Modal Preview Berkas (pakai iframe, bukan link langsung, biar gak "disambar" IDM/download manager) -->
  <div class="modal fade" id="modalPreviewBerkas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content" style="height: 85vh;">
        <div class="modal-header">
          <h6 class="modal-title" id="modalPreviewTitle"><i class="fas fa-file me-2"></i>Preview Berkas</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body p-0" style="overflow:hidden;">
          <iframe id="iframePreviewBerkas" src="" style="width:100%; height:100%; border:0;"></iframe>
        </div>
        <div class="modal-footer py-2">
          <small class="text-muted me-auto">Kalau preview gak muncul, file mungkin perlu didownload manual via tombol "Download Semua (ZIP)" di atas.</small>
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
function tambahDokumen() {
  const wrapper = document.getElementById('tambahanWrapper');
  const row = document.createElement('div');
  row.className = 'd-flex gap-2 align-items-center mb-2';
  row.innerHTML = `
    <input type="text" name="tambahan_jenis[]" class="form-control form-control-sm"
           placeholder="Nama/keterangan dokumen" style="max-width:260px;" required>
    <input type="file" name="tambahan_file[]" accept=".pdf,.jpg,.jpeg,.png"
           class="form-control form-control-sm" style="max-width:220px;" required>
    <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.parentElement.remove()">
      <i class="fas fa-times"></i>
    </button>
  `;
  wrapper.appendChild(row);
}

// Buka preview berkas di modal (iframe), bukan navigasi/link baru
function lihatBerkasModal(id, jenis, judul) {
  const url = 'lihat_berkas.php/berkas.pdf?id=' + id + '&jenis=' + jenis;
  document.getElementById('iframePreviewBerkas').src = url;
  document.getElementById('modalPreviewTitle').innerHTML = '<i class="fas fa-file me-2"></i>' + judul;
  const modalEl = document.getElementById('modalPreviewBerkas');
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();
  modalEl.addEventListener('hidden.bs.modal', function handler() {
    document.getElementById('iframePreviewBerkas').src = '';
    modalEl.removeEventListener('hidden.bs.modal', handler);
  });
}
</script>

<?php require_once 'includes/footer.php'; ?>