<?php
session_start();

// ✅ Include alert_functions.php DI AWAL
require_once 'includes/alert_functions.php';
require_once 'config/koneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Cek akses admin
if ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'admin') {
    alertGagal('penyuluh.php', 'Anda tidak memiliki akses untuk menghapus data');
}

// Cek apakah ID ada
if (!isset($_GET['id']) || empty($_GET['id'])) {
    alertGagal('penyuluh.php', 'ID tidak valid');
}

$id = intval($_GET['id']);

// Cek apakah data exist
$check = $koneksi->prepare("SELECT nama FROM penyuluh WHERE id = ?");
$check->bind_param("i", $id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    $check->close();
    alertGagal('penyuluh.php', 'Data tidak ditemukan');
}

$data = $result->fetch_assoc();
$nama_penyuluh = $data['nama'];
$check->close();

// Hapus data
$sql = "DELETE FROM penyuluh WHERE id = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    $koneksi->close();
    
    error_log("✅ Data Penyuluh berhasil dihapus - ID: $id | Nama: $nama_penyuluh");
    
    // ✅ Redirect dengan alert sukses
    alertSuksesHapus('penyuluh.php', "Data penyuluh $nama_penyuluh berhasil dihapus!");
    
} else {
    $error = $stmt->error;
    $stmt->close();
    $koneksi->close();
    
    error_log("❌ Error hapus penyuluh: $error");
    
    alertGagal('penyuluh.php', 'Gagal menghapus data: ' . $error);
}
?>