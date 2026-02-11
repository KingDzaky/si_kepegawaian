<?php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';

if (!isAdmin()) {
    header('Location: usulan_pensiun.php?error=Akses ditolak');
    exit;
}

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Generate contoh nomor usulan (bukan untuk digunakan langsung)
$tahun = date('Y');
$bulan_romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
$bulan_sekarang = $bulan_romawi[(int)date('n')];

// Ambil nomor terakhir untuk referensi
$query_last = "SELECT nomor_usulan FROM usulan_pensiun WHERE YEAR(created_at) = $tahun ORDER BY id DESC LIMIT 1";
$result_last = $koneksi->query($query_last);

if ($result_last && $result_last->num_rows > 0) {
    $last = $result_last->fetch_assoc();
    // Ambil nomor urut dari nomor usulan terakhir
    preg_match('/\/(\d{3})\//', $last['nomor_usulan'], $matches);
    $last_num = isset($matches[1]) ? (int)$matches[1] : 0;
    $new_num = str_pad($last_num + 1, 3, '0', STR_PAD_LEFT);
} else {
    $new_num = '001';
}

// Format contoh nomor usulan
$contoh_nomor = "800.1.4.2/$new_num/DPPKBPM-BJM/$tahun";
$tanggal_usulan = date('Y-m-d');

// Ambil data DUK untuk dropdown
$query_duk = "SELECT id, nip, nama, ttl, pangkat_terakhir, golongan, jabatan_terakhir, 
              pendidikan_terakhir, prodi, jenis_kelamin, nomor_wa, kartu_pegawai, tmt_pangkat
              FROM duk 
              ORDER BY nama ASC";
$duk_list = $koneksi->query($query_duk);

// Ambil data Penyuluh untuk dropdown
$query_penyuluh = "SELECT id, nip, nama, ttl, pangkat_terakhir, golongan, jabatan_terakhir, 
                   pendidikan_terakhir, prodi, jenis_kelamin, '' as nomor_wa, '' as kartu_pegawai, tmt_pangkat
                   FROM penyuluh 
                   ORDER BY nama ASC";
$penyuluh_list = $koneksi->query($query_penyuluh);
?>

<link rel="stylesheet" href="css/dataduk.css">
<style>
.help-text {
    background: #e3f2fd;
    border-left: 4px solid #2196f3;
    padding: 12px 15px;
    margin-top: 8px;
    border-radius: 4px;
    font-size: 13px;
}

.help-text i {
    color: #2196f3;
    margin-right: 8px;
}

.format-example {
    background: #f5f5f5;
    padding: 8px 12px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    color: #333;
    margin-top: 5px;
}

.input-with-example {
    position: relative;
}

.input-with-example .form-control {
    font-family: 'Courier New', monospace;
}
</style>

<main class="main-content">
  <div class="dashboard-header fade-in">
    <h1 class="dashboard-title">
      <i class="fas fa-user-plus me-2"></i>
      Tambah Usulan Pensiun
    </h1>
    <p class="dashboard-subtitle">Form Pengajuan Usulan Pensiun Pegawai</p>
  </div>

  <div class="form-section fade-in">
    <form id="formUsulanPensiun" action="proses_tambah_usulan_pensiun.php" method="POST" onsubmit="return validateForm()">
      
      <!-- Informasi Usulan -->
      <div class="card mb-4">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Informasi Usulan</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-12">
              <div class="mb-3 input-with-example">
                <label class="form-label">
                  Nomor Usulan <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       class="form-control" 
                       name="nomor_usulan" 
                       id="nomor_usulan"
                       placeholder="Contoh: <?= $contoh_nomor ?>"
                       required
                       pattern="[\d.]+\/\d{3}\/[A-Z-]+\/\d{4}"
                       title="Format: xxx.x.x.x/xxx/DPPKBPM-BJM/YYYY">
                <div class="help-text">
                  <i class="fas fa-info-circle"></i>
                  <strong>Masukkan nomor usulan secara manual</strong> sesuai format yang diinginkan untuk menghindari duplikasi.
                </div>
                <div class="format-example">
                  <strong>Contoh format:</strong> <?= $contoh_nomor ?><br>
                  <strong>Nomor terakhir:</strong> <?= ($result_last && $result_last->num_rows > 0) ? $last['nomor_usulan'] : '-' ?><br>
                  <strong>Nomor berikutnya (saran):</strong> <?= $contoh_nomor ?>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Tanggal Usulan <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="tanggal_usulan" 
                       value="<?= $tanggal_usulan ?>" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">
                  <i class="fas fa-lightbulb text-warning"></i> Tips
                </label>
                <div class="alert alert-warning mb-0" style="padding: 10px; font-size: 13px;">
                  <i class="fas fa-exclamation-triangle"></i>
                  Pastikan nomor usulan <strong>tidak duplikat</strong> dengan yang sudah ada!
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pilih Sumber Data -->
      <div class="card mb-4">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0"><i class="fas fa-database me-2"></i>Pilih Sumber Data Pegawai</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Sumber Data <span class="text-danger">*</span></label>
            <select class="form-select" id="sumber_data" name="sumber_data" required onchange="toggleSumberData()">
              <option value="">-- Pilih Sumber --</option>
              <option value="duk">DUK (Data Urusan Kepegawaian)</option>
              <option value="penyuluh">Penyuluh</option>
            </select>
          </div>

          <!-- Dropdown DUK -->
          <div id="dropdown_duk" style="display: none;">
            <label class="form-label">Pilih Pegawai DUK <span class="text-danger">*</span></label>
            <select class="form-select" id="select_duk" onchange="loadDataPegawai('duk')">
              <option value="">-- Pilih Pegawai DUK --</option>
              <?php while ($duk = $duk_list->fetch_assoc()): ?>
                <option value="<?= $duk['id'] ?>" 
                        data-nip="<?= $duk['nip'] ?>"
                        data-nama="<?= htmlspecialchars($duk['nama']) ?>"
                        data-ttl="<?= htmlspecialchars($duk['ttl']) ?>"
                        data-kartu="<?= htmlspecialchars($duk['kartu_pegawai']) ?>"
                        data-pangkat="<?= htmlspecialchars($duk['pangkat_terakhir']) ?>"
                        data-golongan="<?= htmlspecialchars($duk['golongan']) ?>"
                        data-tmt="<?= $duk['tmt_pangkat'] ?>"
                        data-jabatan="<?= htmlspecialchars($duk['jabatan_terakhir']) ?>"
                        data-pendidikan="<?= htmlspecialchars($duk['pendidikan_terakhir']) ?>"
                        data-prodi="<?= htmlspecialchars($duk['prodi']) ?>"
                        data-jk="<?= $duk['jenis_kelamin'] ?>"
                        data-wa="<?= $duk['nomor_wa'] ?>">
                  <?= htmlspecialchars($duk['nama']) ?> - <?= $duk['nip'] ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Dropdown Penyuluh -->
          <div id="dropdown_penyuluh" style="display: none;">
            <label class="form-label">Pilih Penyuluh <span class="text-danger">*</span></label>
            <select class="form-select" id="select_penyuluh" onchange="loadDataPegawai('penyuluh')">
              <option value="">-- Pilih Penyuluh --</option>
              <?php while ($penyuluh = $penyuluh_list->fetch_assoc()): ?>
                <option value="<?= $penyuluh['id'] ?>" 
                        data-nip="<?= $penyuluh['nip'] ?>"
                        data-nama="<?= htmlspecialchars($penyuluh['nama']) ?>"
                        data-ttl="<?= htmlspecialchars($penyuluh['ttl']) ?>"
                        data-kartu=""
                        data-pangkat="<?= htmlspecialchars($penyuluh['pangkat_terakhir']) ?>"
                        data-golongan="<?= htmlspecialchars($penyuluh['golongan']) ?>"
                        data-tmt="<?= $penyuluh['tmt_pangkat'] ?>"
                        data-jabatan="<?= htmlspecialchars($penyuluh['jabatan_terakhir']) ?>"
                        data-pendidikan="<?= htmlspecialchars($penyuluh['pendidikan_terakhir']) ?>"
                        data-prodi="<?= htmlspecialchars($penyuluh['prodi']) ?>"
                        data-jk="<?= $penyuluh['jenis_kelamin'] ?>"
                        data-wa="">
                  <?= htmlspecialchars($penyuluh['nama']) ?> - <?= $penyuluh['nip'] ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Data Pegawai (Auto-fill) -->
      <div class="card mb-4">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0"><i class="fas fa-user me-2"></i>Data Pegawai</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">NIP <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nip" id="nip" readonly required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nama" id="nama" readonly required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Kartu Pegawai</label>
                <input type="text" class="form-control" name="kartu_pegawai" id="kartu_pegawai" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Tempat, Tanggal Lahir <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="ttl" id="ttl" readonly required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="tanggal_lahir" id="tanggal_lahir" 
                       required onchange="hitungTanggalPensiun()">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Tanggal Pensiun (Otomatis) <span class="text-danger">*</span></label>
                <input type="date" class="form-control bg-light" name="tanggal_pensiun" 
                       id="tanggal_pensiun" readonly required>
                <small class="text-muted">Otomatis: Tanggal Lahir + 60 tahun</small>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Pangkat Terakhir</label>
                <input type="text" class="form-control" name="pangkat_terakhir" id="pangkat_terakhir" readonly>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Golongan</label>
                <input type="text" class="form-control" name="golongan" id="golongan" readonly>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">TMT Pangkat</label>
                <input type="date" class="form-control" name="tmt_pangkat" id="tmt_pangkat" readonly>
              </div>
            </div>
            <div class="col-md-12">
              <div class="mb-3">
                <label class="form-label">Jabatan Terakhir</label>
                <input type="text" class="form-control" name="jabatan_terakhir" id="jabatan_terakhir" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Pendidikan Terakhir</label>
                <input type="text" class="form-control" name="pendidikan_terakhir" id="pendidikan_terakhir" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Program Studi</label>
                <input type="text" class="form-control" name="prodi" id="prodi" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Jenis Kelamin</label>
                <input type="text" class="form-control" name="jenis_kelamin" id="jenis_kelamin" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Nomor WhatsApp</label>
                <input type="text" class="form-control" name="nomor_wa" id="nomor_wa" readonly>
                <small class="text-muted">Untuk notifikasi reminder pensiun</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Informasi Pensiun -->
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
                  <option value="BUP">BUP (Batas Usia Pensiun)</option>
                  <option value="APS">APS (Atas Permintaan Sendiri)</option>
                  <option value="Permintaan Sendiri">Permintaan Sendiri</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Status Usulan <span class="text-danger">*</span></label>
                <select class="form-select" name="status" required>
                  <option value="draft">Draft</option>
                  <option value="diajukan">Diajukan</option>
                  <option value="disetujui">Disetujui</option>
                </select>
              </div>
            </div>
            <div class="col-md-12">
              <div class="mb-3">
                <label class="form-label">Alasan / Keterangan</label>
                <textarea class="form-control" name="alasan" rows="3" 
                          placeholder="Opsional: Tambahkan alasan atau keterangan"></textarea>
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
          <i class="fas fa-save me-2"></i>Simpan Usulan
        </button>
      </div>
    </form>
  </div>
</main>

<script>
function toggleSumberData() {
  const sumber = document.getElementById('sumber_data').value;
  
  document.getElementById('dropdown_duk').style.display = (sumber === 'duk') ? 'block' : 'none';
  document.getElementById('dropdown_penyuluh').style.display = (sumber === 'penyuluh') ? 'block' : 'none';
  
  // Reset form
  clearFormPegawai();
}

function loadDataPegawai(sumber) {
  const selectElement = (sumber === 'duk') ? 
    document.getElementById('select_duk') : 
    document.getElementById('select_penyuluh');
  
  const option = selectElement.options[selectElement.selectedIndex];
  
  if (!option.value) {
    clearFormPegawai();
    return;
  }
  
  // Fill form dengan data dari option
  document.getElementById('nip').value = option.getAttribute('data-nip');
  document.getElementById('nama').value = option.getAttribute('data-nama');
  document.getElementById('ttl').value = option.getAttribute('data-ttl');
  document.getElementById('kartu_pegawai').value = option.getAttribute('data-kartu');
  document.getElementById('pangkat_terakhir').value = option.getAttribute('data-pangkat');
  document.getElementById('golongan').value = option.getAttribute('data-golongan');
  document.getElementById('tmt_pangkat').value = option.getAttribute('data-tmt');
  document.getElementById('jabatan_terakhir').value = option.getAttribute('data-jabatan');
  document.getElementById('pendidikan_terakhir').value = option.getAttribute('data-pendidikan');
  document.getElementById('prodi').value = option.getAttribute('data-prodi');
  document.getElementById('jenis_kelamin').value = option.getAttribute('data-jk');
  document.getElementById('nomor_wa').value = option.getAttribute('data-wa');
  
  // Parse tanggal lahir dari TTL
  const ttl = option.getAttribute('data-ttl');
  const match = ttl.match(/(\d{4}-\d{2}-\d{2})|(\d{2}-\d{2}-\d{4})/);
  
  if (match) {
    let tglLahir = match[0];
    // Convert format if needed
    if (tglLahir.match(/\d{2}-\d{2}-\d{4}/)) {
      const parts = tglLahir.split('-');
      tglLahir = parts[2] + '-' + parts[1] + '-' + parts[0];
    }
    document.getElementById('tanggal_lahir').value = tglLahir;
    hitungTanggalPensiun();
  }
}

function clearFormPegawai() {
  document.getElementById('nip').value = '';
  document.getElementById('nama').value = '';
  document.getElementById('ttl').value = '';
  document.getElementById('kartu_pegawai').value = '';
  document.getElementById('tanggal_lahir').value = '';
  document.getElementById('tanggal_pensiun').value = '';
  document.getElementById('pangkat_terakhir').value = '';
  document.getElementById('golongan').value = '';
  document.getElementById('tmt_pangkat').value = '';
  document.getElementById('jabatan_terakhir').value = '';
  document.getElementById('pendidikan_terakhir').value = '';
  document.getElementById('prodi').value = '';
  document.getElementById('jenis_kelamin').value = '';
  document.getElementById('nomor_wa').value = '';
}

function hitungTanggalPensiun() {
  const tglLahir = document.getElementById('tanggal_lahir').value;
  
  if (!tglLahir) return;
  
  const lahir = new Date(tglLahir);
  const pensiun = new Date(lahir);
  pensiun.setFullYear(lahir.getFullYear() + 60);
  
  // Format YYYY-MM-DD
  const year = pensiun.getFullYear();
  const month = String(pensiun.getMonth() + 1).padStart(2, '0');
  const day = String(pensiun.getDate()).padStart(2, '0');
  
  document.getElementById('tanggal_pensiun').value = `${year}-${month}-${day}`;
}

function validateForm() {
  const sumber = document.getElementById('sumber_data').value;
  const nip = document.getElementById('nip').value;
  const nomorUsulan = document.getElementById('nomor_usulan').value;
  
  if (!sumber) {
    alert('Pilih sumber data pegawai (DUK atau Penyuluh)');
    return false;
  }
  
  if (!nip) {
    alert('Pilih pegawai terlebih dahulu');
    return false;
  }
  
  if (!nomorUsulan) {
    alert('Masukkan nomor usulan terlebih dahulu');
    return false;
  }
  
  // Validasi format nomor usulan
  const formatValid = /[\d.]+\/\d{3}\/[A-Z-]+\/\d{4}/.test(nomorUsulan);
  if (!formatValid) {
    alert('Format nomor usulan tidak sesuai!\nContoh: 800.1.4.2/001/DPPKBPM-BJM/2025');
    return false;
  }
  
  // Konfirmasi sebelum submit
  if (!confirm('Pastikan nomor usulan "' + nomorUsulan + '" sudah benar dan tidak duplikat. Lanjutkan?')) {
    return false;
  }
  
  return true;
}

// Auto-fill nomor usulan dengan contoh saat halaman load (opsional, bisa dihapus jika tidak mau)
// document.addEventListener('DOMContentLoaded', function() {
//   document.getElementById('nomor_usulan').placeholder = '<?= $contoh_nomor ?>';
// });
</script>

<?php require_once 'includes/footer.php'; ?>