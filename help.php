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

?>

<main class="main-content">
  <div class="container-fluid">
    
    <!-- Help Header -->
    <div class="help-header">
      <div class="header-content">
        <div class="header-icon">
          <i class="fas fa-question-circle"></i>
        </div>
        <div class="header-text">
          <h1>Pusat Bantuan</h1>
          <p>Panduan Penggunaan Sistem Informasi Kepegawaian</p>
        </div>
      </div>
      
      <!-- Quick Search -->
      <div class="help-search">
        <input type="text" class="form-control search-input" id="helpSearch" 
               placeholder="Cari topik bantuan...">
        <i class="fas fa-search search-icon"></i>
      </div>
    </div>

    <div class="row">
      <!-- Sidebar Navigation -->
      <div class="col-lg-3">
        <div class="help-sidebar">
          <h6 class="sidebar-title">Kategori Bantuan</h6>
          <ul class="help-nav" id="helpNav">
            <li class="nav-item active">
              <a href="#getting-started" class="nav-link">
                <i class="fas fa-rocket me-2"></i>Memulai
              </a>
            </li>
            <li class="nav-item">
              <a href="#duk-management" class="nav-link">
                <i class="fas fa-users me-2"></i>Kelola Data DUK
              </a>
            </li>
            <li class="nav-item">
              <a href="#penyuluh-management" class="nav-link">
                <i class="fas fa-chalkboard-teacher me-2"></i>Kelola Data Penyuluh
              </a>
            </li>
            <li class="nav-item">
              <a href="#export-data" class="nav-link">
                <i class="fas fa-download me-2"></i>Export Data
              </a>
            </li>
            <li class="nav-item">
              <a href="#filter-search" class="nav-link">
                <i class="fas fa-filter me-2"></i>Filter & Pencarian
              </a>
            </li>
            <li class="nav-item">
              <a href="#troubleshooting" class="nav-link">
                <i class="fas fa-tools me-2"></i>Troubleshooting
              </a>
            </li>
            <li class="nav-item">
              <a href="#faq" class="nav-link">
                <i class="fas fa-question me-2"></i>FAQ
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col-lg-9">
        <div class="help-content">
          
          <!-- Getting Started Section -->
          <section id="getting-started" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-rocket me-2"></i>Memulai
            </h2>
            
            <div class="content-card">
              <h5>Selamat Datang di Sistem Informasi Kepegawaian</h5>
              <p>Sistem ini dirancang untuk memudahkan pengelolaan data pegawai DUK (Daftar Urusan Kepegawaian) dan data Penyuluh Pertanian di Dinas Pertanian Kota Banjarmasin.</p>
              
              <h6 class="mt-4">Fitur Utama:</h6>
              <ul class="feature-list">
                <li><i class="fas fa-check text-success me-2"></i>Manajemen Data DUK Pegawai</li>
                <li><i class="fas fa-check text-success me-2"></i>Manajemen Data Penyuluh Pertanian</li>
                <li><i class="fas fa-check text-success me-2"></i>Export Data ke PDF dan Excel</li>
                <li><i class="fas fa-check text-success me-2"></i>Filter dan Pencarian Data</li>
                <li><i class="fas fa-check text-success me-2"></i>Dashboard Statistik Real-time</li>
              </ul>

              <div class="info-box info">
                <i class="fas fa-info-circle"></i>
                <div>
                  <strong>Tips:</strong> Gunakan menu sidebar di sebelah kiri untuk navigasi cepat antar halaman.
                </div>
              </div>
            </div>
          </section>

          <!-- DUK Management Section -->
          <section id="duk-management" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-users me-2"></i>Kelola Data DUK
            </h2>
            
            <div class="content-card">
              <h5>Menambah Data DUK</h5>
              <ol class="step-list">
                <li>Klik menu <strong>"Tambah DUK"</strong> di sidebar atau tombol <strong>"+ Tambah Data"</strong></li>
                <li>Isi formulir dengan data lengkap:
                  <ul>
                    <li><strong>Data Pribadi:</strong> Nama, NIP, TTL, Jenis Kelamin</li>
                    <li><strong>Kepangkatan:</strong> Pangkat, Golongan, TMT Pangkat</li>
                    <li><strong>Jabatan:</strong> Jabatan, Eselon, TMT Eselon, Pendidikan</li>
                  </ul>
                </li>
                <li>Field dengan tanda <span class="text-danger">*</span> wajib diisi</li>
                <li>Klik <strong>"Preview"</strong> untuk melihat data sebelum menyimpan (opsional)</li>
                <li>Klik <strong>"Simpan Data"</strong> untuk menyimpan ke database</li>
              </ol>

              <div class="info-box warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                  <strong>Perhatian:</strong> NIP harus 18 digit angka. Sistem akan otomatis memformat input NIP.
                </div>
              </div>

              <h5 class="mt-4">Mengedit Data DUK</h5>
              <ol class="step-list">
                <li>Buka halaman <strong>"Data DUK"</strong></li>
                <li>Cari data yang ingin diedit</li>
                <li>Klik tombol <button class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></button> (Edit)</li>
                <li>Ubah data yang diperlukan</li>
                <li>Klik <strong>"Update Data"</strong> untuk menyimpan perubahan</li>
              </ol>

              <h5 class="mt-4">Menghapus Data DUK</h5>
              <ol class="step-list">
                <li>Buka halaman <strong>"Data DUK"</strong></li>
                <li>Cari data yang ingin dihapus</li>
                <li>Klik tombol <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button> (Hapus)</li>
                <li>Konfirmasi penghapusan data</li>
              </ol>

              <div class="info-box danger">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                  <strong>Penting:</strong> Data yang sudah dihapus tidak dapat dikembalikan. Pastikan Anda yakin sebelum menghapus.
                </div>
              </div>
            </div>
          </section>

          <!-- Penyuluh Management Section -->
          <section id="penyuluh-management" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-chalkboard-teacher me-2"></i>Kelola Data Penyuluh
            </h2>
            
            <div class="content-card">
              <h5>Menambah Data Penyuluh</h5>
              <p>Proses menambah data penyuluh sama dengan menambah data DUK:</p>
              <ol class="step-list">
                <li>Klik menu <strong>"Data Penyuluh"</strong> di sidebar</li>
                <li>Klik tombol <strong>"+ Tambah Penyuluh Baru"</strong></li>
                <li>Isi formulir dengan lengkap</li>
                <li>Klik <strong>"Simpan Data"</strong></li>
              </ol>

              <h5 class="mt-4">Perbedaan Data Penyuluh dengan DUK</h5>
              <p>Data penyuluh tidak memiliki field Eselon dan TMT Eselon karena penyuluh pertanian memiliki struktur jabatan fungsional.</p>
            </div>
          </section>

          <!-- Export Data Section -->
          <section id="export-data" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-download me-2"></i>Export Data
            </h2>
            
            <div class="content-card">
              <h5>Export Data ke PDF/Excel</h5>
              <ol class="step-list">
                <li>Buka halaman <strong>"Data DUK"</strong> atau <strong>"Data Penyuluh"</strong></li>
                <li>Terapkan filter jika hanya ingin export data tertentu</li>
                <li>Klik tombol <strong>"Export Data"</strong></li>
                <li>Pilih format export:
                  <ul>
                    <li><strong>PDF:</strong> Format dokumen resmi dengan layout DUK standar</li>
                    <li><strong>Excel:</strong> Format spreadsheet untuk analisis data</li>
                  </ul>
                </li>
                <li>File akan otomatis terunduh</li>
              </ol>

              <div class="info-box info">
                <i class="fas fa-lightbulb"></i>
                <div>
                  <strong>Tips:</strong> Gunakan filter untuk export data spesifik, misalnya hanya pegawai dengan eselon tertentu.
                </div>
              </div>

              <h5 class="mt-4">Alternatif Export</h5>
              <p>Jika export PDF/Excel gagal, Anda dapat menggunakan:</p>
              <ul>
                <li><strong>Print:</strong> Cetak langsung dari browser</li>
                <li><strong>CSV:</strong> Export ke format CSV simple</li>
              </ul>

              <div class="info-box warning">
                <i class="fas fa-wifi"></i>
                <div>
                  <strong>Catatan:</strong> Fitur export membutuhkan koneksi internet untuk memuat library. Jika offline, gunakan Print sebagai alternatif.
                </div>
              </div>
            </div>
          </section>

          <!-- Filter & Search Section -->
          <section id="filter-search" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-filter me-2"></i>Filter & Pencarian
            </h2>
            
            <div class="content-card">
              <h5>Menggunakan Filter</h5>
              <ol class="step-list">
                <li>Klik tombol <strong>"Filter & Pencarian Lanjutan"</strong></li>
                <li>Pilih kriteria filter yang diinginkan:
                  <ul>
                    <li>Nama/NIP</li>
                    <li>Jenis Kelamin</li>
                    <li>Pangkat dan Golongan</li>
                    <li>Jabatan</li>
                    <li>Pendidikan</li>
                    <li>Eselon</li>
                  </ul>
                </li>
                <li>Klik <strong>"Terapkan Filter"</strong></li>
                <li>Data akan ditampilkan sesuai filter</li>
              </ol>

              <h5 class="mt-4">Quick Search</h5>
              <p>Untuk pencarian cepat, gunakan kotak pencarian di header tabel:</p>
              <ul>
                <li>Ketik nama, NIP, atau jabatan</li>
                <li>Hasil akan tampil secara real-time</li>
              </ul>

              <div class="info-box success">
                <i class="fas fa-check-circle"></i>
                <div>
                  <strong>Fitur:</strong> Anda dapat menggabungkan multiple filter untuk hasil yang lebih spesifik.
                </div>
              </div>

              <h5 class="mt-4">Reset Filter</h5>
              <p>Untuk menampilkan semua data kembali, klik tombol <strong>"Reset Filter"</strong>.</p>
            </div>
          </section>

          <!-- Troubleshooting Section -->
          <section id="troubleshooting" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-tools me-2"></i>Troubleshooting
            </h2>
            
            <div class="content-card">
              <h5>Masalah Umum dan Solusi</h5>
              
              <div class="accordion" id="troubleshootingAccordion">
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#problem1">
                      Data tidak muncul di tabel
                    </button>
                  </h2>
                  <div id="problem1" class="accordion-collapse collapse show" data-bs-parent="#troubleshootingAccordion">
                    <div class="accordion-body">
                      <strong>Solusi:</strong>
                      <ul>
                        <li>Refresh halaman (F5)</li>
                        <li>Pastikan filter tidak aktif (klik Reset Filter)</li>
                        <li>Cek koneksi database</li>
                        <li>Pastikan ada data di database</li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#problem2">
                      Export PDF/Excel gagal
                    </button>
                  </h2>
                  <div id="problem2" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                    <div class="accordion-body">
                      <strong>Solusi:</strong>
                      <ul>
                        <li>Pastikan koneksi internet stabil</li>
                        <li>Gunakan browser Chrome/Firefox terbaru</li>
                        <li>Hapus cache browser (Ctrl+Shift+Delete)</li>
                        <li>Gunakan alternatif Print atau CSV</li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#problem3">
                      Form tidak bisa di-submit
                    </button>
                  </h2>
                  <div id="problem3" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                    <div class="accordion-body">
                      <strong>Solusi:</strong>
                      <ul>
                        <li>Pastikan semua field wajib (*) sudah diisi</li>
                        <li>NIP harus 18 digit angka</li>
                        <li>Format tanggal harus benar (YYYY-MM-DD)</li>
                        <li>Cek pesan error yang muncul</li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#problem4">
                      Filter tidak berfungsi
                    </button>
                  </h2>
                  <div id="problem4" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                    <div class="accordion-body">
                      <strong>Solusi:</strong>
                      <ul>
                        <li>Pastikan JavaScript aktif di browser</li>
                        <li>Refresh halaman dan coba lagi</li>
                        <li>Gunakan Quick Search sebagai alternatif</li>
                        <li>Cek console browser untuk error (F12)</li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#problem5">
                      Sidebar tidak muncul/tersembunyi
                    </button>
                  </h2>
                  <div id="problem5" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                    <div class="accordion-body">
                      <strong>Solusi:</strong>
                      <ul>
                        <li>Klik tombol hamburger (☰) di header</li>
                        <li>Pada mobile, sidebar auto-hide untuk ruang lebih luas</li>
                        <li>Refresh halaman jika sidebar stuck</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- FAQ Section -->
          <section id="faq" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-question me-2"></i>FAQ (Frequently Asked Questions)
            </h2>
            
            <div class="content-card">
              <div class="faq-list">
                <div class="faq-item">
                  <h6>Q: Apakah data tersimpan secara otomatis?</h6>
                  <p>A: Tidak. Anda harus klik tombol "Simpan Data" untuk menyimpan perubahan ke database.</p>
                </div>

                <div class="faq-item">
                  <h6>Q: Berapa maksimal data yang bisa dikelola?</h6>
                  <p>A: Sistem dapat mengelola ribuan data. Namun performa tergantung spesifikasi server.</p>
                </div>

                <div class="faq-item">
                  <h6>Q: Apakah bisa import data dari Excel?</h6>
                  <p>A: Saat ini belum tersedia fitur import. Anda perlu input manual atau hubungi administrator.</p>
                </div>

                <div class="faq-item">
                  <h6>Q: Format export PDF tidak sesuai, bagaimana?</h6>
                  <p>A: Pastikan browser Anda update terbaru. Gunakan Chrome atau Firefox untuk hasil terbaik.</p>
                </div>

                <div class="faq-item">
                  <h6>Q: Apakah ada history/riwayat perubahan data?</h6>
                  <p>A: Sistem menyimpan tanggal created_at. Untuk audit trail lengkap, hubungi administrator.</p>
                </div>

                <div class="faq-item">
                  <h6>Q: Bagaimana cara backup data?</h6>
                  <p>A: Export semua data ke Excel secara berkala. Administrator dapat backup database.</p>
                </div>

                <div class="faq-item">
                  <h6>Q: Apakah ada batasan akses user?</h6>
                  <p>A: Sistem saat ini belum memiliki role management. Semua user memiliki akses penuh.</p>
                </div>
              </div>
            </div>
          </section>

          <!-- Contact Support -->
          <section class="help-section">
            <div class="content-card contact-card">
              <div class="contact-header">
                <i class="fas fa-headset"></i>
                <h4>Butuh Bantuan Lebih Lanjut?</h4>
              </div>
              <p>Jika masalah Anda tidak terjawab di dokumentasi ini, silakan hubungi:</p>
              <div class="contact-info">
                <div class="contact-item">
                  <i class="fas fa-envelope"></i>
                  <div>
                    <strong>Email:</strong><br>
                    support@dppkbpm.com
                  </div>
                </div>
                <div class="contact-item">
                  <i class="fas fa-phone"></i>
                  <div>
                    <strong>Telepon:</strong><br>
                    (0511) 3252040
                  </div>
                </div>
                <div class="contact-item">
                  <i class="fas fa-clock"></i>
                  <div>
                    <strong>Jam Kerja:</strong><br>
                    Senin - Jumat, 08:00 - 16:00 WITA
                  </div>
                </div>
              </div>
            </div>
          </section>

        </div>
      </div>
    </div>
  </div>
</main>


<style>
/* Help Page Styles */
.main-content {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: 100vh;
  padding: 2rem 0;
}

.help-header {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  border-radius: 20px;
  padding: 2rem;
  margin-bottom: 2rem;
  color: white;
  box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.header-content {
  display: flex;
  align-items: center;
  margin-bottom: 1.5rem;
}

.header-icon {
  width: 70px;
  height: 70px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 1.5rem;
  font-size: 2rem;
}

.header-text h1 {
  margin: 0;
  font-size: 2rem;
  font-weight: 700;
}

.header-text p {
  margin: 0;
  opacity: 0.9;
}

.help-search {
  position: relative;
  max-width: 500px;
}

.help-search .search-input {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 12px 45px 12px 20px;
  border-radius: 25px;
}

.help-search .search-input::placeholder {
  color: rgba(255, 255, 255, 0.8);
}

.help-search .search-input:focus {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.5);
  outline: none;
}

.help-search .search-icon {
  position: absolute;
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
  color: rgba(255, 255, 255, 0.8);
}

/* Sidebar */
.help-sidebar {
  background: white;
  border-radius: 15px;
  padding: 1.5rem;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 100px;
}

.sidebar-title {
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e9ecef;
}

.help-nav {
  list-style: none;
  padding: 0;
  margin: 0;
}

.help-nav .nav-item {
  margin-bottom: 0.5rem;
}

.help-nav .nav-link {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  border-radius: 10px;
  color: #495057;
  text-decoration: none;
  transition: all 0.3s ease;
}

.help-nav .nav-link:hover {
  background: #f8f9fa;
  color: #2c3e50 0%, #34495e;
  transform: translateX(5px);
}

.help-nav .nav-item.active .nav-link {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e);
  color: white;
}

/* Content */
.help-content {
  background: white;
  border-radius: 15px;
  padding: 2rem;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.help-section {
  margin-bottom: 3rem;
  scroll-margin-top: 100px;
}

.section-title {
  color: #2c3e50;
  font-weight: 700;
  font-size: 1.75rem;
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 3px solid #2c3e50 0%, #34495e;
}

.content-card {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.content-card h5 {
  color: #2c3e50;
  font-weight: 600;
  margin-bottom: 1rem;
}

.content-card h6 {
  color: #495057;
  font-weight: 600;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
}

/* Lists */
.feature-list {
  list-style: none;
  padding: 0;
}

.feature-list li {
  padding: 0.5rem 0;
  font-weight: 500;
}

.step-list {
  padding-left: 1.5rem;
}

.step-list li {
  padding: 0.5rem 0;
  line-height: 1.6;
}

/* Info Boxes */
.info-box {
  display: flex;
  align-items: start;
  padding: 1rem;
  border-radius: 10px;
  margin: 1rem 0;
}

.info-box i {
  font-size: 1.5rem;
  margin-right: 1rem;
  flex-shrink: 0;
}

.info-box.info {
  background: #cfe2ff;
  border: 1px solid #0d6efd;
  color: #084298;
}

.info-box.warning {
  background: #fff3cd;
  border: 1px solid #ffc107;
  color: #856404;
}

.info-box.danger {
  background: #f8d7da;
  border: 1px solid #dc3545;
  color: #842029;
}

.info-box.success {
  background: #d1e7dd;
  border: 1px solid #28a745;
  color: #0f5132;
}

/* Accordion */
.accordion-item {
  border: 1px solid #e9ecef;
  border-radius: 10px !important;
  margin-bottom: 0.5rem;
  overflow: hidden;
}

.accordion-button {
  background: #f8f9fa;
  font-weight: 600;
}

.accordion-button:not(.collapsed) {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e);
  color: white;
}

/* FAQ */
.faq-list {
  padding: 0;
}

.faq-item {
  padding: 1.5rem;
  border-bottom: 1px solid #e9ecef;
}

.faq-item:last-child {
  border-bottom: none;
}

.faq-item h6 {
  color: #2c3e50 0%, #34495e;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.faq-item p {
  margin: 0;
  color: #495057;
  line-height: 1.6;
}

/* Contact Card */
.contact-card {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e);
  color: white;
  text-align: center;
}

.contact-header {
  margin-bottom: 1rem;
}

.contact-header i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.8;
}

.contact-header h4 {
  font-weight: 700;
  margin: 0;
}

.contact-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

.contact-item {
  background: rgba(255, 255, 255, 0.2);
  padding: 1.5rem;
  border-radius: 12px;
  text-align: left;
  display: flex;
  align-items: start;
}

</style>

<?php include "includes/footer.php"?>