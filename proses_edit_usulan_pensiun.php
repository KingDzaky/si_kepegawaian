<?php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/alert_functions.php';

// Hanya superadmin dan admin yang bisa akses
if (!isAdmin()) {
    alertGagal('usulan_pensiun.php', 'Akses ditolak! Anda tidak memiliki izin.');
    exit;
}

// Validasi method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alertGagal('usulan_pensiun.php', 'Metode request tidak valid!');
    exit;
}

// Ambil dan validasi ID
$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    alertGagal('usulan_pensiun.php', 'ID tidak valid!');
    exit;
}

// Escape semua input
$nomor_usulan = mysqli_real_escape_string($koneksi, trim($_POST['nomor_usulan'] ?? ''));
$tanggal_usulan = mysqli_real_escape_string($koneksi, $_POST['tanggal_usulan'] ?? '');
$jenis_pensiun = mysqli_real_escape_string($koneksi, $_POST['jenis_pensiun'] ?? 'BUP');
$alasan = mysqli_real_escape_string($koneksi, trim($_POST['alasan'] ?? ''));
$status = mysqli_real_escape_string($koneksi, $_POST['status'] ?? 'draft');
$keterangan = mysqli_real_escape_string($koneksi, trim($_POST['keterangan'] ?? ''));

// Validasi field wajib
if (empty($nomor_usulan)) {
    alertGagal("form_edit_usulan_pensiun.php?id=$id", 'Nomor usulan tidak boleh kosong!');
    exit;
}

if (empty($tanggal_usulan)) {
    alertGagal("form_edit_usulan_pensiun.php?id=$id", 'Tanggal usulan tidak boleh kosong!');
    exit;
}

// =====================================================
// VALIDASI DUPLICATE NOMOR USULAN
// =====================================================
$check_query = "SELECT id, nomor_usulan FROM usulan_pensiun 
                WHERE nomor_usulan = ? AND id != ?";
$check_stmt = $koneksi->prepare($check_query);

if (!$check_stmt) {
    alertGagal("form_edit_usulan_pensiun.php?id=$id", 'Gagal validasi nomor usulan: ' . $koneksi->error);
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
    alertGagal("form_edit_usulan_pensiun.php?id=$id", $error_msg);
    exit;
}

$check_stmt->close();
// =====================================================

// Ambil nama pegawai untuk log
$nama_query = "SELECT nama FROM usulan_pensiun WHERE id = ?";
$nama_stmt = $koneksi->prepare($nama_query);
$nama_stmt->bind_param("i", $id);
$nama_stmt->execute();
$nama_result = $nama_stmt->get_result();
$nama_data = $nama_result->fetch_assoc();
$nama = $nama_data['nama'] ?? 'Unknown';
$nama_stmt->close();

// Query UPDATE
$query = "UPDATE usulan_pensiun SET 
          nomor_usulan = ?,
          tanggal_usulan = ?,
          jenis_pensiun = ?,
          alasan = ?,
          status = ?,
          keterangan = ?,
          updated_at = NOW()
          WHERE id = ?";

$stmt = $koneksi->prepare($query);

if (!$stmt) {
    alertGagal("form_edit_usulan_pensiun.php?id=$id", 'Gagal prepare statement: ' . $koneksi->error);
    exit;
}

$stmt->bind_param(
    "ssssssi",
    $nomor_usulan,
    $tanggal_usulan,
    $jenis_pensiun,
    $alasan,
    $status,
    $keterangan,
    $id
);

if ($stmt->execute()) {
    // ========== UPDATE BERHASIL ==========
    $stmt->close();
    $koneksi->close();
    
    // Log untuk debugging
    error_log("✅ Data Usulan Pensiun berhasil diupdate - ID: $id | Nama: $nama");
    
    // Redirect dengan alert sukses
    alertSuksesUbah('usulan_pensiun.php', "Data Usulan Pensiun $nama berhasil diperbarui!");
    exit;
    
} else {
    // ========== UPDATE GAGAL ==========
    $error_message = $stmt->error;
    $stmt->close();
    $koneksi->close();
    
    // Log error
    error_log("❌ Error update usulan_pensiun - ID: $id | Error: $error_message");
    
    // Redirect dengan alert error
    alertGagal("form_edit_usulan_pensiun.php?id=$id", 'Gagal mengupdate data: ' . $error_message);
    exit;
}
?>