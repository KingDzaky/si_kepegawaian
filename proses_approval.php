<?php
// proses_approval.php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/alert_functions.php';

if (!hasRole(['superadmin', 'admin', 'kepala_dinas'])) {
    alertWarning('approval.php', 'Akses ditolak');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alertWarning('approval.php', 'Method tidak valid');
    exit;
}

$id         = (int)($_POST['id'] ?? 0);
$action     = $_POST['action'] ?? '';
$keterangan = trim($_POST['keterangan'] ?? '');

if (!$id || !in_array($action, ['approve', 'reject'])) {
    alertWarning('approval.php', 'Data tidak valid');
    exit;
}

// Ambil data kenaikan pangkat
$stmt = $koneksi->prepare("
    SELECT nip, nama, pangkat_baru, golongan_baru, 
           tmt_pangkat_baru, jabatan_baru, status
    FROM kenaikan_pangkat 
    WHERE id = ? AND status = 'diajukan'
    LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$kp = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$kp) {
    alertWarning('approval.php', 'Data tidak ditemukan atau sudah diproses');
    exit;
}

// ============================================
// APPROVE — Setujui & langsung update DUK
// ============================================
if ($action === 'approve') {

    $stmt1 = $koneksi->prepare("
        UPDATE kenaikan_pangkat 
        SET status     = 'disetujui',
            keterangan = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt1->bind_param("si", $keterangan, $id);
    $stmt1->execute();
    $stmt1->close();

      // Blok update DUK dihapus dari sini — dipindah ke proses_upload_berkas.php
    // DUK baru ter-update otomatis setelah 4 berkas wajib lengkap

    $koneksi->close();
    alertSuksesUbah('approval.php', "Usulan {$kp['nama']} telah disetujui. Menunggu kelengkapan berkas untuk update DUK.");


// ============================================
// REJECT — Tolak usulan
// ============================================
} elseif ($action === 'reject') {

    if (empty($keterangan)) {
        alertWarning('approval.php', 'Alasan penolakan wajib diisi');
        exit;
    }

    $stmt = $koneksi->prepare("
        UPDATE kenaikan_pangkat 
        SET status     = 'ditolak',
            keterangan = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("si", $keterangan, $id);
    $stmt->execute();
    $stmt->close();

    $koneksi->close();
    alertSuksesUbah('approval.php', "Usulan {$kp['nama']} telah ditolak.");
}