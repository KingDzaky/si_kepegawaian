<?php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';

if (!isAdmin()) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}

$id_berkas           = (int)($_POST['id_berkas'] ?? 0);
$id_kenaikan_pangkat = (int)($_POST['id_kenaikan_pangkat'] ?? 0);

if (!$id_berkas || !$id_kenaikan_pangkat) {
    header("Location: form_upload_berkas_kp.php?id={$id_kenaikan_pangkat}&error=Parameter tidak valid");
    exit;
}

// Ambil data berkas
$stmt = $koneksi->prepare("SELECT file_path, jenis_berkas FROM berkas_kenaikan_pangkat WHERE id = ? AND id_kenaikan_pangkat = ? LIMIT 1");
$stmt->bind_param("ii", $id_berkas, $id_kenaikan_pangkat);
$stmt->execute();
$berkas = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$berkas) {
    header("Location: form_upload_berkas_kp.php?id={$id_kenaikan_pangkat}&error=Berkas tidak ditemukan");
    exit;
}

// Hapus file fisik (dengan validasi path traversal)
$rootPath    = realpath(__DIR__);
$filePath    = realpath($rootPath . '/' . $berkas['file_path']);
$uploadsBase = realpath($rootPath . '/uploads');

if ($filePath && $uploadsBase && strpos($filePath, $uploadsBase) === 0 && file_exists($filePath)) {
    unlink($filePath);
}

// Hapus record DB
$stmt2 = $koneksi->prepare("DELETE FROM berkas_kenaikan_pangkat WHERE id = ?");
$stmt2->bind_param("i", $id_berkas);
$stmt2->execute();
$stmt2->close();

// Kelengkapan berkas tidak perlu di-flag di tabel kenaikan_pangkat —
// form_upload_berkas_kp.php sudah hitung otomatis dari COUNT berkas
// saat halaman di-load, jadi badge progress langsung terupdate sendiri.

header("Location: form_upload_berkas_kp.php?id={$id_kenaikan_pangkat}&success=Berkas berhasil dihapus");
exit;