<?php
session_start();
require_once 'check_session.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Hanya superadmin dan admin yang bisa akses
if (!isAdmin()) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}

$error = $_GET['error'] ?? '';

// Set nomor_usulan kosong untuk input manual
$nomor_usulan = '';

// ATAU jika ingin tetap ada default value otomatis, uncomment kode di bawah:
/*
$tahun = date('Y');
$query = "SELECT COUNT(*) as total FROM kenaikan_pangkat WHERE YEAR(tanggal_usulan) = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $tahun);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$urutan = str_pad($result['total'] + 1, 3, '0', STR_PAD_LEFT);
$nomor_usulan = "800.1.3.2/" . $urutan . "/DPPKBPM-BJM/" . $tahun;
*/
?>

<link rel="stylesheet" href="css/form_tambah_duk.css">
<!-- Tambahkan jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<main class="main-content">
  <!-- Container Notifikasi & Loading -->
  <div id="notification-container" style="position: fixed; top: 80px; right: 20px; z-index: 9999; width: 350px;"></div>
  
  <div id="loading-indicator" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10000; background: rgba(0,0,0,0.8); padding: 30px 40px; border-radius: 10px; color: white; text-align: center;">
    <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
    <div>Memuat data pegawai...</div>
  </div>

  <div class="container-fluid">
    <div class="page-header fade-in">
      <h2><i class="fas fa-file-signature me-3"></i>Buat Usulan Kenaikan Pangkat</h2>
      <p class="mb-0">Formulir Usulan Mutasi Kenaikan Pangkat</p>
    </div>

    <div class="row justify-content-center">
      <div class="col-12 col-xl-10">

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger fade-in">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <div class="form-card fade-in">
          <div class="card-body">
            <form action="proses_tambah_kenaikan_pangkat.php" method="POST" id="kenaikanPangkatForm">
              
              <!-- Informasi Usulan -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-file-alt me-2"></i>Informasi Usulan
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-hashtag"></i>Nomor Usulan <span class="required">*</span>
                      </label>
                      <input type="text" 
                             name="nomor_usulan" 
                             class="form-control" 
                             value="<?= htmlspecialchars($nomor_usulan) ?>"
                             placeholder="Contoh: 800.1.3.2/001/DPPKBPM-BJM/2025"
                             required>
                      <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Masukkan nomor usulan secara manual sesuai format yang diinginkan
                      </small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-calendar"></i>Tanggal Usulan <span class="required">*</span>
                      </label>
                      <input type="date" name="tanggal_usulan" class="form-control" 
                             value="<?= date('Y-m-d') ?>" required>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-list"></i>Jenis Kenaikan Pangkat <span class="required">*</span>
                      </label>
                      <select name="jenis_kenaikan" class="form-select" required>
                        <option value="Pilihan">1. PILIHAN</option>
                        <option value="Reguler" selected>2. REGULER</option>
                        <option value="Anumerta">3. ANUMERTA</option>
                        <option value="Pengabdian">4. PENGABDIAN</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Data Pegawai (Auto-fill dari DUK) -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-user me-2"></i>Data Pegawai
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label">
                        <i class="fas fa-id-card"></i>NIP <span class="required">*</span>
                      </label>
                      <div class="input-group">
                        <input type="text" name="nip" id="nip" class="form-control" 
                               required maxlength="18" placeholder="Masukkan NIP (18 digit)">
                        <button type="button" class="btn btn-primary" id="btnCariNIP">
                          <i class="fas fa-search"></i> Cari Data
                        </button>
                      </div>
                      <small class="text-muted">Klik tombol "Cari Data" setelah memasukkan NIP</small>
                    </div>
                  </div>
                </div>

                <div id="autoFillData" style="display:none;">
                  <div class="alert alert-success mt-3">
                    <i class="fas fa-check-circle me-2"></i>Data pegawai ditemukan dan berhasil dimuat!
                  </div>
                  
                  <!-- Hidden field untuk id_opd -->
                  <input type="hidden" name="id_opd" id="id_opd">
                  
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label"><i class="fas fa-user"></i>Nama</label>
                        <input type="text" name="nama" id="nama" class="form-control" readonly>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label"><i class="fas fa-id-badge"></i>Kartu Pegawai</label>
                        <input type="text" name="kartu_pegawai" id="kartu_pegawai" class="form-control" readonly>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label"><i class="fas fa-map-marker-alt"></i>Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" id="tempat_lahir" class="form-control" readonly>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label"><i class="fas fa-calendar-alt"></i>Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control" readonly>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label"><i class="fas fa-graduation-cap"></i>Pendidikan Terakhir</label>
                        <input type="text" name="pendidikan_terakhir" id="pendidikan_terakhir" class="form-control" readonly>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label"><i class="fas fa-book"></i>Program Studi</label>
                        <input type="text" name="prodi" id="prodi" class="form-control" readonly>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Data Pangkat LAMA -->
              <div class="form-section">
                <div class="section-title bg-danger text-white">
                  <i class="fas fa-arrow-down me-2"></i>Pangkat/Golongan/Jabatan LAMA
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Pangkat Lama <span class="required">*</span></label>
                      <input type="text" name="pangkat_lama" id="pangkat_lama" class="form-control" readonly required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Golongan Lama <span class="required">*</span></label>
                      <select name="golongan_lama" id="golongan_lama" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <option value="I/a">I/a</option><option value="I/b">I/b</option>
                        <option value="I/c">I/c</option><option value="I/d">I/d</option>
                        <option value="II/a">II/a</option><option value="II/b">II/b</option>
                        <option value="II/c">II/c</option><option value="II/d">II/d</option>
                        <option value="III/a">III/a</option><option value="III/b">III/b</option>
                        <option value="III/c">III/c</option><option value="III/d">III/d</option>
                        <option value="IV/a">IV/a</option><option value="IV/b">IV/b</option>
                        <option value="IV/c">IV/c</option><option value="IV/d">IV/d</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">TMT Pangkat Lama</label>
                      <input type="date" name="tmt_pangkat_lama" id="tmt_pangkat_lama" class="form-control" readonly required>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Masa Kerja (Tahun)</label>
                      <input type="number" name="masa_kerja_tahun_lama" class="form-control" required min="0">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Masa Kerja (Bulan)</label>
                      <input type="number" name="masa_kerja_bulan_lama" class="form-control" required min="0" max="11">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Gaji Pokok Lama</label>
                      <input type="text" name="gaji_pokok_lama" class="form-control money" required placeholder="3.043.600">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Jabatan Lama</label>
                      <input type="text" name="jabatan_lama" id="jabatan_lama" class="form-control" readonly required>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Data Pangkat BARU -->
              <div class="form-section">
                <div class="section-title bg-success text-white">
                  <i class="fas fa-arrow-up me-2"></i>Pangkat/Golongan/Jabatan BARU (Usulan)
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Pangkat Baru <span class="required">*</span></label>
                      <input type="text" name="pangkat_baru" class="form-control" required placeholder="Penata / III c">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Golongan Baru <span class="required">*</span></label>
                      <select name="golongan_baru" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <option value="III/c">III/c</option><option value="III/d">III/d</option>
                        <option value="IV/a">IV/a</option><option value="IV/b">IV/b</option>
                        <option value="IV/c">IV/c</option><option value="IV/d">IV/d</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">TMT Pangkat Baru</label>
                      <input type="date" name="tmt_pangkat_baru" class="form-control" required>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Masa Kerja (Tahun)</label>
                      <input type="number" name="masa_kerja_tahun_baru" class="form-control" required min="0">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Masa Kerja (Bulan)</label>
                      <input type="number" name="masa_kerja_bulan_baru" class="form-control" required min="0" max="11">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Gaji Pokok Baru</label>
                      <input type="text" name="gaji_pokok_baru" class="form-control money" required placeholder="3.645.200">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Jabatan Baru</label>
                      <input type="text" name="jabatan_baru" class="form-control" required>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Perhitungan Masa Kerja -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-calculator me-2"></i>Perhitungan Masa Kerja
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">MK Dalam Gol/Ruang (Tahun)</label>
                      <input type="number" name="mk_golongan_tahun" class="form-control" required min="0" value="4">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">MK Dalam Gol/Ruang (Bulan)</label>
                      <input type="number" name="mk_golongan_bulan" class="form-control" required min="0" max="11" value="0">
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label">Dari s/d</label>
                      <input type="text" name="mk_dari_sampai" class="form-control" 
                             placeholder="09-09-2021 s/d 01-08-2025">
                    </div>
                  </div>
                </div>
              </div>

              <!-- Atasan Langsung -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-user-tie me-2"></i>Atasan Langsung
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Nama Atasan</label>
                      <input type="text" name="atasan_nama" id="atasan_nama" class="form-control" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">NIP Atasan</label>
                      <input type="text" name="atasan_nip" id="atasan_nip" class="form-control" maxlength="18" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Pangkat Atasan</label>
                      <input type="text" name="atasan_pangkat" id="atasan_pangkat" class="form-control" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Jabatan Atasan</label>
                      <input type="text" name="atasan_jabatan" id="atasan_jabatan" class="form-control" readonly>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Wilayah & SKP -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-map-marked-alt me-2"></i>Wilayah Pembayaran & SKP
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label">Wilayah Pembayaran</label>
                      <input type="text" name="wilayah_pembayaran" class="form-control" 
                             placeholder="Badan Pengelolaan Keuangan dan Aset Daerah Kota Banjarmasin">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">SKP Tahun 1</label>
                      <input type="text" name="skp_tahun_1" class="form-control" placeholder="2023">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Nilai SKP Tahun 1</label>
                      <input type="text" name="skp_nilai_1" class="form-control" placeholder="Baik">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">SKP Tahun 2</label>
                      <input type="text" name="skp_tahun_2" class="form-control" placeholder="2024">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Nilai SKP Tahun 2</label>
                      <input type="text" name="skp_nilai_2" class="form-control" placeholder="Baik">
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label">Status</label>
                      <select name="status" class="form-select">
                        <option value="draft">Draft</option>
                        <option value="diajukan">Diajukan</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="btn-group-custom">
                <a href="kenaikan_pangkat.php" class="btn btn-secondary">
                  <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save me-2"></i>Simpan Data Usulan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Script Auto-fill Data Pegawai -->
<script>
$(document).ready(function() {
    // Event ketika tombol Cari diklik
    $('#btnCariNIP').on('click', function() {
        const nip = $('#nip').val().trim();
        
        if (nip.length === 0) {
            showNotification('error', 'Silakan masukkan NIP terlebih dahulu');
            return;
        }
        
        if (nip.length !== 18) {
            showNotification('error', 'NIP harus 18 digit');
            return;
        }
        
        // Tampilkan loading
        $('#loading-indicator').show();
        $('#autoFillData').hide();
        
        $.ajax({
            url: 'get_pegawai_data.php',
            type: 'GET',
            data: { nip: nip },
            dataType: 'json',
            success: function(response) {
                $('#loading-indicator').hide();
                
                if (response.success) {
                    // Isi data pegawai
                    $('#nama').val(response.data.nama);
                    $('#kartu_pegawai').val(response.data.kartu_pegawai);
                    $('#tempat_lahir').val(response.data.tempat_lahir);
                    $('#tanggal_lahir').val(response.data.tanggal_lahir);
                    $('#pendidikan_terakhir').val(response.data.pendidikan_terakhir);
                    $('#prodi').val(response.data.prodi);
                    
                    // Isi data pangkat lama
                    $('#pangkat_lama').val(response.data.pangkat_lama);
                    $('#golongan_lama').val(response.data.golongan_lama);
                    $('#tmt_pangkat_lama').val(response.data.tmt_pangkat_lama);
                    $('#jabatan_lama').val(response.data.jabatan_lama);
                    
                    // Isi data atasan
                    $('#atasan_nama').val(response.data.atasan_nama);
                    $('#atasan_nip').val(response.data.atasan_nip);
                    $('#atasan_pangkat').val(response.data.atasan_pangkat);
                    $('#atasan_jabatan').val(response.data.atasan_jabatan);
                    
                    // Tampilkan section data
                    $('#autoFillData').slideDown();
                    
                    // Tampilkan notifikasi sukses
                    showNotification('success', 'Data pegawai berhasil dimuat!');
                } else {
                    // Kosongkan field jika data tidak ditemukan
                    clearFormFields();
                    showNotification('error', response.message);
                }
            },
            error: function(xhr, status, error) {
                $('#loading-indicator').hide();
                showNotification('error', 'Terjadi kesalahan saat mengambil data. Silakan coba lagi.');
                console.error('Error:', error);
            }
        });
    });
    
    // Event ketika Enter ditekan di input NIP
    $('#nip').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btnCariNIP').click();
        }
    });
});

// Fungsi untuk mengosongkan field
function clearFormFields() {
    $('#nama, #kartu_pegawai, #tempat_lahir, #tanggal_lahir, #pendidikan_terakhir, #prodi').val('');
    //                                         ^^^^^^^^^^^^^^^ TAMBAHKAN INI
    $('#pangkat_lama, #golongan_lama, #tmt_pangkat_lama, #jabatan_lama').val('');
    $('#atasan_nama, #atasan_nip, #atasan_pangkat, #atasan_jabatan').val('');
    $('#autoFillData').hide();
}

// Fungsi untuk menampilkan notifikasi
function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const notification = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <i class="fas ${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('#notification-container').html(notification);
    
    // Auto hide setelah 5 detik
    setTimeout(function() {
        $('.alert').fadeOut(400, function() {
            $(this).remove();
        });
    }, 5000);
}
</script>

<?php require_once 'includes/footer.php'; ?>