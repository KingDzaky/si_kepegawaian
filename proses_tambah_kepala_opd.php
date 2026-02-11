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

$nama = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
$nip = mysqli_real_escape_string($koneksi, trim($_POST['nip']));
$pangkat = mysqli_real_escape_string($koneksi, trim($_POST['pangkat']));
$golongan = mysqli_real_escape_string($koneksi, trim($_POST['golongan']));
$jabatan = mysqli_real_escape_string($koneksi, trim($_POST['jabatan']));
$gelar_depan = mysqli_real_escape_string($koneksi, trim($_POST['gelar_depan']));
$gelar_belakang = mysqli_real_escape_string($koneksi, trim($_POST['gelar_belakang']));
$status = mysqli_real_escape_string($koneksi, $_POST['status']);
$tmt_jabatan = mysqli_real_escape_string($koneksi, $_POST['tmt_jabatan']);

// Jika status aktif, set semua yang lain jadi non-aktif
if ($status === 'aktif') {
    $update_query = "UPDATE kepala_opd SET status = 'non-aktif'";
    $koneksi->query($update_query);
}

$query = "INSERT INTO kepala_opd (nama, nip, pangkat, golongan, jabatan, gelar_depan, gelar_belakang, status, tmt_jabatan) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("sssssssss", $nama, $nip, $pangkat, $golongan, $jabatan, $gelar_depan, $gelar_belakang, $status, $tmt_jabatan);

// Execute statement
if ($stmt->execute()) {
    $insert_id = $stmt->insert_id;
    $stmt->close();
    $koneksi->close();
    
    // Log sukses
    error_log("✅ Data Kenaikan Pangkat berhasil ditambahkan - ID: $insert_id | Nama: $nama");
    
    // Redirect dengan alert sukses
    alertSuksesTambah('kepala_opd.php', "Data Kepala OPD $nama berhasil ditambahkan!");
    
} else {
    $error = $stmt->error;
    $stmt->close();
    $koneksi->close();
    
    // Log error
    error_log("❌ Error execute statement: $error");
    
    // Redirect dengan alert gagal
    alertGagal('form_tambah_kepala_opd.php', 'Gagal menyimpan data: ' . $error);
}

$stmt->close();
$koneksi->close();
exit;
?>