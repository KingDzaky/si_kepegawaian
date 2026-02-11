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
    alertGagal('kepala_opd.php', 'Anda tidak memiliki akses untuk menghapus data');
}

// Cek apakah ID ada
if (!isset($_GET['id']) || empty($_GET['id'])) {
    alertGagal('kepala_opd.php', 'ID tidak valid');
}

$id = intval($_GET['id']);

// Cek apakah data exist
$check = $koneksi->prepare("SELECT nama FROM kepala_opd WHERE id = ?");
$check->bind_param("i", $id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    $check->close();
    alertGagal('kepala_opd.php', 'Data tidak ditemukan');
}

$data = $result->fetch_assoc();
$nama_kepala_opd = $data['nama'];
$check->close();

// ✅ PENTING: Cek apakah Kepala OPD ini sedang digunakan di tabel DUK
$check_usage = $koneksi->prepare("SELECT COUNT(*) as jumlah FROM duk WHERE id_opd = ?");
$check_usage->bind_param("i", $id);
$check_usage->execute();
$result_usage = $check_usage->get_result();
$usage_data = $result_usage->fetch_assoc();
$jumlah_pegawai = $usage_data['jumlah'];
$check_usage->close();

if ($jumlah_pegawai > 0) {
    alertWarning(
        'kepala_opd.php', 
        "Tidak dapat menghapus Kepala OPD $nama_kepala_opd karena masih digunakan oleh $jumlah_pegawai pegawai. Ubah status menjadi Non-Aktif sebagai gantinya."
    );
}

// Hapus data (jika tidak ada yang menggunakan)
$sql = "DELETE FROM kepala_opd WHERE id = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    $koneksi->close();
    
    error_log("✅ Data Kepala OPD berhasil dihapus - ID: $id | Nama: $nama_kepala_opd");
    
    // ✅ Redirect dengan alert sukses
    alertSuksesHapus('kepala_opd.php', "Data Kepala OPD $nama_kepala_opd berhasil dihapus!");
    
} else {
    $error = $stmt->error;
    $stmt->close();
    $koneksi->close();
    
    error_log("❌ Error hapus Kepala OPD: $error");
    
    alertGagal('kepala_opd.php', 'Gagal menghapus data: ' . $error);
}
?>