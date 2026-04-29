<?php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/soft_delete_functions.php';
require_once 'includes/alert_functions.php';

// Cek role - hanya admin/superadmin yang bisa hapus
if (!isAdmin()) {
    alertGagal('dataduk.php', 'Akses ditolak');
}

$id = $_GET['id'] ?? 0;
$confirm = $_GET['confirm'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;

if ($id > 0) {
    // Ambil data pegawai
    $query_check = "SELECT * FROM duk WHERE id = ?";
    $stmt_check = $koneksi->prepare($query_check);
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows === 0) {
        alertGagal('dataduk.php', 'Data tidak ditemukan');
    }

    $pegawai = $result->fetch_assoc();

    // Cek apakah sudah dihapus
    if ($pegawai['deleted_at'] !== null) {
        alertWarning('dataduk.php', 'Data ini sudah dihapus sebelumnya');
    }

    // Cek apakah ada usulan kenaikan pangkat yang masih aktif
    $check_usulan = "SELECT COUNT(*) as total 
                     FROM kenaikan_pangkat 
                     WHERE nip = ? 
                     AND status IN ('draft', 'diajukan', 'disetujui')
                     AND deleted_at IS NULL";
    $stmt_usulan = $koneksi->prepare($check_usulan);
    $stmt_usulan->bind_param("s", $pegawai['nip']);
    $stmt_usulan->execute();
    $result_usulan = $stmt_usulan->get_result();
    $usulan_data = $result_usulan->fetch_assoc();

    // Jika ada usulan aktif dan belum konfirmasi
    if ($usulan_data['total'] > 0 && $confirm !== 'yes') {
        alertWarning(
            'dataduk.php',
            "Pegawai {$pegawai['nama']} masih memiliki {$usulan_data['total']} usulan kenaikan pangkat aktif. Hapus usulan terlebih dahulu atau konfirmasi penghapusan."
        );
    }

    // Proses Soft Delete
    $hasil = softDelete($koneksi, 'duk', $id, $user_id, "Dihapus dari halaman Data DUK");

    if ($hasil['success']) {
        alertSuksesHapus('dataduk.php', "Data {$pegawai['nama']} berhasil dihapus. Data tersimpan di Recycle Bin selama 5 tahun.");
    } else {
        alertGagal('dataduk.php', $hasil['message']);
    }

} else {
    alertGagal('dataduk.php', 'ID tidak valid');
}
?>