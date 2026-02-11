<?php
session_start();

// ✅ Urutan include yang benar
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/alert_functions.php';

// Cek akses admin
if (!isAdmin()) {
    alertGagal('usulan_pensiun.php', 'Akses ditolak! Anda tidak memiliki izin.');
    exit;
}

// Validasi ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    alertGagal('usulan_pensiun.php', 'ID tidak valid!');
    exit;
}

$id = (int)$_GET['id'];

// Cek apakah data exists dan ambil info untuk log
$check_stmt = $koneksi->prepare("SELECT nomor_usulan, nama FROM usulan_pensiun WHERE id = ?");
$check_stmt->bind_param("i", $id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    $check_stmt->close();
    $koneksi->close();
    alertGagal('usulan_pensiun.php', 'Data usulan pensiun tidak ditemukan!');
    exit;
}

$data = $result->fetch_assoc();
$nomor_usulan = $data['nomor_usulan'];
$nama = $data['nama'];
$check_stmt->close();

// ========== DELETE DATA ==========
// Cascade akan otomatis menghapus notifikasi_pensiun yang terkait
$query = "DELETE FROM usulan_pensiun WHERE id = ?";
$stmt = $koneksi->prepare($query);

if (!$stmt) {
    error_log("❌ Error prepare statement: " . $koneksi->error);
    $koneksi->close();
    alertGagal('usulan_pensiun.php', 'Error database: ' . $koneksi->error);
    exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // ========== DELETE BERHASIL ==========
    $stmt->close();
    $koneksi->close();
    
    // Log untuk debugging
    error_log("✅ Data Usulan Pensiun berhasil dihapus - ID: $id | Nomor: $nomor_usulan | Nama: $nama");
    
    // Redirect dengan SweetAlert
    alertSuksesHapus('usulan_pensiun.php', "Usulan pensiun $nama (No: $nomor_usulan) berhasil dihapus!");
    exit;
    
} else {
    // ========== DELETE GAGAL ==========
    $error_message = $stmt->error;
    $stmt->close();
    $koneksi->close();
    
    // Log error
    error_log("❌ Error hapus Usulan Pensiun - ID: $id | Error: $error_message");
    
    // Redirect dengan alert error
    alertGagal('usulan_pensiun.php', 'Gagal menghapus data: ' . $error_message);
    exit;
}
?>