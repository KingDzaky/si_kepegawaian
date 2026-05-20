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

    // 1. Update status kenaikan_pangkat → disetujui
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

    // 2. ✅ Auto-update tabel DUK langsung
    //    Validasi: hanya update jika pangkat_baru tidak kosong
    if (!empty($kp['pangkat_baru']) && !empty($kp['golongan_baru'])) {
        $stmt2 = $koneksi->prepare("
            UPDATE duk SET
                pangkat_terakhir = ?,
                golongan         = ?,
                tmt_pangkat      = ?,
                jabatan_terakhir = CASE 
                    WHEN ? != '' THEN ? 
                    ELSE jabatan_terakhir 
                END
            WHERE nip = ?
        ");
        $stmt2->bind_param("ssssss",
            $kp['pangkat_baru'],
            $kp['golongan_baru'],
            $kp['tmt_pangkat_baru'],
            $kp['jabatan_baru'],
            $kp['jabatan_baru'],
            $kp['nip']
        );
        $stmt2->execute();

        $rows_updated = $stmt2->affected_rows;
        $stmt2->close();

        error_log("✅ DUK diperbarui untuk NIP: {$kp['nip']} | Rows: $rows_updated");
    } else {
        // Pangkat baru kosong — catat di log tapi tetap approve
        error_log("⚠️ Pangkat baru kosong untuk KP ID: $id, NIP: {$kp['nip']} — DUK tidak diupdate");
    }

    $koneksi->close();
    alertSuksesUbah('approval.php', "Usulan {$kp['nama']} telah disetujui dan data DUK diperbarui.");

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