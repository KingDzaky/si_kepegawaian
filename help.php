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

<link rel="stylesheet" href="css/help.css">

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



<?php include "includes/footer.php"?>