<?php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/soft_delete_functions.php';
require_once 'includes/alert_functions.php';

// HANYA SUPERADMIN yang bisa hapus permanen
if (!isAdmin()) { header('Location: dashboard.php?error=Akses ditolak'); exit; }

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    alertGagal('recycle_bin.php', 'ID tidak valid.');
}

// ── Ambil data pegawai ────────────────────────────────────────────────────────
$stmt = $koneksi->prepare("SELECT nip, nama FROM duk WHERE id = ? AND deleted_at IS NOT NULL");
$stmt->bind_param("i", $id);
$stmt->execute();
$pegawai = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pegawai) {
    alertGagal('recycle_bin.php', 'Data tidak ditemukan atau belum ada di Recycle Bin.');
}

$nip  = $pegawai['nip'];
$nama = $pegawai['nama'];

// ── Validasi: Cek usulan kenaikan pangkat aktif ───────────────────────────────
// Cek TANPA filter deleted_at karena bisa jadi sudah ikut soft delete
// tapi usulannya tetap ada dan perlu diselesaikan dulu
$stmt_kp = $koneksi->prepare("
    SELECT COUNT(*) as total FROM kenaikan_pangkat
    WHERE nip = ? AND status IN ('draft','diajukan','disetujui')
");
$stmt_kp->bind_param("s", $nip);
$stmt_kp->execute();
$total_kp = $stmt_kp->get_result()->fetch_assoc()['total'];
$stmt_kp->close();

// ── Validasi: Cek usulan pensiun aktif ───────────────────────────────────────
// Sama: tidak filter deleted_at agar tetap terdeteksi walau sudah di-cascade
$stmt_up = $koneksi->prepare("
    SELECT COUNT(*) as total FROM usulan_pensiun
    WHERE nip = ? AND status IN ('draft','diajukan','disetujui')
");
$stmt_up->bind_param("s", $nip);
$stmt_up->execute();
$total_up = $stmt_up->get_result()->fetch_assoc()['total'];
$stmt_up->close();

// ── Blokir jika masih ada usulan aktif ───────────────────────────────────────
if ($total_kp > 0 || $total_up > 0) {
    $pesan = [];
    if ($total_kp > 0) $pesan[] = "{$total_kp} usulan kenaikan pangkat aktif";
    if ($total_up > 0) $pesan[] = "{$total_up} usulan pensiun aktif";

    $msg = "Tidak dapat menghapus permanen data {$nama}. "
         . "Masih terdapat " . implode(' dan ', $pesan)
         . ". Selesaikan atau hapus usulan tersebut terlebih dahulu.";

    alertWarning('recycle_bin.php', $msg);
}

// ── Proses hapus permanen ─────────────────────────────────────────────────────
$result = hardDelete($koneksi, 'duk', $id);

if ($result['success']) {
    alertSuksesHapus(
        'recycle_bin.php',
        "Data {$nama} berhasil dihapus permanen dari sistem."
    );
} else {
    alertGagal('recycle_bin.php', $result['message']);
}
?>