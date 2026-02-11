<?php
session_start();

// ✅ Urutan include yang benar
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/alert_functions.php';

// Cek akses admin
if (!isAdmin()) {
    alertGagal('kenaikan_pangkat.php', 'Akses ditolak! Anda tidak memiliki izin.');
    exit;
}

// Validasi ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    alertGagal('kenaikan_pangkat.php', 'ID tidak valid!');
    exit;
}

$id = (int)$_GET['id'];

// Cek apakah data exists dan ambil info untuk log
$check_stmt = $koneksi->prepare("SELECT nomor_usulan, nama FROM kenaikan_pangkat WHERE id = ?");
$check_stmt->bind_param("i", $id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    $check_stmt->close();
    alertGagal('kenaikan_pangkat.php', 'Data usulan tidak ditemukan!');
    exit;
}

$data = $result->fetch_assoc();
$nomor_usulan = $data['nomor_usulan'];
$nama = $data['nama'];
$check_stmt->close();

// ========== DELETE DATA ==========
$query = "DELETE FROM kenaikan_pangkat WHERE id = ?";
$stmt = $koneksi->prepare($query);

if (!$stmt) {
    error_log("❌ Error prepare statement: " . $koneksi->error);
    alertGagal('kenaikan_pangkat.php', 'Error database: ' . $koneksi->error);
    exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // ========== DELETE BERHASIL ==========
    $stmt->close();
    $koneksi->close();
    
    // Log untuk debugging
    error_log("✅ Data Kenaikan Pangkat berhasil dihapus - ID: $id | Nomor: $nomor_usulan | Nama: $nama");
    
    // Redirect dengan SweetAlert
    alertSuksesHapus('kenaikan_pangkat.php', "Usulan kenaikan pangkat $nama (No: $nomor_usulan) berhasil dihapus!");
    exit;
    
} else {
    // ========== DELETE GAGAL ==========
    $error_message = $stmt->error;
    $stmt->close();
    $koneksi->close();
    
    // Log error
    error_log("❌ Error hapus Kenaikan Pangkat - ID: $id | Error: $error_message");
    
    // Redirect dengan alert error
    alertGagal('kenaikan_pangkat.php', 'Gagal menghapus data: ' . $error_message);
    exit;
}
?>