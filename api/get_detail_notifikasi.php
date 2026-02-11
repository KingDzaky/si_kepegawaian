<?php
// Suppress all errors/warnings agar tidak tercampur dengan JSON
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// Hanya load file yang diperlukan
require_once __DIR__ . '/../config/koneksi.php';

// Set header JSON SEBELUM output apapun
header('Content-Type: application/json; charset=utf-8');

// Clear any output buffer
if (ob_get_level()) {
    ob_end_clean();
}

// Cek login
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Session expired'
    ]);
    exit;
}

// Ambil ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID tidak valid'
    ]);
    exit;
}

try {
    // Query detail notifikasi
    $sql = "SELECT 
                nw.*,
                kp.nomor_usulan,
                kp.nama,
                kp.nip
            FROM notifikasi_wa nw
            INNER JOIN kenaikan_pangkat kp ON nw.id_kenaikan_pangkat = kp.id
            WHERE kp.id = ? AND nw.status = 'terkirim'
            ORDER BY nw.created_at DESC
            LIMIT 1";

    $stmt = $koneksi->prepare($sql);

    if (!$stmt) {
        throw new Exception('Error prepare statement: ' . $koneksi->error);
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Success - kirim data
        echo json_encode([
            'success' => true,
            'data' => $row
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Detail notifikasi tidak ditemukan. Notifikasi belum pernah dikirim.'
        ], JSON_UNESCAPED_UNICODE);
    }

    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$koneksi->close();

// PENTING: Jangan ada output apapun setelah ini!
exit;
