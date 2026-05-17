<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/wa_functions.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Cek login
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu']);
    exit;
}

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid. Harus POST.']);
    exit;
}

// Ambil ID
$id_kenaikan_pangkat = isset($_POST['id_kenaikan_pangkat']) ? intval($_POST['id_kenaikan_pangkat']) : 0;

if ($id_kenaikan_pangkat <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID kenaikan pangkat tidak valid: ' . $id_kenaikan_pangkat]);
    exit;
}

error_log("=== KIRIM NOTIFIKASI WA ===");
error_log("ID Kenaikan Pangkat: " . $id_kenaikan_pangkat);
error_log("User: " . ($_SESSION['username'] ?? '-'));

// Cek koneksi DB
if (!$koneksi || $koneksi->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error koneksi database']);
    exit;
}

try {
    // ✅ FIX: fungsi sekarang terima $koneksi + $id, bukan data array
    $result = kirim_notifikasi_kenaikan_pangkat($koneksi, $id_kenaikan_pangkat);
    error_log("Result: " . json_encode($result));
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}