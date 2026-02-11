<?php
/**
 * Router untuk export berkas pensiun individual
 * Bisa export: all, pengantar, pernyataan
 */
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';

if (!hasRole(['superadmin', 'admin', 'kepala_dinas'])) {
    header('Location: dashboard.php?error=Akses ditolak');
    exit;
}

$id = $_GET['id'] ?? 0;
$jenis = $_GET['jenis'] ?? 'all';

if ($id <= 0) {
    header('Location: usulan_pensiun.php?error=ID tidak valid');
    exit;
}

// Ambil data
$query = "SELECT up.*, kop.* 
          FROM usulan_pensiun up
          LEFT JOIN kepala_opd kop ON kop.status = 'aktif'
          WHERE up.id = ?
          LIMIT 1";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: usulan_pensiun.php?error=Data tidak ditemukan');
    exit;
}

$data = $result->fetch_assoc();

// Tentukan template mana yang akan di-load
switch ($jenis) {
    case 'pengantar':
        if ($data['sumber_data'] === 'penyuluh') {
            include 'templates/surat_pengantar_pkb.php';
        } else {
            include 'templates/surat_pengantar_duk.php';
        }
        break;
        
    case 'pernyataan':
        if ($data['sumber_data'] === 'duk') {
            include 'templates/surat_pernyataan_pidana.php';
        } else {
            header('Location: usulan_pensiun.php?error=Surat pernyataan hanya untuk DUK');
        }
        break;
        
    case 'all':
    default:
        // Load berkas lengkap (sudah ada di export_berkas_pensiun.php)
        include 'export_berkas_pensiun_lengkap.php';
        break;
}

$stmt->close();
$koneksi->close();
?>