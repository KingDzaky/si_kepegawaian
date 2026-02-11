<?php
session_start();

// ✅ URUTAN YANG BENAR:
// 1. check_session.php dulu (ini punya function isAdmin())
require_once 'check_session.php';

// 2. Baru file lainnya
require_once 'config/koneksi.php';
require_once 'includes/alert_functions.php';

// Sekarang isAdmin() sudah bisa dipanggil
if (!isAdmin()) {
    alertGagal('kepala_opd.php', 'Anda tidak memiliki akses untuk mengubah data');
    exit;
}

// Validasi request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alertGagal('kepala_opd.php', 'Invalid request method');
    exit;
}

// Validasi ID
if (!isset($_POST['id']) || empty($_POST['id'])) {
    alertGagal('kepala_opd.php', 'ID tidak valid');
    exit;
}

$id = (int)$_POST['id'];

// Cek apakah data exist
$check = $koneksi->prepare("SELECT nama FROM kepala_opd WHERE id = ?");
$check->bind_param("i", $id);
$check->execute();
$result_check = $check->get_result();

if ($result_check->num_rows === 0) {
    $check->close();
    alertGagal('kepala_opd.php', 'Data tidak ditemukan');
    exit;
}
$check->close();

// Ambil dan sanitasi data
$nama = trim($_POST['nama'] ?? '');
$nip = trim($_POST['nip'] ?? '');
$pangkat = trim($_POST['pangkat'] ?? '');
$golongan = trim($_POST['golongan'] ?? '');
$jabatan = trim($_POST['jabatan'] ?? '');
$gelar_depan = trim($_POST['gelar_depan'] ?? '');
$gelar_belakang = trim($_POST['gelar_belakang'] ?? '');
$status = trim($_POST['status'] ?? '');
$tmt_jabatan = trim($_POST['tmt_jabatan'] ?? '');

// Validasi field wajib
$errors = [];
if (empty($nama)) $errors[] = "Nama wajib diisi";
if (empty($nip)) $errors[] = "NIP wajib diisi";
if (empty($pangkat)) $errors[] = "Pangkat wajib diisi";
if (empty($golongan)) $errors[] = "Golongan wajib diisi";
if (empty($jabatan)) $errors[] = "Jabatan wajib diisi";
if (empty($status)) $errors[] = "Status wajib diisi";

if (!empty($errors)) {
    alertGagal("form_edit_kepala_opd.php?id=$id", implode(', ', $errors));
    exit;
}

// Validasi NIP format (18 digit)
if (!preg_match('/^\d{18}$/', $nip)) {
    alertGagal("form_edit_kepala_opd.php?id=$id", 'NIP harus 18 digit angka');
    exit;
}

// Cek duplikasi NIP (kecuali untuk data ini sendiri)
$check_nip = $koneksi->prepare("SELECT id FROM kepala_opd WHERE nip = ? AND id != ?");
$check_nip->bind_param("si", $nip, $id);
$check_nip->execute();
$check_nip->store_result();

if ($check_nip->num_rows > 0) {
    $check_nip->close();
    alertGagal("form_edit_kepala_opd.php?id=$id", 'NIP sudah digunakan oleh Kepala OPD lain!');
    exit;
}
$check_nip->close();

// ✅ PENTING: Jika status diubah menjadi aktif, set semua yang lain jadi non-aktif
if ($status === 'aktif') {
    $update_query = "UPDATE kepala_opd SET status = 'non-aktif' WHERE id != ?";
    $stmt_update = $koneksi->prepare($update_query);
    $stmt_update->bind_param("i", $id);
    $stmt_update->execute();
    $stmt_update->close();
}

// Update data kepala OPD
$query = "UPDATE kepala_opd SET 
          nama = ?, 
          nip = ?, 
          pangkat = ?, 
          golongan = ?, 
          jabatan = ?, 
          gelar_depan = ?, 
          gelar_belakang = ?, 
          status = ?, 
          tmt_jabatan = ?,
          updated_at = NOW()
          WHERE id = ?";

$stmt = $koneksi->prepare($query);

if (!$stmt) {
    error_log("❌ Error prepare statement: " . $koneksi->error);
    alertGagal("form_edit_kepala_opd.php?id=$id", 'Error database: ' . $koneksi->error);
    exit;
}

$stmt->bind_param(
    "sssssssssi", 
    $nama, 
    $nip, 
    $pangkat, 
    $golongan, 
    $jabatan, 
    $gelar_depan, 
    $gelar_belakang, 
    $status, 
    $tmt_jabatan, 
    $id
);

if ($stmt->execute()) {
    // ========== UPDATE BERHASIL ==========
    $stmt->close();
    $koneksi->close();
    
    // Log untuk debugging
    error_log("✅ Data Kepala OPD berhasil diupdate - ID: $id | Nama: $nama | Status: $status");
    
    // ✅ Redirect ke halaman list dengan SweetAlert
    alertSuksesUbah('kepala_opd.php', "Data Kepala OPD <strong>$nama</strong> berhasil diperbarui!");
    exit;
    
} else {
    // ========== UPDATE GAGAL ==========
    $error_message = $stmt->error;
    $stmt->close();
    $koneksi->close();
    
    // Log error
    error_log("❌ Error update Kepala OPD - ID: $id | Error: $error_message");
    
    // Redirect dengan alert error
    alertGagal("form_edit_kepala_opd.php?id=$id", 'Gagal mengupdate data: ' . $error_message);
    exit;
}
?>