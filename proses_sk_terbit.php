<?php
// proses_sk_terbit.php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/alert_functions.php';

if (!hasRole(['superadmin', 'admin', 'kepala_dinas'])) {
    alertWarning('approval.php', 'Akses ditolak');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) {
    alertWarning('approval.php', 'ID tidak valid');
    exit;
}

// Ambil data kenaikan pangkat
$stmt = $koneksi->prepare("
    SELECT nip, pangkat_baru, golongan_baru, tmt_pangkat_baru, jabatan_baru, status
    FROM kenaikan_pangkat WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$kp = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$kp) {
    alertWarning('approval.php', 'Data tidak ditemukan');
    exit;
}

if ($kp['status'] !== 'disetujui') {
    alertWarning('approval.php', 'Usulan belum disetujui, tidak bisa konfirmasi SK');
    exit;
}

// Update DUK dengan data pangkat baru

// SESUDAH — hapus updated_at dari query
$stmt2 = $koneksi->prepare("
    UPDATE duk SET
        pangkat_terakhir = ?,
        golongan         = ?,
        tmt_pangkat      = ?,
        jabatan_terakhir = ?
    WHERE nip = ? AND deleted_at IS NULL
");
$stmt2->bind_param("sssss",
    $kp['pangkat_baru'],
    $kp['golongan_baru'],
    $kp['tmt_pangkat_baru'],
    $kp['jabatan_baru'],
    $kp['nip']
);
$stmt2->execute();
$stmt2->close();

// Tandai SK sudah terbit DAN ubah status
$stmt3 = $koneksi->prepare("
    UPDATE kenaikan_pangkat 
    SET status     = 'sk_terbit',
        keterangan = CONCAT(IFNULL(keterangan,''), ' | SK terbit: ', NOW()),
        updated_at = NOW()
    WHERE id = ?
");
$stmt3->bind_param("i", $id);
$stmt3->execute();
$stmt3->close();

$koneksi->close();
alertSuksesTambah('approval.php', 'SK dikonfirmasi. Data DUK berhasil diperbarui.');