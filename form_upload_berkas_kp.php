<?php
// form_upload_berkas_kp.php
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
    header('Location: kenaikan_pangkat.php');
    exit;
}

// Ambil data usulan
$stmt = $koneksi->prepare("SELECT * FROM kenaikan_pangkat WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$kp = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$kp) {
    header('Location: kenaikan_pangkat.php?error=Data tidak ditemukan');
    exit;
}

if ($kp['status'] !== 'disetujui') {
    header('Location: kenaikan_pangkat.php?error=Usulan belum disetujui, upload berkas belum bisa dilakukan');
    exit;
}

// 4 jenis berkas wajib
$jenis_wajib = [
    'Daftar Usul Mutasi',
    'Surat Keterangan',
    'Surat Hukdis',
    'Surat Tidak Pidana'
];

// Ambil berkas wajib yang sudah diupload
$stmt2 = $koneksi->prepare("SELECT * FROM berkas_kenaikan_pangkat WHERE id_kenaikan_pangkat = ? AND jenis_berkas IN (" . implode(',', array_fill(0, count($jenis_wajib), '?')) . ")");
$types  = 'i' . str_repeat('s', count($jenis_wajib));
$params = array_merge([$id], $jenis_wajib);
$stmt2->bind_param($types, ...$params);
$stmt2->execute();
$result_berkas = $stmt2->get_result();

$berkas_terupload = [];
while ($row = $result_berkas->fetch_assoc()) {
    $berkas_terupload[$row['jenis_berkas']] = $row;
}
$stmt2->close();

// Ambil dokumen tambahan
$stmt3 = $koneksi->prepare("SELECT * FROM berkas_kenaikan_pangkat WHERE id_kenaikan_pangkat = ? AND jenis_berkas NOT IN (" . implode(',', array_fill(0, count($jenis_wajib), '?')) . ") ORDER BY tanggal_upload DESC");
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
        Upload Berkas Kenaikan Pangkat
      </h1>
      <p class="dashboard-subtitle"><?= htmlspecialchars($kp['nama']) ?> — <?= htmlspecialchars($kp['nomor_usulan']) ?></p>
    </div>
    <?php if (!empty($berkas_terupload) || !empty($dokumen_tambahan)): ?>
      <a href="download_folder_berkas.php?id=<?= $id ?>&jenis=kp" class="btn btn-outline-dark">
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
          <i class="fas fa-check-circle"></i> <?= $jumlah_lengkap ?>/<?= $total_wajib ?> Lengkap — DUK sudah diperbarui
        </span>
      <?php else: ?>
        <span class="badge bg-warning text-dark" style="font-size:14px;padding:8px 14px;">
          <i class="fas fa-clock"></i> <?= $jumlah_lengkap ?>/<?= $total_wajib ?> Berkas Lengkap
        </span>
      <?php endif; ?>
    </div>

    <!-- Form upload berkas -->
    <form action="proses_upload_berkas_kp.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id_kenaikan_pangkat" value="<?= $id ?>">

      <div class="p-4">
        <!-- ===== BERKAS WAJIB ===== -->
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

              <div class="d-flex gap-2 align-items-center flex-wrap">
                <?php if ($sudah_ada): ?>
                  <button type="button" class="btn btn-outline-info btn-sm"
                          onclick="lihatBerkasModal(
                            <?= $berkas_terupload[$jenis]['id'] ?>,
                            'kp',
                            '<?= htmlspecialchars(addslashes($jenis)) ?>'
                          )">
                    <i class="fas fa-eye"></i> Lihat
                  </button>
                  <button type="button" class="btn btn-outline-danger btn-sm"
                          onclick="konfirmasiHapus(
                            <?= $berkas_terupload[$jenis]['id'] ?>,
                            <?= $id ?>,
                            '<?= htmlspecialchars(addslashes($jenis)) ?>'
                          )">
                    <i class="fas fa-trash"></i> Hapus
                  </button>
                <?php endif; ?>
                <input type="file"
                       name="berkas[<?= htmlspecialchars($jenis) ?>]"
                       accept=".pdf,.jpg,.jpeg,.png"
                       class="form-control form-control-sm"
                       style="max-width:220px;">
              </div>
            </div>
          </div>
        <?php endforeach; ?>

        <!-- ===== DOKUMEN TAMBAHAN ===== -->
        <div class="card mb-3 border-secondary">
          <div class="card-body">
            <h6 class="mb-3"><i class="fas fa-paperclip me-2"></i>Dokumen Tambahan (opsional)</h6>

            <?php if (!empty($dokumen_tambahan)): ?>
              <div class="mb-3" id="daftarDokumenTambahan">
                <?php foreach ($dokumen_tambahan as $dok): ?>
                  <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2 flex-wrap gap-2">
                    <div>
                      <strong><?= htmlspecialchars($dok['jenis_berkas']) ?></strong>
                      <br>
                      <small class="text-muted">
                        <?= htmlspecialchars($dok['nama_file']) ?>
                        &mdash; <?= date('d/m/Y H:i', strtotime($dok['tanggal_upload'])) ?>
                      </small>
                    </div>
                    <div class="d-flex gap-2">
                      <button type="button" class="btn btn-outline-info btn-sm"
                              onclick="lihatBerkasModal(
                                <?= $dok['id'] ?>,
                                'kp',
                                '<?= htmlspecialchars(addslashes($dok['jenis_berkas'])) ?>'
                              )">
                        <i class="fas fa-eye"></i> Lihat
                      </button>
                      <button type="button" class="btn btn-outline-danger btn-sm"
                              onclick="konfirmasiHapus(
                                <?= $dok['id'] ?>,
                                <?= $id ?>,
                                '<?= htmlspecialchars(addslashes($dok['jenis_berkas'])) ?>'
                              )">
                        <i class="fas fa-trash"></i> Hapus
                      </button>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div id="tambahanWrapper"></div>

            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="tambahDokumen()">
              <i class="fas fa-plus"></i> Tambah Dokumen Lain
            </button>

            <datalist id="saranJenisTambahan">
              <option value="SPMT Satya Lencana">
              <option value="Surat Pernyataan Satya Lencana">
            </datalist>
          </div>
        </div>

        <div class="alert alert-light border mt-3">
          <i class="fas fa-info-circle"></i>
          Format file: PDF, JPG, atau PNG. Maksimal 5MB per file.
          Boleh isi sebagian dulu — yang dikosongkan tidak akan diubah.
          Data DUK akan otomatis diperbarui begitu keempat berkas wajib di atas lengkap.
        </div>

        <button type="submit" class="btn btn-primary mt-2">
          <i class="fas fa-upload"></i> Upload Semua
        </button>
        <a href="kenaikan_pangkat.php" class="btn btn-secondary mt-2">
          <i class="fas fa-arrow-left"></i> Kembali ke Daftar Usulan
        </a>
      </div>
    </form>
  </div>

  <!-- ===================================================
       MODAL PREVIEW BERKAS
      tidak bisa diintersep IDM karena
       bukan HTTP request — 100% tampil inline di browser.
       =================================================== -->
  <div class="modal fade" id="modalPreviewBerkas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content" style="height:90vh;">
        <div class="modal-header py-2">
          <h6 class="modal-title" id="modalPreviewTitle">
            <i class="fas fa-file me-2"></i>Preview Berkas
          </h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body p-0" style="overflow:hidden;height:100%;position:relative;">

          <!-- Loading spinner -->
          <div id="previewLoading"
               style="display:flex;justify-content:center;align-items:center;
                      height:100%;color:#666;position:absolute;width:100%;top:0;left:0;
                      background:#fff;z-index:10;">
            <div class="text-center">
              <div class="spinner-border text-primary mb-3" role="status"></div>
              <p class="mb-0">Memuat berkas, harap tunggu...</p>
            </div>
          </div>

          <!-- PDF / file tampil di sini via blob URL — IDM tidak bisa intersep blob:// -->
          <embed id="iframePreviewBerkas"
       src=""
       type="application/pdf"
       style="width:100%;height:100%;border:0;display:none;">
          

          <!-- Gambar (JPG/PNG) -->
          <div id="imgPreviewWrapper"
               style="display:none;height:100%;overflow:auto;text-align:center;padding:20px;background:#f8f9fa;">
            <img id="imgPreviewBerkas"
                 src=""
                 alt="Preview"
                 style="max-width:100%;border-radius:6px;box-shadow:0 2px 12px rgba(0,0,0,.15);">
          </div>

          <!-- Error state -->
          <div id="previewError"
               style="display:none;justify-content:center;align-items:center;
                      height:100%;flex-direction:column;gap:12px;color:#dc3545;">
            <i class="fas fa-exclamation-triangle fa-3x"></i>
            <p id="previewErrorMsg" class="mb-0 text-center"></p>
          </div>

        </div>

        <div class="modal-footer py-2">
          <small class="text-muted me-auto" id="previewFooterNote"></small>
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Form hapus (hidden, submit via JS) — HARUS di luar form upload -->
  <form id="formHapusBerkas" action="hapus_berkas_kp.php" method="POST" style="display:none;">
    <input type="hidden" id="hapusIdBerkas"  name="id_berkas">
    <input type="hidden" id="hapusIdKP"      name="id_kenaikan_pangkat">
  </form>

</main>

<script>
/* ============================================================
   PREVIEW BERKAS — fetch() → Blob URL → iframe/img
   Kenapa blob://?
   IDM intersep berdasarkan URL HTTP/HTTPS yang browser load.
   Blob URL (blob://...) adalah object URL lokal di memory,
   bukan request jaringan → IDM tidak pernah tahu ada file ini
   → PDF tampil langsung di browser dengan toolbar Print/Save.
   ============================================================ */
function lihatBerkasModal(id, jenis, judul) {
  const modalEl    = document.getElementById('modalPreviewBerkas');
  const iframe     = document.getElementById('iframePreviewBerkas');
  const imgWrapper = document.getElementById('imgPreviewWrapper');
  const imgEl      = document.getElementById('imgPreviewBerkas');
  const loading    = document.getElementById('previewLoading');
  const errorDiv   = document.getElementById('previewError');
  const errorMsg   = document.getElementById('previewErrorMsg');
  const footerNote = document.getElementById('previewFooterNote');
  const title      = document.getElementById('modalPreviewTitle');

  // --- Reset semua state ---
  iframe.style.display     = 'none';
  imgWrapper.style.display = 'none';
  errorDiv.style.display   = 'none';
  loading.style.display    = 'flex';
  iframe.src = '';
  imgEl.src  = '';
  footerNote.textContent = '';
  title.innerHTML = '<i class="fas fa-file me-2"></i>' + judul;

  // Buka modal duluan supaya user lihat spinner
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();

  // Fetch file sebagai Blob — IDM tidak bisa intersep ini
  fetch('lihat_berkas.php?id=' + id + '&jenis=' + jenis, {
    credentials: 'same-origin'   // kirim session cookie agar check_session.php lolos
  })
  .then(function(res) {
    if (!res.ok) {
      throw new Error('Server merespons HTTP ' + res.status + '. File mungkin tidak ditemukan.');
    }
    return res.blob();
  })
  .then(function(blob) {
    // Deteksi tipe file dari magic bytes — lebih reliable daripada Content-Type header
    // yang bisa null/corrupt kalau ada whitespace di awal file PHP yang di-require
    var reader = new FileReader();
    reader.onloadend = function() {
      var arr    = new Uint8Array(reader.result);
      var header = '';
      // Baca 4 byte pertama, jadikan hex string
      for (var i = 0; i < Math.min(4, arr.length); i++) {
        header += arr[i].toString(16).padStart(2, '0');
      }

      // Magic bytes:
      // PDF  : 25504446 (%PDF)
      // JPEG : ffd8ff
      // PNG  : 89504e47
      var isPdf  = header.startsWith('25504446');
      var isJpeg = header.startsWith('ffd8ff');
      var isPng  = header.startsWith('89504e47');

      var mimeType = isPdf  ? 'application/pdf'
                   : isJpeg ? 'image/jpeg'
                   : isPng  ? 'image/png'
                   : null;

      // Buat blob baru dengan mime type yang benar — pastikan browser tau ini PDF/gambar
      var typedBlob = mimeType ? new Blob([blob], { type: mimeType }) : blob;
      var blobUrl   = URL.createObjectURL(typedBlob);

      loading.style.display = 'none';

      if (isPdf) {
        // Pakai <embed> — lebih reliable untuk PDF blob URL di Chrome/Edge
        // <iframe> kadang trigger download, <embed> langsung render via built-in PDF viewer
        iframe.setAttribute('src', blobUrl);
        iframe.style.display = 'block';
        footerNote.textContent = 'Gunakan toolbar di dalam frame untuk Print atau Save As PDF.';
      } else if (isJpeg || isPng) {
        // Gambar → tampil di <img>
        imgEl.src                = blobUrl;
        imgWrapper.style.display = 'block';
        footerNote.textContent   = 'Klik kanan gambar → Save Image As / Print untuk menyimpan.';
      } else {
        // Tipe tidak dikenal / tidak bisa dipreview browser
        errorDiv.style.display = 'flex';
        errorMsg.innerHTML     = 'Tipe file tidak dikenali (bukan PDF/JPG/PNG).<br>Gunakan tombol <b>Download Semua (ZIP)</b> di atas.';
        URL.revokeObjectURL(blobUrl);
        return;
      }

      // Cleanup blob URL saat modal ditutup — cegah memory leak
      modalEl.addEventListener('hidden.bs.modal', function handler() {
  URL.revokeObjectURL(blobUrl);
  iframe.removeAttribute('src');   // <-- pakai removeAttribute, bukan iframe.src = ''
  imgEl.src  = '';
  iframe.style.display     = 'none';
  imgWrapper.style.display = 'none';
  errorDiv.style.display   = 'none';
  loading.style.display    = 'flex';
  modalEl.removeEventListener('hidden.bs.modal', handler);
});
    };
    // Baca 4 byte pertama blob untuk deteksi magic bytes
    reader.readAsArrayBuffer(blob.slice(0, 4));
  })
  .catch(function(err) {
    loading.style.display  = 'none';
    errorDiv.style.display = 'flex';
    errorMsg.innerHTML = '<b>Gagal memuat berkas:</b><br>' + err.message;
  });
}

/* ============================================================
   TAMBAH BARIS DOKUMEN TAMBAHAN
   ============================================================ */
function tambahDokumen() {
  var wrapper = document.getElementById('tambahanWrapper');
  var row = document.createElement('div');
  row.className = 'd-flex gap-2 align-items-center mb-2';
  row.innerHTML =
    '<input type="text" name="tambahan_jenis[]" list="saranJenisTambahan" ' +
    '       class="form-control form-control-sm" placeholder="Nama/keterangan dokumen" ' +
    '       style="max-width:260px;" required>' +
    '<input type="file" name="tambahan_file[]" accept=".pdf,.jpg,.jpeg,.png" ' +
    '       class="form-control form-control-sm" style="max-width:220px;" required>' +
    '<button type="button" class="btn btn-outline-danger btn-sm" ' +
    '        onclick="this.parentElement.remove()">' +
    '  <i class="fas fa-times"></i>' +
    '</button>';
  wrapper.appendChild(row);
}

/* ============================================================
   KONFIRMASI HAPUS BERKAS
   ============================================================ */
function konfirmasiHapus(idBerkas, idKP, namaJenis) {
  Swal.fire({
    title: 'Hapus berkas?',
    html:  'Berkas <b>' + namaJenis + '</b> akan dihapus permanen dari server.<br>' +
           'Tindakan ini <b>tidak bisa dibatalkan</b>.',
    icon:  'warning',
    showCancelButton:    true,
    confirmButtonColor:  '#dc3545',
    cancelButtonColor:   '#6c757d',
    confirmButtonText:   '<i class="fas fa-trash me-1"></i>Ya, hapus',
    cancelButtonText:    'Batal'
  }).then(function(result) {
    if (result.isConfirmed) {
      document.getElementById('hapusIdBerkas').value = idBerkas;
      document.getElementById('hapusIdKP').value     = idKP;
      document.getElementById('formHapusBerkas').submit();
    }
  });
}
</script>

<?php require_once 'includes/footer.php'; ?>