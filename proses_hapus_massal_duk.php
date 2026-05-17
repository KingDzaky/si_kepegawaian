<?php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';

// ============================================================
// KEAMANAN: Hanya Superadmin yang boleh akses
// ============================================================
if (!isSuperAdmin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Akses ditolak. Hanya Superadmin yang dapat melakukan hapus massal.'
    ]);
    exit;
}

header('Content-Type: application/json');

$input  = json_decode(file_get_contents('php://input'), true);
$aksi   = $input['aksi'] ?? '';   // 'terpilih' | 'semua'
$ids    = $input['ids']  ?? [];   // array of int (hanya untuk 'terpilih')

$deleted_at = date('Y-m-d H:i:s');
$deleted_by = $_SESSION['user_id'] ?? 0;

// ============================================================
// HAPUS SEMUA (soft delete seluruh data DUK aktif)
// ============================================================
if ($aksi === 'semua') {
    $stmt = $koneksi->prepare("
        UPDATE duk 
        SET deleted_at = ?, deleted_by = ?
        WHERE deleted_at IS NULL
    ");
    $stmt->bind_param('si', $deleted_at, $deleted_by);

    if ($stmt->execute()) {
        $jumlah = $stmt->affected_rows;
        echo json_encode([
            'success' => true,
            'message' => "Berhasil memindahkan <strong>{$jumlah} data</strong> ke Recycle Bin.",
            'jumlah'  => $jumlah
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus data: ' . $koneksi->error
        ]);
    }
    exit;
}

// ============================================================
// HAPUS TERPILIH (soft delete berdasarkan ID)
// ============================================================
if ($aksi === 'terpilih') {
    if (empty($ids)) {
        echo json_encode([
            'success' => false,
            'message' => 'Tidak ada data yang dipilih.'
        ]);
        exit;
    }

    // Sanitasi: pastikan semua ID adalah integer
    $ids_clean = array_filter(array_map('intval', $ids));

    if (empty($ids_clean)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID tidak valid.'
        ]);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($ids_clean), '?'));
    $types        = str_repeat('i', count($ids_clean));

    $stmt = $koneksi->prepare("
        UPDATE duk 
        SET deleted_at = ?, deleted_by = ?
        WHERE id IN ({$placeholders}) AND deleted_at IS NULL
    ");

    // Bind: deleted_at (s), deleted_by (i), lalu semua id (i x N)
    $bind_params = array_merge(['si' . $types, $deleted_at, $deleted_by], $ids_clean);
    $refs = [];
    foreach ($bind_params as $k => $v) {
        $refs[$k] = &$bind_params[$k];
    }
    call_user_func_array([$stmt, 'bind_param'], $refs);

    if ($stmt->execute()) {
        $jumlah = $stmt->affected_rows;
        echo json_encode([
            'success' => true,
            'message' => "Berhasil memindahkan <strong>{$jumlah} data</strong> ke Recycle Bin.",
            'jumlah'  => $jumlah,
            'ids'     => $ids_clean
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus data: ' . $koneksi->error
        ]);
    }
    exit;
}

// Aksi tidak dikenal
echo json_encode([
    'success' => false,
    'message' => 'Aksi tidak dikenal.'
]);