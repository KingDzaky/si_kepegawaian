<?php
session_start();
require_once 'check_session.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

if (!isAdmin()) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}

$error = $_GET['error'] ?? '';
$nomor_usulan = '';
?>

<link rel="stylesheet" href="css/form_tambah_duk.css">
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
      <p class="mb-0">Formulir Daftar Usulan Mutasi Kenaikan Pangkat</p>
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
                      <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Klik tombol "Cari Data" atau tekan Enter — data pegawai akan terisi otomatis dari DUK
                      </small>
                    </div>
                  </div>
                </div>

                <div id="autoFillData" style="display:none;">
                  <div class="alert alert-success mt-3">
                    <i class="fas fa-check-circle me-2"></i>Data pegawai ditemukan dan berhasil dimuat!
                  </div>
                  
                  <input type="hidden" name="id_opd" id="id_opd">
                  
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label"><i class="fas fa-user me-1"></i>Nama</label>
                        <input type="text" name="nama" id="nama" class="form-control" readonly>
                        <small class="text-muted"><i class="fas fa-lock me-1"></i>Otomatis dari DUK</small>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label"><i class="fas fa-id-badge me-1"></i>Kartu Pegawai</label>
                        <input type="text" name="kartu_pegawai" id="kartu_pegawai" class="form-control" readonly>
                        <small class="text-muted"><i class="fas fa-lock me-1"></i>Otomatis dari DUK</small>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label"><i class="fas fa-map-marker-alt me-1"></i>Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" id="tempat_lahir" class="form-control" readonly>
                        <small class="text-muted"><i class="fas fa-lock me-1"></i>Otomatis dari DUK</small>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label"><i class="fas fa-calendar-alt me-1"></i>Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control" readonly>
                        <small class="text-muted"><i class="fas fa-lock me-1"></i>Otomatis dari DUK</small>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label"><i class="fas fa-graduation-cap me-1"></i>Pendidikan Terakhir</label>
                        <input type="text" name="pendidikan_terakhir" id="pendidikan_terakhir" class="form-control" readonly>
                        <small class="text-muted"><i class="fas fa-lock me-1"></i>Otomatis dari DUK</small>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label"><i class="fas fa-book me-1"></i>Program Studi</label>
                        <input type="text" name="prodi" id="prodi" class="form-control" readonly>
                        <small class="text-muted"><i class="fas fa-lock me-1"></i>Otomatis dari DUK</small>
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
                      <label class="form-label">Pangkat Lama</label>
                      <input type="text" name="pangkat_lama" id="pangkat_lama" class="form-control" readonly>
                      <small class="text-muted"><i class="fas fa-lock me-1"></i>Otomatis dari DUK</small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Golongan Lama</label>
                      <!-- FIX: pakai disabled + hidden agar value terkirim saat submit -->
                      <select name="golongan_lama_display" id="golongan_lama" class="form-select" disabled>
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
                      <!-- Hidden field yang benar-benar terkirim ke server -->
                      <input type="hidden" name="golongan_lama" id="golongan_lama_hidden">
                      <small class="text-muted"><i class="fas fa-lock me-1"></i>Otomatis dari DUK</small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">TMT Pangkat Lama</label>
                      <input type="date" name="tmt_pangkat_lama" id="tmt_pangkat_lama" class="form-control" readonly>
                      <small class="text-muted"><i class="fas fa-lock me-1"></i>Otomatis dari DUK</small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Masa Kerja (Tahun)</label>
                      <input type="number" name="masa_kerja_tahun_lama" id="mk_tahun_lama"
                             class="form-control" required min="0"
                             oninput="hitungTotalMasaKerja()">
                      <small class="text-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Dihitung otomatis dari TMT. Sesuaikan dengan SK jika berbeda.
                      </small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Masa Kerja (Bulan)</label>
                      <input type="number" name="masa_kerja_bulan_lama" id="mk_bulan_lama"
                             class="form-control" required min="0" max="11"
                             oninput="hitungTotalMasaKerja()">
                      <small class="text-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Dihitung otomatis dari TMT. Sesuaikan dengan SK jika berbeda.
                      </small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Gaji Pokok Lama</label>
                      <input type="text" name="gaji_pokok_lama" class="form-control money" required placeholder="3.043.600">
                      <small class="text-muted">Format: angka dengan titik, contoh: 3.043.600</small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Jabatan Lama</label>
                      <input type="text" name="jabatan_lama" id="jabatan_lama" class="form-control" readonly>
                      <small class="text-muted"><i class="fas fa-lock me-1"></i>Otomatis dari DUK</small>
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
                      <input type="text" name="pangkat_baru" class="form-control" required placeholder="Contoh: Penata">
                      <small class="text-muted">Isi sesuai usulan pangkat baru pegawai</small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Golongan Baru <span class="required">*</span></label>
                      <select name="golongan_baru" class="form-select" required>
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
                      <label class="form-label">TMT Pangkat Baru <span class="required">*</span></label>
                      <input type="date" name="tmt_pangkat_baru" id="tmt_pangkat_baru" class="form-control" required>
                      <small class="text-info">
                        <i class="fas fa-magic me-1"></i>Mengisi ini akan menghitung masa kerja otomatis
                      </small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Masa Kerja (Tahun)</label>
                      <input type="number" name="masa_kerja_tahun_baru" id="mk_tahun_baru"
                             class="form-control" required min="0"
                             oninput="updateSummaryTotal()">
                      <small class="text-success">
                        <i class="fas fa-magic me-1"></i>Otomatis = Lama + Dalam Golongan
                      </small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Masa Kerja (Bulan)</label>
                      <input type="number" name="masa_kerja_bulan_baru" id="mk_bulan_baru"
                             class="form-control" required min="0" max="11"
                             oninput="updateSummaryTotal()">
                      <small class="text-success">
                        <i class="fas fa-edit me-1"></i>Bisa disesuaikan dengan SK
                      </small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Gaji Pokok Baru</label>
                      <input type="text" name="gaji_pokok_baru" class="form-control money" required placeholder="3.645.200">
                      <small class="text-muted">Format: angka dengan titik, contoh: 3.645.200</small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Jabatan Baru <span class="required">*</span></label>
                      <input type="text" name="jabatan_baru" class="form-control" required
                             placeholder="Contoh: Penelaah Teknis Kebijakan">
                    </div>
                  </div>
                </div>
              </div>

              <!-- Perhitungan Masa Kerja -->
              <div class="alert alert-info mb-3" style="font-size: 13px;">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Cara Perhitungan:</strong> Total masa kerja baru = 
                Masa kerja lama (dari TMT pangkat lama s/d sekarang) + 
                Masa kerja dalam golongan (dari TMT lama s/d TMT baru).
                Nilai akan dihitung otomatis, sesuaikan jika berbeda dengan SK.
              </div>

              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-calculator me-2"></i>Perhitungan Masa Kerja
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">MK Dalam Gol/Ruang (Tahun)</label>
                      <input type="number" name="mk_golongan_tahun" id="mk_golongan_tahun"
                             class="form-control" required min="0"
                             oninput="hitungTotalMasaKerja()">
                      <small class="text-muted">
                        <i class="fas fa-magic me-1"></i>Otomatis dari selisih TMT lama & baru
                      </small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">MK Dalam Gol/Ruang (Bulan)</label>
                      <input type="number" name="mk_golongan_bulan" id="mk_golongan_bulan"
                             class="form-control" required min="0" max="11"
                             oninput="hitungTotalMasaKerja()">
                      <small class="text-muted">
                        <i class="fas fa-magic me-1"></i>Otomatis dari selisih TMT lama & baru
                      </small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Dari s/d</label>
                      <input type="text" name="mk_dari_sampai" id="mk_dari_sampai"
                             class="form-control" required
                             placeholder="Otomatis: 01-04-2021 s/d 01-08-2025">
                      <small class="text-muted">
                        <i class="fas fa-magic me-1"></i>Otomatis terisi setelah pilih TMT
                      </small>
                    </div>
                  </div>
                </div>

                <!-- Summary Box Perhitungan -->
                <div style="background: linear-gradient(135deg, #f8f9ff, #e8f4ff); border: 2px solid #667eea; border-radius: 10px; padding: 15px 20px; margin-top: 15px;">
                  <div style="font-weight: 700; color: #667eea; margin-bottom: 10px; font-size: 13px;">
                    <i class="fas fa-sigma me-2"></i>Ringkasan Perhitungan
                  </div>
                  <div style="display:flex; justify-content:space-between; padding: 5px 0; border-bottom: 1px dashed #c5d3f0; font-size: 13px;">
                    <span class="text-muted"><i class="fas fa-history me-2 text-danger"></i>Masa Kerja Lama</span>
                    <strong id="summary_mk_lama">— Tahun — Bulan</strong>
                  </div>
                  <div style="display:flex; justify-content:space-between; padding: 5px 0; border-bottom: 1px dashed #c5d3f0; font-size: 13px;">
                    <span class="text-muted"><i class="fas fa-exchange-alt me-2 text-primary"></i>Masa Kerja Dalam Golongan</span>
                    <strong id="summary_mk_gol">— Tahun — Bulan</strong>
                  </div>
                  <div style="display:flex; justify-content:space-between; padding: 8px 0 0 0; font-size: 14px; font-weight: 700;">
                    <span style="color:#667eea;"><i class="fas fa-equals me-2"></i>Total Masa Kerja Baru</span>
                    <span style="color:#667eea;" id="summary_mk_total">— Tahun — Bulan</span>
                  </div>
                </div>
              </div>

              <!-- Atasan Langsung -->
              <div class="form-section">
                <div class="section-title">
                  <i class="fas fa-user-tie me-2"></i>Atasan Langsung
                </div>
                <small class="text-muted d-block mb-3">
                  <i class="fas fa-info-circle me-1"></i>
                  Data atasan terisi otomatis berdasarkan Kepala OPD yang terdaftar di sistem
                </small>
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
                             value="Badan Pengelolaan Keuangan dan Aset Daerah Kota Banjarmasin"
                             placeholder="Badan Pengelolaan Keuangan dan Aset Daerah Kota Banjarmasin">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">SKP Tahun 1</label>
                      <input type="text" name="skp_tahun_1" class="form-control"
                             placeholder="<?= date('Y') - 2 ?>">
                      <small class="text-muted">Isi tahun SKP pertama, contoh: <?= date('Y') - 2 ?></small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Nilai SKP Tahun 1</label>
                      <input type="text" name="skp_nilai_1" class="form-control" placeholder="Baik">
                      <small class="text-muted">Contoh: Baik, Sangat Baik</small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">SKP Tahun 2</label>
                      <input type="text" name="skp_tahun_2" class="form-control"
                             placeholder="<?= date('Y') - 1 ?>">
                      <small class="text-muted">Isi tahun SKP kedua, contoh: <?= date('Y') - 1 ?></small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Nilai SKP Tahun 2</label>
                      <input type="text" name="skp_nilai_2" class="form-control" placeholder="Baik">
                      <small class="text-muted">Contoh: Baik, Sangat Baik</small>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label">Status</label>
                      <select name="status" class="form-select">
                        <option value="draft">Draft</option>
                        <option value="diajukan">Diajukan</option>
                      </select>
                      <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Pilih <strong>Draft</strong> jika belum siap diajukan, <strong>Diajukan</strong> jika sudah siap dikirim ke BKD
                      </small>
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

<script>
$(document).ready(function () {

    // ===== CARI DATA PEGAWAI =====
    function cariData() {
        const nip = $('#nip').val().trim();
        if (!nip) { showNotification('error', 'Silakan masukkan NIP terlebih dahulu'); return; }
        if (nip.length !== 18) { showNotification('error', 'NIP harus tepat 18 digit'); return; }

        $('#loading-indicator').show();
        $('#autoFillData').hide();

        $.ajax({
            url: 'get_pegawai_data.php',
            type: 'GET',
            data: { nip: nip },
            dataType: 'json',
            success: function (response) {
                $('#loading-indicator').hide();

                if (response.success) {
                    const d = response.data;

                    // Data Pegawai
                    $('#nama').val(d.nama);
                    $('#kartu_pegawai').val(d.kartu_pegawai);
                    $('#tempat_lahir').val(d.tempat_lahir);
                    $('#tanggal_lahir').val(d.tanggal_lahir);
                    $('#pendidikan_terakhir').val(d.pendidikan_terakhir);
                    $('#prodi').val(d.prodi);
                    $('#id_opd').val(d.id_opd);

                    // Pangkat Lama
                    $('#pangkat_lama').val(d.pangkat_lama);
                    // FIX: set golongan di select (untuk tampilan) DAN hidden field (untuk submit)
                    $('#golongan_lama').val(d.golongan_lama);
                    $('#golongan_lama_hidden').val(d.golongan_lama);
                    $('#tmt_pangkat_lama').val(d.tmt_pangkat_lama);
                    $('#jabatan_lama').val(d.jabatan_lama);
                    $('#mk_tahun_lama').val(d.masa_kerja_tahun_lama);
                    $('#mk_bulan_lama').val(d.masa_kerja_bulan_lama);

                    // Atasan
                    $('#atasan_nama').val(d.atasan_nama);
                    $('#atasan_nip').val(d.atasan_nip);
                    $('#atasan_pangkat').val(d.atasan_pangkat);
                    $('#atasan_jabatan').val(d.atasan_jabatan);

                    // Hitung otomatis setelah data terisi
                    hitungTotalMasaKerja();
                    updateDariSampai();

                    $('#autoFillData').slideDown();
                    showNotification('success', 'Data pegawai <strong>' + d.nama + '</strong> berhasil dimuat!');

                } else {
                    clearFormFields();
                    if (response.type === 'warning') {
                        showNotification('warning', response.message);
                    } else {
                        showNotification('error', response.message);
                    }
                }
            },
            error: function () {
                $('#loading-indicator').hide();
                showNotification('error', 'Terjadi kesalahan saat mengambil data. Silakan coba lagi.');
            }
        });
    }

    $('#btnCariNIP').on('click', cariData);
    $('#nip').on('keypress', function (e) {
        if (e.which === 13) { e.preventDefault(); cariData(); }
    });
});

// ===== PERHITUNGAN MASA KERJA =====

// Hitung masa kerja dalam golongan dari TMT lama s/d TMT baru
// dan update field "dari s/d"
function updateDariSampai() {
    const tmtLama = document.querySelector('[name="tmt_pangkat_lama"]').value;
    const tmtBaru = document.getElementById('tmt_pangkat_baru').value;

    if (!tmtLama || !tmtBaru) return;

    function formatTgl(s) {
        const [y, m, d] = s.split('-');
        return `${d}-${m}-${y}`;
    }

    document.getElementById('mk_dari_sampai').value =
        `${formatTgl(tmtLama)} s/d ${formatTgl(tmtBaru)}`;

    const d1 = new Date(tmtLama);
    const d2 = new Date(tmtBaru);
    let tahun = d2.getFullYear() - d1.getFullYear();
    let bulan  = d2.getMonth() - d1.getMonth();
    if (bulan < 0) { tahun--; bulan += 12; }

    document.getElementById('mk_golongan_tahun').value = Math.max(0, tahun);
    document.getElementById('mk_golongan_bulan').value = Math.max(0, bulan);

    hitungTotalMasaKerja();
}

// Hitung total = lama + dalam golongan → isi ke masa kerja baru
function hitungTotalMasaKerja() {
    const tL = parseInt(document.getElementById('mk_tahun_lama')?.value) || 0;
    const bL = parseInt(document.getElementById('mk_bulan_lama')?.value)  || 0;
    const tG = parseInt(document.getElementById('mk_golongan_tahun')?.value) || 0;
    const bG = parseInt(document.getElementById('mk_golongan_bulan')?.value)  || 0;

    let totalBulan = (tL * 12 + bL) + (tG * 12 + bG);
    const totalTahun = Math.floor(totalBulan / 12);
    totalBulan = totalBulan % 12;

    document.getElementById('mk_tahun_baru').value = totalTahun;
    document.getElementById('mk_bulan_baru').value = totalBulan;

    updateSummaryBox(tL, bL, tG, bG, totalTahun, totalBulan);
}

// Dipanggil saat user edit manual masa kerja baru
function updateSummaryTotal() {
    const tL = parseInt(document.getElementById('mk_tahun_lama')?.value)    || 0;
    const bL = parseInt(document.getElementById('mk_bulan_lama')?.value)     || 0;
    const tG = parseInt(document.getElementById('mk_golongan_tahun')?.value) || 0;
    const bG = parseInt(document.getElementById('mk_golongan_bulan')?.value)  || 0;
    const tT = parseInt(document.getElementById('mk_tahun_baru')?.value)     || 0;
    const bT = parseInt(document.getElementById('mk_bulan_baru')?.value)      || 0;
    updateSummaryBox(tL, bL, tG, bG, tT, bT);
}

function updateSummaryBox(tL, bL, tG, bG, tT, bT) {
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    set('summary_mk_lama',  `${tL} Tahun ${bL} Bulan`);
    set('summary_mk_gol',   `${tG} Tahun ${bG} Bulan`);
    set('summary_mk_total', `${tT} Tahun ${bT} Bulan`);
}

// Pasang event listener saat halaman siap
document.addEventListener('DOMContentLoaded', function () {
    const tmtBaru = document.getElementById('tmt_pangkat_baru');
    if (tmtBaru) tmtBaru.addEventListener('change', updateDariSampai);

    const tmtLama = document.querySelector('[name="tmt_pangkat_lama"]');
    if (tmtLama) tmtLama.addEventListener('change', updateDariSampai);
});

// ===== CLEAR FORM =====
function clearFormFields() {
    const ids = ['nama','kartu_pegawai','tempat_lahir','tanggal_lahir',
                 'pendidikan_terakhir','prodi','pangkat_lama','jabatan_lama',
                 'tmt_pangkat_lama','atasan_nama','atasan_nip',
                 'atasan_pangkat','atasan_jabatan',
                 'mk_tahun_lama','mk_bulan_lama'];
    ids.forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    document.getElementById('golongan_lama').value = '';
    document.getElementById('golongan_lama_hidden').value = '';
    document.getElementById('mk_golongan_tahun').value = 0;
    document.getElementById('mk_golongan_bulan').value = 0;
    document.getElementById('mk_tahun_baru').value = '';
    document.getElementById('mk_bulan_baru').value = '';
    document.getElementById('mk_dari_sampai').value = '';
    updateSummaryBox(0, 0, 0, 0, 0, 0);
    $('#autoFillData').hide();
}

// ===== NOTIFIKASI =====
function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success'
                     : type === 'warning' ? 'alert-warning'
                     : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle'
               : type === 'warning' ? 'fa-exclamation-triangle'
               : 'fa-exclamation-circle';

    $('#notification-container').html(`
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert"
             style="box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <i class="fas ${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);

    setTimeout(function () {
        $('#notification-container .alert').fadeOut(400, function () { $(this).remove(); });
    }, 5000);
}
</script>

<?php require_once 'includes/footer.php'; ?>