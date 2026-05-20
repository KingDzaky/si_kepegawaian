<?php
session_start();
require_once 'check_session.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
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
          <p>Panduan Penggunaan Sistem Administrasi Kepegawaian DPPKBPM</p>
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
              <a href="#hak-akses" class="nav-link">
                <i class="fas fa-shield-alt me-2"></i>Hak Akses &amp; Role
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
              <a href="#nonaktif-pegawai" class="nav-link">
                <i class="fas fa-user-slash me-2"></i>Nonaktifkan &amp; Aktifkan Pegawai
              </a>
            </li>
            <li class="nav-item">
              <a href="#hapus-data" class="nav-link">
                <i class="fas fa-trash-alt me-2"></i>Hapus Data (Soft &amp; Permanen)
              </a>
            </li>
            <li class="nav-item">
              <a href="#kenaikan-pangkat" class="nav-link">
                <i class="fas fa-arrow-up me-2"></i>Kenaikan Pangkat
              </a>
            </li>
            <li class="nav-item">
              <a href="#satya-lencana" class="nav-link">
                <i class="fas fa-medal me-2"></i>Satya Lencana
              </a>
            </li>
            <li class="nav-item">
              <a href="#usulan-pensiun" class="nav-link">
                <i class="fas fa-user-clock me-2"></i>Usulan Pensiun
              </a>
            </li>
            <li class="nav-item">
              <a href="#export-data" class="nav-link">
                <i class="fas fa-download me-2"></i>Export Data
              </a>
            </li>
            <li class="nav-item">
              <a href="#filter-search" class="nav-link">
                <i class="fas fa-filter me-2"></i>Filter &amp; Pencarian
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

          <!-- ============================================================ -->
          <!-- MEMULAI -->
          <!-- ============================================================ -->
          <section id="getting-started" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-rocket me-2"></i>Memulai
            </h2>
            <div class="content-card">
              <h5>Selamat Datang di Sistem Administrasi Kepegawaian</h5>
              <p>Sistem ini dirancang untuk memudahkan pengelolaan data pegawai DUK dan Penyuluh di DPPKBPM Kota Banjarmasin, mulai dari data kepegawaian, kenaikan pangkat, satya lencana, hingga pensiun.</p>

              <h6 class="mt-4">Fitur Utama:</h6>
              <ul class="feature-list">
                <li><i class="fas fa-check text-success me-2"></i>Manajemen Data DUK &amp; Penyuluh</li>
                <li><i class="fas fa-check text-success me-2"></i>Usulan Kenaikan Pangkat</li>
                <li><i class="fas fa-check text-success me-2"></i>Satya Lencana (otomatis dari kenaikan pangkat)</li>
                <li><i class="fas fa-check text-success me-2"></i>Usulan Pensiun dengan notifikasi WA</li>
                <li><i class="fas fa-check text-success me-2"></i>Export PDF &amp; Excel</li>
                <li><i class="fas fa-check text-success me-2"></i>Nonaktifkan &amp; reaktifkan pegawai</li>
                <li><i class="fas fa-check text-success me-2"></i>Soft delete &amp; hapus permanen</li>
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

          <!-- ============================================================ -->
          <!-- HAK AKSES & ROLE -->
          <!-- ============================================================ -->
          <section id="hak-akses" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-shield-alt me-2"></i>Hak Akses &amp; Role
            </h2>
            <div class="content-card">
              <p>Sistem memiliki <strong>3 role pengguna</strong> dengan hak akses yang berbeda-beda.</p>

              <!-- Tabel perbandingan role -->
              <div class="table-responsive mt-3">
                <table class="table table-bordered table-sm" style="font-size:13px;">
                  <thead style="background:#2c3e50;color:white;">
                    <tr>
                      <th>Fitur</th>
                      <th class="text-center">Superadmin</th>
                      <th class="text-center">Admin</th>
                      <th class="text-center">Kepala Dinas</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>Lihat Data DUK &amp; Penyuluh</td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-danger"><i class="fas fa-times-circle"></i></td>
                    </tr>
                    <tr>
                      <td>Melihat Laporan Kenaikan Pangkat &amp; Laporan Pensiun </td>
                      <td class="text-center text-danger"><i class="fas fa-times-circle"></i></td>
                      <td class="text-center text-danger"><i class="fas fa-times-circle"></i></td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                    </tr>
                    <tr>
                      <td>Approval Usulan Kenaikan Pangkat &amp; Usulan Pensiun </td>
                      <td class="text-center text-danger"><i class="fas fa-times-circle"></i></td>
                      <td class="text-center text-danger"><i class="fas fa-times-circle"></i></td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                    </tr>
                    <tr>
                      <td>Tambah / Edit / Hapus DUK &amp; Penyuluh</td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-danger"><i class="fas fa-times-circle"></i></td>
                    </tr>
                    <tr>
                      <td>Lihat Usulan Pensiun</td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                    </tr>
                    <tr>
                      <td>Tambah / Edit / Hapus Usulan Pensiun</td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-danger"><i class="fas fa-times-circle"></i></td>
                    </tr>
                    <tr>
                      <td>Lihat Usulan Pensiun</td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                    </tr>
                    <tr>
                      <td>Kelola Kenaikan Pangkat</td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-danger"><i class="fas fa-times-circle"></i></td>
                    </tr>
                    <tr>
                      <td>Nonaktifkan Pegawai</td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-danger"><i class="fas fa-times-circle"></i></td>
                    </tr>
                    <tr>
                      <td><strong>Reaktifkan Pegawai</strong></td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-danger"><i class="fas fa-times-circle"></i></td>
                      <td class="text-center text-danger"><i class="fas fa-times-circle"></i></td>
                    </tr>
                    <tr>
                      <td>Hapus Permanen (dari Recycle Bin)</td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-danger"><i class="fas fa-times-circle"></i></td>
                      <td class="text-center text-danger"><i class="fas fa-times-circle"></i></td>
                    </tr>
                    <tr>
                      <td>Kelola Data User atau Pengguna</td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-danger"><i class="fas fa-times-circle"></i></td>
                      <td class="text-center text-danger"><i class="fas fa-times-circle"></i></td>
                    </tr>
                    <tr>
                      <td>Export PDF &amp; Excel</td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                      <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="info-box warning mt-3">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                  <strong>Catatan:</strong> Kepala Dinas hanya memiliki akses <em>lihat</em> dan <em>export</em>. Semua aksi ubah data hanya bisa dilakukan oleh Admin atau Superadmin.
                </div>
              </div>

              <div class="info-box danger mt-3">
                <i class="fas fa-crown"></i>
                <div>
                  <strong>Superadmin Eksklusif:</strong> Hanya Superadmin yang dapat mengaktifkan kembali pegawai yang dinonaktifkan dan menghapus data secara permanen dari Recycle Bin.
                </div>
              </div>
            </div>
          </section>

          <!-- ============================================================ -->
          <!-- DATA DUK -->
          <!-- ============================================================ -->
          <section id="duk-management" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-users me-2"></i>Kelola Data DUK
            </h2>
            <div class="content-card">
              <h5>Menambah Data DUK</h5>
              <ol class="step-list">
                <li>Klik menu <strong>"Tambah DUK"</strong> di sidebar atau tombol <strong>"+ Tambah Data"</strong> di halaman Data DUK.</li>
                <li>Isi formulir dengan data lengkap:
                  <ul>
                    <li><strong>Informasi Personal:</strong> Nama lengkap, NIP (18 digit), Kartu Pegawai, TTL, Jenis Kelamin, Pendidikan, Program Studi, Nomor WhatsApp.</li>
                    <li><strong>Informasi OPD:</strong> Pilih Kepala OPD yang aktif dari dropdown.</li>
                    <li><strong>Kepegawaian:</strong> Pangkat, Golongan, TMT Pangkat, TMT Pangkat Awal (opsional), Jabatan, Eselon, TMT Eselon/Jabatan.</li>
                  </ul>
                </li>
                <li>Untuk eselon <strong>Non-Eselon</strong>, akan muncul pilihan tambahan:
                  <ul>
                    <li><strong>JFT</strong> (Jabatan Fungsional Tertentu) → pilih Tingkat JFT</li>
                    <li><strong>JFU</strong> (Jabatan Fungsional Umum) → pilih Kelas JFU</li>
                  </ul>
                </li>
                <li>Field bertanda <span class="text-danger">*</span> wajib diisi.</li>
                <li>Klik <strong>"Simpan Data"</strong>.</li>
              </ol>

              <div class="info-box warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                  <strong>Perhatian:</strong> NIP harus 18 digit angka. Nomor WA harus format 08xxx atau 628xxx. Sistem akan menolak jika NIP, Nomor WA, atau Kartu Pegawai sudah terdaftar.
                </div>
              </div>

              <h5 class="mt-4">Mengedit Data DUK</h5>
              <ol class="step-list">
                <li>Buka halaman <strong>"Data DUK"</strong>.</li>
                <li>Cari data yang ingin diedit.</li>
                <li>Klik tombol <button class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></button> (Edit).</li>
                <li>Ubah data yang diperlukan, lalu klik <strong>"Update Data"</strong>.</li>
              </ol>

              <h5 class="mt-4">Menghapus Data DUK</h5>
              <p>Lihat bagian <a href="#hapus-data"><strong>Hapus Data (Soft &amp; Permanen)</strong></a> untuk penjelasan lengkap.</p>
            </div>
          </section>

          <!-- ============================================================ -->
          <!-- DATA PENYULUH -->
          <!-- ============================================================ -->
          <section id="penyuluh-management" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-chalkboard-teacher me-2"></i>Kelola Data Penyuluh
            </h2>
            <div class="content-card">
              <h5>Menambah Data Penyuluh</h5>
              <ol class="step-list">
                <li>Klik menu <strong>"Data Penyuluh"</strong> di sidebar.</li>
                <li>Klik tombol <strong>"+ Tambah Penyuluh Baru"</strong>.</li>
                <li>Isi formulir dengan data penyuluh (mirip DUK, tanpa field Eselon).</li>
                <li>Klik <strong>"Simpan Data"</strong>.</li>
              </ol>

              <div class="info-box info">
                <i class="fas fa-info-circle"></i>
                <div>
                  <strong>Perbedaan Penyuluh &amp; DUK:</strong> Data penyuluh tidak memiliki field Eselon dan TMT Eselon karena jabatan fungsional penyuluh memiliki struktur tersendiri.
                </div>
              </div>
            </div>
          </section>

          <!-- ============================================================ -->
          <!-- NONAKTIFKAN & AKTIFKAN PEGAWAI -->
          <!-- ============================================================ -->
          <section id="nonaktif-pegawai" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-user-slash me-2"></i>Nonaktifkan &amp; Aktifkan Pegawai
            </h2>
            <div class="content-card">
              <h5>Menonaktifkan Pegawai</h5>
              <p>Fitur ini digunakan ketika pegawai cuti panjang, pensiun sementara, atau kondisi lain yang mengharuskan status pegawai dinonaktifkan tanpa menghapus datanya.</p>
              <ol class="step-list">
                <li>Buka halaman <strong>"Data DUK"</strong>.</li>
                <li>Cari pegawai yang akan dinonaktifkan.</li>
                <li>Klik tombol <strong>"Nonaktifkan"</strong> (ikon <i class="fas fa-user-slash text-warning"></i>).</li>
                <li>Isi alasan penonaktifan dan keterangan tambahan.</li>
                <li>Konfirmasi tindakan.</li>
              </ol>
              <div class="info-box warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                  <strong>Efek Nonaktif:</strong> Pegawai yang dinonaktifkan <em>tidak dapat</em> diusulkan kenaikan pangkat atau pensiun. Sistem akan menampilkan peringatan jika NIP pegawai nonaktif dimasukkan ke dalam form usulan.
                </div>
              </div>

              <h5 class="mt-4">Mengaktifkan Kembali Pegawai</h5>
              <div class="info-box danger">
                <i class="fas fa-crown"></i>
                <div>
                  <strong>Hanya Superadmin</strong> yang dapat mengaktifkan kembali pegawai yang dinonaktifkan. Admin tidak memiliki akses ini.
                </div>
              </div>
              <ol class="step-list">
                <li>Login sebagai <strong>Superadmin</strong>.</li>
                <li>Buka halaman <strong>"Data DUK"</strong> dan tampilkan filter <strong>"Nonaktif"</strong>.</li>
                <li>Cari pegawai yang akan diaktifkan kembali.</li>
                <li>Klik tombol <strong>"Reaktifkan"</strong> (ikon <i class="fas fa-user-check text-success"></i>).</li>
                <li>Isi keterangan reaktifasi, lalu konfirmasi.</li>
              </ol>
              <div class="info-box info">
                <i class="fas fa-info-circle"></i>
                <div>
                  <strong>Log Tercatat:</strong> Setiap aksi nonaktif dan reaktif dicatat di <code>deleted_records_log</code> beserta waktu dan siapa yang melakukan.
                </div>
              </div>
            </div>
          </section>

          <!-- ============================================================ -->
          <!-- HAPUS DATA -->
          <!-- ============================================================ -->
          <section id="hapus-data" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-trash-alt me-2"></i>Hapus Data (Soft Delete &amp; Hapus Permanen)
            </h2>
            <div class="content-card">
              <h5>Apa itu Soft Delete?</h5>
              <p>Saat Anda menekan tombol hapus, data <strong>tidak langsung dihapus</strong> dari database. Data dipindahkan ke <strong>Recycle Bin</strong> (ditandai dengan <code>deleted_at</code>). Data ini tidak muncul di daftar utama tetapi masih bisa dipulihkan.</p>

              <h5 class="mt-4">Cara Menghapus Data (Soft Delete)</h5>
              <ol class="step-list">
                <li>Buka halaman <strong>"Data DUK"</strong> atau halaman data lainnya.</li>
                <li>Klik tombol <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button> (Hapus) pada baris data yang ingin dihapus.</li>
                <li>Konfirmasi penghapusan pada dialog yang muncul.</li>
                <li>Data berpindah ke <strong>Recycle Bin</strong>.</li>
              </ol>

              <h5 class="mt-4">Memulihkan Data dari Recycle Bin</h5>
              <ol class="step-list">
                <li>Buka menu <strong>"Recycle Bin"</strong> di sidebar.</li>
                <li>Cari data yang ingin dipulihkan.</li>
                <li>Klik tombol <strong>"Pulihkan"</strong> (ikon <i class="fas fa-trash-restore text-success"></i>).</li>
                <li>Data kembali muncul di daftar utama.</li>
              </ol>

              <h5 class="mt-4">Hapus Permanen</h5>
              <div class="info-box danger">
                <i class="fas fa-crown"></i>
                <div>
                  <strong>Hanya Superadmin</strong> yang dapat menghapus data secara permanen. Admin hanya bisa melakukan soft delete.
                </div>
              </div>
              <ol class="step-list">
                <li>Login sebagai <strong>Superadmin</strong>.</li>
                <li>Buka menu <strong>"Recycle Bin"</strong>.</li>
                <li>Klik tombol <strong>"Hapus Permanen"</strong> pada data yang ingin dihapus selamanya.</li>
                <li>Konfirmasi. Data tidak dapat dikembalikan setelah ini.</li>
              </ol>

              <div class="info-box warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                  <strong>Perhatian:</strong> Data di Recycle Bin otomatis terjadwal untuk dihapus permanen setelah <strong>5 tahun</strong> (sesuai konfigurasi sistem).
                </div>
              </div>
            </div>
          </section>

          <!-- ============================================================ -->
          <!-- KENAIKAN PANGKAT -->
          <!-- ============================================================ -->
          <section id="kenaikan-pangkat" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-arrow-up me-2"></i>Kenaikan Pangkat
            </h2>
            <div class="content-card">
              <h5>Membuat Usulan Kenaikan Pangkat</h5>
              <ol class="step-list">
                <li>Buka menu <strong>"Usulan Kenaikan Pangkat"</strong> di sidebar.</li>
                <li>Klik <strong>"+ Tambah Usulan"</strong>.</li>
                <li>Masukkan <strong>NIP pegawai</strong> di kolom pencarian, lalu klik <strong>"Cari"</strong>. Sistem akan otomatis mengisi data pegawai dari DUK.</li>
                <li>Periksa dan lengkapi data yang belum terisi:
                  <ul>
                    <li><strong>Pangkat Baru &amp; Golongan Baru</strong> — pilih sesuai kenaikan.</li>
                    <li><strong>TMT Pangkat Baru</strong> — tanggal mulai berlaku pangkat baru.</li>
                    <li><strong>Masa Kerja Baru</strong> — otomatis dihitung, bisa disesuaikan.</li>
                    <li><strong>Jabatan Baru</strong> — isi jika ada perubahan jabatan.</li>
                    <li><strong>Gaji Pokok Lama &amp; Baru</strong> — masukkan sesuai tabel gaji.</li>
                    <li><strong>Jenis Kenaikan</strong> — Reguler, Pilihan, Anumerta, atau Pengabdian.</li>
                    <li><strong>SKP Tahun 1 &amp; 2</strong> — nilai SKP dua tahun terakhir.</li>
                    <li><strong>Masa Kerja Golongan</strong> — dihitung dari TMT pangkat lama.</li>
                    <li><strong>Wilayah Pembayaran</strong> — isi sesuai data pembayaran gaji.</li>
                  </ul>
                </li>
                <li>Pilih <strong>Status</strong>:
                  <ul>
                    <li><strong>Draft</strong> — disimpan tapi belum diajukan.</li>
                    <li><strong>Diajukan</strong> — siap untuk diproses.</li>
                  </ul>
                </li>
                <li>Klik <strong>"Simpan Usulan"</strong>.</li>
              </ol>

              <div class="info-box info">
                <i class="fas fa-magic"></i>
                <div>
                  <strong>Auto-fill:</strong> Saat NIP dimasukkan dan data ditemukan, sistem otomatis mengisi nama, pangkat lama, golongan lama, TMT pangkat, masa kerja, jabatan, dan data atasan (Kepala OPD)
                  Jika data masa kerja tidak sesuai, bisa disesuaikan dengan SK Terkait.
                </div>
              </div>

              <h5 class="mt-4">Mengubah Status Usulan</h5>
              <ol class="step-list">
                <li>Buka daftar usulan kenaikan pangkat.</li>
                <li>Klik tombol <strong>Edit</strong> pada usulan yang diinginkan.</li>
                <li>Ubah status menjadi <strong>"Disetujui"</strong> atau <strong>"Ditolak"</strong> atau bisa diapprove oleh Kepala Dinas jika menggunakan akun Kepala Dinas .</li>
                <li>Isi keterangan jika perlu, lalu simpan.</li>
              </ol>

              <ul class="feature-list">
                <li><span class="badge bg-secondary me-2">Draft</span> — Usulan tersimpan belum diajukan.</li>
                <li><span class="badge bg-info me-2">Diajukan</span> — Sudah diajukan, menunggu persetujuan.</li>
                <li><span class="badge bg-success me-2">Disetujui</span> — Usulan disetujui, reminder WA aktif.</li>
                <li><span class="badge bg-danger me-2">Ditolak</span> — Usulan ditolak.</li>
              </ul>

              <ul class="feature-list">
                <li><i class="fas fa-bell text-info me-2"></i><strong>Reminder Usulan Kenaikan Pangkat</strong> — muncul saat status usulan sudah disetujui.</li>
              </ul>

              <h5 class="mt-4">Update Data DUK</h5>
              <ol class="step-list">
                <li>Setelah usulan berstatus <strong>Disetujui</strong> otomatis memperbarui data DUK dan tombol Notifikasi Whattsap &amp; Reminder akan muncul .</li>
                <li>Klik tombol <strong>Notifikasi Dan Reminder</strong>.</li>
                <li>Konfirmasi. Sistem akan <strong>Menampilkan Export Daftar Usul Mutasi Kenaikan Pangkat</strong> pegawai (pangkat, golongan, TMT pangkat, jabatan) sesuai data usulan yang disetujui.</li>
              </ol>

              <div class="info-box warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                  <strong>Penting:</strong> Tombol "Notifikasi &amp; Reminder" hanya muncul jika status usulan sudah <em>Disetujui</em>. Pastikan data sudah benar sebelum dikonfirmasi karena data DUK akan berubah otomatis.
                </div>
              </div>
            </div>
          </section>

          <!-- ============================================================ -->
          <!-- SATYA LENCANA -->
          <!-- ============================================================ -->
          <section id="satya-lencana" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-medal me-2"></i>Satya Lencana
            </h2>
            <div class="content-card">
              <h5>Apa itu Satya Lencana?</h5>
              <p>Satya Lencana adalah penghargaan masa kerja pegawai ASN. Sistem secara <strong>otomatis mengkalkulasi</strong> kelayakan Satya Lencana berdasarkan data masa kerja yang ada di riwayat kenaikan pangkat.</p>

              <h5 class="mt-4">Cara Kerja Satya Lencana</h5>
              <div class="info-box info">
                <i class="fas fa-info-circle"></i>
                <div>
                  <strong>Otomatis dari Kenaikan Pangkat:</strong> Satya Lencana <em>tidak perlu diinput manual</em>. Sistem membaca data masa kerja dari riwayat kenaikan pangkat pegawai dan menentukan jenis Satya Lencana yang layak diterima.
                </div>
              </div>
              <ul class="feature-list mt-3">
                <li><i class="fas fa-star text-warning me-2"></i><strong>Karya Satya 10 Tahun</strong> — masa kerja ≥ 10 tahun</li>
                <li><i class="fas fa-star text-warning me-2"></i><strong>Karya Satya 20 Tahun</strong> — masa kerja ≥ 20 tahun</li>
                <li><i class="fas fa-star text-warning me-2"></i><strong>Karya Satya 30 Tahun</strong> — masa kerja ≥ 30 tahun</li>
              </ul>

              <h5 class="mt-4">Melihat Data Satya Lencana</h5>
              <ol class="step-list">
                <li>Buka menu <strong>"Usulan Satya Lencana"</strong> di sidebar.</li>
                <li>Sistem menampilkan daftar pegawai yang layak menerima Satya Lencana beserta jenis dan status pengajuannya.</li>
                <li>Gunakan filter untuk melihat berdasarkan jenis Satya Lencana atau status.</li>
              </ol>

              <h5 class="mt-4">Export Berkas Satya Lencana</h5>
              <ol class="step-list">
                <li>Pada daftar Satya Lencana, klik tombol <strong>"Export"</strong> pada baris pegawai yang diinginkan.</li>
                <li>Pilih format <strong>PDF</strong> untuk dokumen resmi atau <strong>Excel</strong> untuk data.</li>
              </ol>
            </div>
          </section>

          <!-- ============================================================ -->
          <!-- USULAN PENSIUN -->
          <!-- ============================================================ -->
          <section id="usulan-pensiun" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-user-clock me-2"></i>Usulan Pensiun
            </h2>
            <div class="content-card">
              <h5>Membuat Usulan Pensiun</h5>
              <ol class="step-list">
                <li>Buka menu <strong>"Usulan Pensiun Pegawai"</strong> di sidebar.</li>
                <li>Klik <strong>"+ Tambah Usulan"</strong>.</li>
                <li>Pilih <strong>Sumber Data</strong>: DUK atau Penyuluh.</li>
                <li>Masukkan <strong>NIP atau nama</strong> pegawai. Data akan terisi otomatis dari database.</li>
                <li>Lengkapi data yang belum terisi:
                  <ul>
                    <li><strong>Nomor Usulan</strong> — format otomatis, bisa disesuaikan.</li>
                    <li><strong>Tanggal Pensiun</strong> — dihitung otomatis dari tanggal lahir (usia 60 tahun), bisa diubah manual.</li>
                    <li><strong>Jenis Pensiun</strong> — BUP (Batas Usia Pensiun), Dini, Janda/Duda, dll.</li>
                    <li><strong>Nomor WA</strong> — untuk notifikasi reminder otomatis.</li>
                  </ul>
                </li>
                <li>Klik <strong>"Simpan Usulan"</strong>.</li>
              </ol>

              <h5 class="mt-4">Status Usulan Pensiun</h5>
              <ul class="feature-list">
                <li><span class="badge bg-secondary me-2">Draft</span> — Usulan tersimpan belum diajukan.</li>
                <li><span class="badge bg-info me-2">Diajukan</span> — Sudah diajukan, menunggu persetujuan.</li>
                <li><span class="badge bg-success me-2">Disetujui</span> — Usulan disetujui, reminder WA aktif.</li>
                <li><span class="badge bg-danger me-2">Ditolak</span> — Usulan ditolak.</li>
              </ul>

              <h5 class="mt-4">Sistem Reminder WhatsApp</h5>
              <p>Setelah status usulan menjadi <strong>Disetujui</strong>, sistem akan menampilkan tombol reminder otomatis:</p>
              <ul class="feature-list">
                <li><i class="fas fa-bell text-info me-2"></i><strong>Reminder 1 Tahun</strong> — muncul saat ±15 hari dari 365 hari sebelum pensiun.</li>
                <li><i class="fas fa-bell text-warning me-2"></i><strong>Reminder 1 Bulan</strong> — muncul saat ±15 hari dari 30 hari sebelum pensiun.</li>
                <li><i class="fas fa-bell text-danger me-2"></i><strong>Reminder 1 Minggu</strong> — muncul saat 3–10 hari sebelum pensiun.</li>
              </ul>
              <div class="info-box info">
                <i class="fas fa-info-circle"></i>
                <div>
                  <strong>Status Reminder:</strong> Badge di kolom Reminder menunjukkan apakah reminder sudah terkirim (hijau ✓) atau belum (kuning/merah jam). Di luar window waktu reminder, sistem menampilkan info sisa waktu (abu-abu).
                </div>
              </div>

              <h5 class="mt-4">Filter Tab Usulan Pensiun</h5>
              <ul class="feature-list">
                <li><strong>Semua</strong> — tampilkan semua usulan.</li>
                <li><strong>Reminder 1 Tahun / 1 Bulan / 1 Minggu</strong> — filter pegawai yang masuk window reminder.</li>
                <li><strong>DUK / Penyuluh</strong> — filter berdasarkan sumber data.</li>
                <li><strong>Disetujui</strong> — filter hanya yang sudah disetujui.</li>
              </ul>

              <h5 class="mt-4">Export Berkas Pensiun</h5>
              <ol class="step-list">
                <li>Pada kolom <strong>Aksi</strong>, klik tombol <strong>"Semua"</strong> untuk export semua berkas.</li>
                <li>Untuk Penyuluh, tersedia tombol <strong>"PKB"</strong> (Surat Pengantar PKB).</li>
                <li>Untuk DUK, tersedia tombol <strong>"Pengantar"</strong> dan <strong>"Pernyataan"</strong> secara terpisah.</li>
              </ol>
            </div>
          </section>

          <!-- ============================================================ -->
          <!-- EXPORT DATA -->
          <!-- ============================================================ -->
          <section id="export-data" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-download me-2"></i>Export Data
            </h2>
            <div class="content-card">
              <h5>Export Data ke PDF / Excel</h5>
              <ol class="step-list">
                <li>Buka halaman data yang ingin diekspor (Data DUK, Penyuluh, Kenaikan Pangkat, Laporan Pensiun, dll).</li>
                <li>Terapkan filter jika ingin export data spesifik.</li>
                <li>Klik tombol <strong>"Export Excel"</strong> atau <strong>"Export PDF"</strong>.</li>
                <li>File akan otomatis terunduh.</li>
              </ol>

              <div class="info-box info">
                <i class="fas fa-lightbulb"></i>
                <div>
                  <strong>Tips:</strong> Halaman <strong>Laporan Usulan Pensiun</strong> dan <strong>Laporan Kenaikan Pangkat</strong> mendukung filter tahun dan status sebelum export.
                </div>
              </div>
            </div>
          </section>

          <!-- ============================================================ -->
          <!-- FILTER & PENCARIAN -->
          <!-- ============================================================ -->
          <section id="filter-search" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-filter me-2"></i>Filter &amp; Pencarian
            </h2>
            <div class="content-card">
              <h5>Quick Search</h5>
              <p>Gunakan kotak pencarian di atas tabel. Ketik nama, NIP, atau nomor usulan — hasil tampil secara real-time tanpa reload halaman.</p>

              <h5 class="mt-4">Filter Tab</h5>
              <p>Klik tab filter (Semua, Reminder, DUK, Penyuluh, Disetujui, dll) untuk mempersempit data yang ditampilkan. Filter tab dan quick search bisa dikombinasikan.</p>

              <h5 class="mt-4">Reset Filter</h5>
              <p>Klik tombol <strong>"Reset Pencarian"</strong> atau tab <strong>"Semua"</strong> untuk kembali menampilkan semua data.</p>

              <div class="info-box success">
                <i class="fas fa-check-circle"></i>
                <div>
                  <strong>Fitur:</strong> Filter tab dan pencarian teks berjalan bersamaan. Misalnya: filter tab "Disetujui" + ketik nama pegawai → hanya menampilkan pegawai disetujui dengan nama tersebut.
                </div>
              </div>
            </div>
          </section>

          <!-- ============================================================ -->
          <!-- TROUBLESHOOTING -->
          <!-- ============================================================ -->
          <section id="troubleshooting" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-tools me-2"></i>Troubleshooting
            </h2>
            <div class="content-card">
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
                        <li>Refresh halaman (F5).</li>
                        <li>Pastikan tidak ada filter aktif — klik <strong>"Reset Pencarian"</strong>.</li>
                        <li>Cek koneksi database dengan administrator.</li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#problem2">
                      Badge reminder masih 0 meski sudah terkirim
                    </button>
                  </h2>
                  <div id="problem2" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                    <div class="accordion-body">
                      <strong>Solusi:</strong>
                      <ul>
                        <li>Refresh halaman setelah kirim reminder.</li>
                        <li>Pastikan tidak ada JavaScript error di browser (tekan F12 → Console).</li>
                        <li>Badge hanya menghitung pegawai yang masuk <em>window waktu</em> reminder (sekitar 1 tahun/1 bulan/1 minggu sebelum pensiun).</li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#problem3">
                      NIP pegawai tidak ditemukan saat buat usulan kenaikan pangkat
                    </button>
                  </h2>
                  <div id="problem3" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                    <div class="accordion-body">
                      <strong>Solusi:</strong>
                      <ul>
                        <li>Pastikan NIP yang dimasukkan tepat 18 digit.</li>
                        <li>Cek apakah pegawai sudah terdaftar di Data DUK.</li>
                        <li>Pastikan status pegawai <em>aktif</em> — pegawai nonaktif tidak bisa diusulkan.</li>
                        <li>Pastikan pegawai belum memiliki usulan aktif (diajukan/disetujui).</li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#problem4">
                      Tombol "Reaktifkan" tidak muncul
                    </button>
                  </h2>
                  <div id="problem4" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                    <div class="accordion-body">
                      <strong>Solusi:</strong>
                      <ul>
                        <li>Tombol Reaktifkan hanya tampil untuk login <strong>Superadmin</strong>.</li>
                        <li>Pastikan Anda login dengan akun Superadmin, bukan Admin biasa.</li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#problem5">
                      Export PDF / Excel gagal
                    </button>
                  </h2>
                  <div id="problem5" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                    <div class="accordion-body">
                      <strong>Solusi:</strong>
                      <ul>
                        <li>Pastikan koneksi internet stabil.</li>
                        <li>Gunakan browser Chrome atau Firefox terbaru.</li>
                        <li>Hapus cache browser (Ctrl+Shift+Delete).</li>
                        <li>Gunakan alternatif Print (Ctrl+P) dari browser.</li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#problem6">
                      Tampilan tabel tidak pas saat zoom in / zoom out
                    </button>
                  </h2>
                  <div id="problem6" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                    <div class="accordion-body">
                      <strong>Solusi:</strong>
                      <ul>
                        <li>Gunakan zoom browser standar (100%). Tekan <strong>Ctrl+0</strong> untuk reset zoom.</li>
                        <li>Tabel mendukung scroll horizontal jika konten terlalu lebar.</li>
                        <li>Gunakan browser Chrome atau Firefox untuk tampilan terbaik.</li>
                      </ul>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </section>

          <!-- ============================================================ -->
          <!-- FAQ -->
          <!-- ============================================================ -->
          <section id="faq" class="help-section">
            <h2 class="section-title">
              <i class="fas fa-question me-2"></i>FAQ
            </h2>
            <div class="content-card">
              <div class="faq-list">

                <div class="faq-item">
                  <h6>Q: Apakah data yang dihapus bisa dikembalikan?</h6>
                  <p>A: Ya, data yang dihapus masuk ke <strong>Recycle Bin</strong> dan bisa dipulihkan kapan saja. Hanya Superadmin yang bisa menghapus permanen dari Recycle Bin.</p>
                </div>

                <div class="faq-item">
                  <h6>Q: Bedanya nonaktifkan pegawai dengan hapus data?</h6>
                  <p>A: <strong>Nonaktifkan</strong> membuat pegawai tidak bisa diusulkan apapun tapi data tetap aktif di DUK. <strong>Hapus</strong> memindahkan data ke Recycle Bin dan data tidak muncul di daftar utama.</p>
                </div>

                <div class="faq-item">
                  <h6>Q: Mengapa Satya Lencana tidak perlu diinput manual?</h6>
                  <p>A: Sistem menghitung kelayakan Satya Lencana otomatis dari akumulasi masa kerja di riwayat kenaikan pangkat pegawai.</p>
                </div>

                <div class="faq-item">
                  <h6>Q: Apakah data DUK otomatis berubah setelah kenaikan pangkat disetujui?</h6>
                  <p>A: Tidak otomatis saat disetujui. Perubahan data DUK baru terjadi setelah Admin/Superadmin menekan tombol <strong>"SK Terbit"</strong> yang menandakan SK fisik sudah diterbitkan.</p>
                </div>

                <div class="faq-item">
                  <h6>Q: Bisakah Admin mengaktifkan kembali pegawai nonaktif?</h6>
                  <p>A: Tidak. Hanya <strong>Superadmin</strong> yang dapat mengaktifkan kembali pegawai nonaktif.</p>
                </div>

                <div class="faq-item">
                  <h6>Q: Apa yang terjadi jika reminder WA gagal terkirim?</h6>
                  <p>A: Sistem akan menampilkan pesan error. Badge reminder tetap menunjukkan "Belum Terkirim" sehingga Anda bisa mencoba kirim ulang.</p>
                </div>

                <div class="faq-item">
                  <h6>Q: Apakah ada batasan akses untuk Kepala Dinas?</h6>
                  <p>A: Ya. Kepala Dinas hanya bisa <strong>melihat</strong> data dan <strong>mengekspor</strong> laporan. Tidak bisa tambah, edit, hapus, nonaktifkan, atau mengusulkan apapun.</p>
                </div>

                <div class="faq-item">
                  <h6>Q: Apakah bisa import data dari Excel?</h6>
                  <p>A: Saat ini belum tersedia fitur import massal. Data harus diinput satu per satu atau hubungi administrator untuk import via database langsung.</p>
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
                    Senin – Jumat, 08:00 – 16:00 WITA
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

<?php include "includes/footer.php"; ?>