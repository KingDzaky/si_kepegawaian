<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Session expired'
    ]);
    exit;
}

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method tidak valid'
    ]);
    exit;
}

// Ambil data
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$nomor_wa = isset($_POST['nomor_wa']) ? trim($_POST['nomor_wa']) : '';

// Validasi
if ($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID tidak valid'
    ]);
    exit;
}

if (empty($nomor_wa)) {
    echo json_encode([
        'success' => false,
        'message' => 'Nomor WhatsApp tidak boleh kosong'
    ]);
    exit;
}

// Format nomor: hapus karakter non-digit
$nomor_wa = preg_replace('/[^0-9]/', '', $nomor_wa);

// Validasi panjang minimal
if (strlen($nomor_wa) < 10) {
    echo json_encode([
        'success' => false,
        'message' => 'Nomor WhatsApp minimal 10 digit'
    ]);
    exit;
}

// Update database
$sql = "UPDATE duk SET nomor_wa = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'si', $nomor_wa, $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'success' => true,
        'message' => 'Nomor WhatsApp berhasil diupdate'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengupdate nomor WhatsApp: ' . mysqli_error($conn)
    ]);
}

mysqli_stmt_close($stmt);
?>