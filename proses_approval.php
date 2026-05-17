<?php
// proses_approval.php
session_start();

require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/alert_functions.php';

// Cek akses
if (!isset($_SESSION['user_id'])) {
    alertWarning('login.php', 'Silakan login terlebih dahulu!');
    exit;
}

// Validasi request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alertWarning('approval.php', 'Invalid request method!');
    exit;
}

// Validasi action dan ID
if (!isset($_POST['action']) || !isset($_POST['id'])) {
    alertWarning('approval.php', 'Data tidak lengkap!');
    exit;
}

$id         = (int)$_POST['id'];
$action     = $_POST['action'];
$keterangan = trim($_POST['keterangan'] ?? '');

// Validasi action
if (!in_array($action, ['approve', 'reject'])) {
    alertWarning('approval.php', 'Action tidak valid!');
    exit;
}

// ========== CEK DATA USULAN EXISTS ==========
$check_stmt = $koneksi->prepare("
    SELECT id, nomor_usulan, nip, nama, status 
    FROM kenaikan_pangkat 
    WHERE id = ?
");
$check_stmt->bind_param("i", $id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    $check_stmt->close();
    alertWarning('approval.php', 'Data usulan tidak ditemukan!');
    exit;
}

$usulan = $result->fetch_assoc();
$check_stmt->close();

// Cek apakah sudah diproses
if ($usulan['status'] !== 'diajukan') {
    $status_text = $usulan['status'] === 'disetujui' ? 'disetujui' : 'ditolak';
    alertWarning('approval.php', "Usulan ini sudah $status_text sebelumnya!");
    exit;
}

// ========== TENTUKAN STATUS BARU ==========
if ($action === 'approve') {
    $status = 'disetujui';
} else {
    $status = 'ditolak';

    // Keterangan wajib diisi saat menolak
    if (empty($keterangan)) {
        alertWarning('approval.php', 'Alasan penolakan wajib diisi!');
        exit;
    }
}

// ========== UPDATE STATUS KENAIKAN PANGKAT ==========
// CATATAN: DUK *tidak* diupdate di sini.
// DUK baru diupdate setelah SK BKN benar-benar terbit,
// melalui tombol "SK Terbit" → proses_sk_terbit.php
$update_query = "UPDATE kenaikan_pangkat 
                 SET status     = ?, 
                     keterangan = ?,
                     updated_at = NOW()
                 WHERE id = ?";

$stmt = $koneksi->prepare($update_query);

if (!$stmt) {
    error_log("❌ Error prepare statement: " . $koneksi->error);
    alertWarning('approval.php', 'Error database: ' . $koneksi->error);
    exit;
}

$stmt->bind_param("ssi", $status, $keterangan, $id);

if ($stmt->execute()) {
    $stmt->close();
    $koneksi->close();

    $nama      = $usulan['nama'];
    $user_name = $_SESSION['username'] ?? 'Unknown';

    error_log("✅ Approval $status - ID: $id | Nama: {$usulan['nama']} | NIP: {$usulan['nip']} | By: $user_name");

    if ($action === 'approve') {
        alertSuksesApproval('approval.php', "Data Approval $nama Disetujui. Klik 'SK Terbit' setelah SK BKN keluar untuk memperbarui data DUK.");
    } else {
        alertGagalApproval('approval.php', "Data Approval $nama Ditolak");
    }
    exit;

} else {
    $error_message = $stmt->error;
    $stmt->close();
    $koneksi->close();

    error_log("❌ Error approval - ID: $id | Error: $error_message");
    alertWarning('approval.php', 'Gagal memproses approval: ' . $error_message);
    exit;
}