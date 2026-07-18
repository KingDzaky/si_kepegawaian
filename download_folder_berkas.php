<?php
// download_folder_berkas.php
// Download semua berkas dalam 1 folder pegawai (KP atau Pensiun) sebagai file ZIP.
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/helper_berkas.php';

if (!isAdmin()) {
    http_response_code(403);
    die('Akses ditolak.');
}

if (!class_exists('ZipArchive')) {
    http_response_code(500);
    die('Ekstensi PHP "zip" belum aktif di server. Buka php.ini, aktifkan baris "extension=zip", lalu restart Apache.');
}

$id    = (int)($_GET['id'] ?? 0);
$jenis = $_GET['jenis'] ?? '';

if (!$id || !in_array($jenis, ['kp', 'pensiun'], true)) {
    http_response_code(400);
    die('Parameter tidak valid.');
}

if ($jenis === 'kp') {
    $stmt = $koneksi->prepare("SELECT nama, nip FROM kenaikan_pangkat WHERE id = ? LIMIT 1");
    $folderBase = 'uploads/sk_kp/';
} else {
    $stmt = $koneksi->prepare("SELECT nama, nip FROM usulan_pensiun WHERE id = ? LIMIT 1");
    $folderBase = 'uploads/sk_pensiun/';
}
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    http_response_code(404);
    die('Data pegawai tidak ditemukan.');
}

$folderRelatif = $folderBase . folderPegawaiBerkas($data['nama'], $data['nip']);
$folderFull    = realpath(__DIR__ . '/' . $folderRelatif);
$uploadsBase   = realpath(__DIR__ . '/uploads');

// Validasi folder tetap di dalam uploads/ (cegah path traversal) dan benar-benar ada
if (!$folderFull || !$uploadsBase || strpos($folderFull, $uploadsBase) !== 0 || !is_dir($folderFull)) {
    http_response_code(404);
    die('Belum ada berkas yang diupload untuk pegawai ini.');
}

$files = array_filter(glob($folderFull . '/*'), 'is_file');

if (empty($files)) {
    http_response_code(404);
    die('Folder berkas masih kosong.');
}

$zipName = slugBerkas($data['nama']) . '_berkas.zip';
$zipPath = sys_get_temp_dir() . '/' . uniqid('berkas_', true) . '.zip';

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    die('Gagal membuat file ZIP di server.');
}

foreach ($files as $file) {
    $zip->addFile($file, basename($file));
}
$zip->close();

// Ini memang DIMAKSUDKAN untuk didownload (bukan preview), jadi Content-Disposition attachment sudah tepat
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . filesize($zipPath));
header('Cache-Control: private, max-age=0, must-revalidate');

readfile($zipPath);
unlink($zipPath);
exit;
