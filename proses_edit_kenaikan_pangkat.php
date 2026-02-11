<?php
// proses_edit_duk.php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/alert_functions.php';

// Hanya superadmin dan admin yang bisa akses
if (!isAdmin()) {
    alertGagal('dashboard.php', 'Akses ditolak! Anda tidak memiliki izin.');
    exit;
}

$id = (int)$_POST['id'];

// Validasi ID
if ($id <= 0) {
    header('Location: kenaikan_pangkat.php?error=ID tidak valid');
    exit;
}

// Escape semua input
$nomor_usulan = mysqli_real_escape_string($koneksi, trim($_POST['nomor_usulan']));
$tanggal_usulan = mysqli_real_escape_string($koneksi, $_POST['tanggal_usulan']);
$jenis_kenaikan = mysqli_real_escape_string($koneksi, $_POST['jenis_kenaikan']);

// Data Pegawai
$nama = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
$kartu_pegawai = mysqli_real_escape_string($koneksi, trim($_POST['kartu_pegawai']));
$tempat_lahir = mysqli_real_escape_string($koneksi, trim($_POST['tempat_lahir']));
$pendidikan_terakhir = mysqli_real_escape_string($koneksi, $_POST['pendidikan_terakhir']);
$prodi = mysqli_real_escape_string($koneksi, trim($_POST['prodi']));

// Pangkat LAMA
$pangkat_lama = mysqli_real_escape_string($koneksi, trim($_POST['pangkat_lama']));
$golongan_lama = mysqli_real_escape_string($koneksi, $_POST['golongan_lama']);
$tmt_pangkat_lama = mysqli_real_escape_string($koneksi, $_POST['tmt_pangkat_lama']);
$masa_kerja_tahun_lama = (int)$_POST['masa_kerja_tahun_lama'];
$masa_kerja_bulan_lama = (int)$_POST['masa_kerja_bulan_lama'];
$gaji_pokok_lama = (float)str_replace(['.', ','], '', $_POST['gaji_pokok_lama']);
$jabatan_lama = mysqli_real_escape_string($koneksi, trim($_POST['jabatan_lama']));

// Pangkat BARU
$pangkat_baru = mysqli_real_escape_string($koneksi, trim($_POST['pangkat_baru']));
$golongan_baru = mysqli_real_escape_string($koneksi, $_POST['golongan_baru']);
$tmt_pangkat_baru = mysqli_real_escape_string($koneksi, $_POST['tmt_pangkat_baru']);
$masa_kerja_tahun_baru = (int)$_POST['masa_kerja_tahun_baru'];
$masa_kerja_bulan_baru = (int)$_POST['masa_kerja_bulan_baru'];
$gaji_pokok_baru = (float)str_replace(['.', ','], '', $_POST['gaji_pokok_baru']);
$jabatan_baru = mysqli_real_escape_string($koneksi, trim($_POST['jabatan_baru']));

// Masa Kerja
$mk_golongan_tahun = (int)$_POST['mk_golongan_tahun'];
$mk_golongan_bulan = (int)$_POST['mk_golongan_bulan'];
$mk_dari_sampai = mysqli_real_escape_string($koneksi, trim($_POST['mk_dari_sampai']));

// Atasan
$atasan_nama = mysqli_real_escape_string($koneksi, trim($_POST['atasan_nama']));
$atasan_nip = mysqli_real_escape_string($koneksi, trim($_POST['atasan_nip']));
$atasan_pangkat = mysqli_real_escape_string($koneksi, trim($_POST['atasan_pangkat']));
$atasan_jabatan = mysqli_real_escape_string($koneksi, trim($_POST['atasan_jabatan']));

// Wilayah & SKP
$wilayah_pembayaran = mysqli_real_escape_string($koneksi, trim($_POST['wilayah_pembayaran']));
$skp_tahun_1 = mysqli_real_escape_string($koneksi, trim($_POST['skp_tahun_1']));
$skp_nilai_1 = mysqli_real_escape_string($koneksi, trim($_POST['skp_nilai_1']));
$skp_tahun_2 = mysqli_real_escape_string($koneksi, trim($_POST['skp_tahun_2']));
$skp_nilai_2 = mysqli_real_escape_string($koneksi, trim($_POST['skp_nilai_2']));
$status = mysqli_real_escape_string($koneksi, $_POST['status']);

// Validasi field wajib
if (empty($nomor_usulan) || empty($tanggal_usulan) || empty($nama)) {
    header('Location: form_edit_kenaikan_pangkat.php?id=' . $id . '&error=Field wajib tidak boleh kosong');
    exit;
}

// =====================================================
// VALIDASI DUPLICATE NOMOR USULAN
// =====================================================
$check_query = "SELECT id, nomor_usulan FROM kenaikan_pangkat 
                WHERE nomor_usulan = ? AND id != ?";
$check_stmt = $koneksi->prepare($check_query);

if (!$check_stmt) {
    header('Location: form_edit_kenaikan_pangkat.php?id=' . $id . '&error=Gagal validasi nomor usulan: ' . $koneksi->error);
    exit;
}

$check_stmt->bind_param("si", $nomor_usulan, $id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $existing = $check_result->fetch_assoc();
    $check_stmt->close();
    $koneksi->close();
    
    $error_msg = "Nomor usulan '$nomor_usulan' sudah digunakan oleh data lain (ID: {$existing['id']}). Silakan gunakan nomor usulan yang berbeda.";
    header('Location: form_edit_kenaikan_pangkat.php?id=' . $id . '&error=' . urlencode($error_msg));
    exit;
}

$check_stmt->close();
// =====================================================

// Query UPDATE dengan nomor_usulan
$query = "UPDATE kenaikan_pangkat SET 
    nomor_usulan = ?,
    tanggal_usulan = ?,
    jenis_kenaikan = ?,
    nama = ?, 
    kartu_pegawai = ?, 
    tempat_lahir = ?,
    pendidikan_terakhir = ?, 
    prodi = ?, 
    pangkat_lama = ?, 
    golongan_lama = ?, 
    tmt_pangkat_lama = ?, 
    masa_kerja_tahun_lama = ?, 
    masa_kerja_bulan_lama = ?, 
    gaji_pokok_lama = ?, 
    jabatan_lama = ?,
    pangkat_baru = ?, 
    golongan_baru = ?, 
    tmt_pangkat_baru = ?, 
    masa_kerja_tahun_baru = ?, 
    masa_kerja_bulan_baru = ?, 
    gaji_pokok_baru = ?, 
    jabatan_baru = ?, 
    mk_golongan_tahun = ?, 
    mk_golongan_bulan = ?, 
    mk_dari_sampai = ?, 
    atasan_nama = ?, 
    atasan_nip = ?, 
    atasan_pangkat = ?, 
    atasan_jabatan = ?, 
    wilayah_pembayaran = ?, 
    skp_tahun_1 = ?, 
    skp_nilai_1 = ?, 
    skp_tahun_2 = ?, 
    skp_nilai_2 = ?, 
    status = ?
    WHERE id = ?";

$stmt = $koneksi->prepare($query);

if (!$stmt) {
    header('Location: form_edit_kenaikan_pangkat.php?id=' . $id . '&error=Gagal prepare statement: ' . $koneksi->error);
    exit;
}

// Total 36 parameter sekarang (ditambah nomor_usulan)
// Urutan type: s s s s s s s s s s s i i d s s s s i i d s i i s s s s s s s s s s s i
// Total: 27 s + 8 i + 2 d = 37 parameter (36 field + 1 id)

$stmt->bind_param(
    "sssssssssssiidssssiidssiissssssssssi",
    $nomor_usulan,             // 1  - s (BARU)
    $tanggal_usulan,           // 2  - s
    $jenis_kenaikan,           // 3  - s
    $nama,                     // 4  - s
    $kartu_pegawai,            // 5  - s
    $tempat_lahir,             // 6  - s
    $pendidikan_terakhir,      // 7  - s
    $prodi,                    // 8  - s
    $pangkat_lama,             // 9  - s
    $golongan_lama,            // 10 - s
    $tmt_pangkat_lama,         // 11 - s
    $masa_kerja_tahun_lama,    // 12 - i
    $masa_kerja_bulan_lama,    // 13 - i
    $gaji_pokok_lama,          // 14 - d
    $jabatan_lama,             // 15 - s
    $pangkat_baru,             // 16 - s
    $golongan_baru,            // 17 - s
    $tmt_pangkat_baru,         // 18 - s
    $masa_kerja_tahun_baru,    // 19 - i
    $masa_kerja_bulan_baru,    // 20 - i
    $gaji_pokok_baru,          // 21 - d
    $jabatan_baru,             // 22 - s
    $mk_golongan_tahun,        // 23 - i
    $mk_golongan_bulan,        // 24 - i
    $mk_dari_sampai,           // 25 - s
    $atasan_nama,              // 26 - s
    $atasan_nip,               // 27 - s
    $atasan_pangkat,           // 28 - s
    $atasan_jabatan,           // 29 - s
    $wilayah_pembayaran,       // 30 - s
    $skp_tahun_1,              // 31 - s
    $skp_nilai_1,              // 32 - s
    $skp_tahun_2,              // 33 - s
    $skp_nilai_2,              // 34 - s
    $status,                   // 35 - s
    $id                        // 36 - i (WHERE clause)
);

if ($stmt->execute()) {
    // ========== UPDATE BERHASIL ==========
    $stmt->close();
    $koneksi->close();
    
    // Log untuk debugging
    error_log("✅ Data Usulan Kenaikan berhasil diupdate - ID: $id | Nama: $nama");
    
    // Redirect dengan alert sukses
    alertSuksesUbah('kenaikan_pangkat.php', "Data Usulan Kenaikan Pangkat $nama berhasil diperbarui!");
    exit;
    
} else {
    // ========== UPDATE GAGAL ==========
    $error_message = $stmt->error;
    $stmt->close();
    $koneksi->close();
    
    // Log error
    error_log("❌ Error update kenaikan_pangkat - ID: $id | Error: $error_message");
    
    // Redirect dengan alert error
    alertGagal("form_edit_kenaikan_pangkat.php?id=$id", 'Gagal mengupdate data: ' . $error_message);
    exit;
}
?>