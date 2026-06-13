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
// HELPER: Cascade soft delete usulan terkait berdasarkan NIPs
// ============================================================
function cascadeSoftDeleteUsulan($koneksi, array $nips, $deleted_at, $deleted_by) {
    if (empty($nips)) return;

    $ph  = implode(',', array_fill(0, count($nips), '?'));
    $tys = str_repeat('s', count($nips));

    // --- Cascade kenaikan_pangkat ---
    $s1 = $koneksi->prepare("
        UPDATE kenaikan_pangkat
        SET deleted_at = ?, deleted_by = ?
        WHERE nip IN ($ph)
          AND deleted_at IS NULL
    ");
    $p1 = array_merge(['si' . $tys, $deleted_at, $deleted_by], $nips);
    $r1 = [];
    foreach ($p1 as $k => $v) $r1[$k] = &$p1[$k];
    call_user_func_array([$s1, 'bind_param'], $r1);
    $s1->execute();
    $s1->close();

    // --- Cascade usulan_pensiun ---
    $s2 = $koneksi->prepare("
        UPDATE usulan_pensiun
        SET deleted_at = ?, deleted_by = ?
        WHERE nip IN ($ph)
          AND deleted_at IS NULL
    ");
    $p2 = array_merge(['si' . $tys, $deleted_at, $deleted_by], $nips);
    $r2 = [];
    foreach ($p2 as $k => $v) $r2[$k] = &$p2[$k];
    call_user_func_array([$s2, 'bind_param'], $r2);
    $s2->execute();
    $s2->close();
}

// ============================================================
// HAPUS SEMUA (soft delete seluruh data DUK aktif)
// ============================================================
if ($aksi === 'semua') {

    // 1. Ambil semua NIP yang akan dihapus
    $res_nip = $koneksi->query("SELECT nip FROM duk WHERE deleted_at IS NULL");
    $semua_nip = [];
    while ($row = $res_nip->fetch_assoc()) {
        $semua_nip[] = $row['nip'];
    }

    // 2. Cascade soft delete usulan terkait
    if (!empty($semua_nip)) {
        cascadeSoftDeleteUsulan($koneksi, $semua_nip, $deleted_at, $deleted_by);
    }

    // 3. Soft delete semua DUK
    $stmt = $koneksi->prepare("
        UPDATE duk
        SET deleted_at = ?, deleted_by = ?
        WHERE deleted_at IS NULL
    ");
    $stmt->bind_param('si', $deleted_at, $deleted_by);

    if ($stmt->execute()) {
        $jumlah = $stmt->affected_rows;
        $stmt->close();
        echo json_encode([
            'success' => true,
            'message' => "Berhasil memindahkan <strong>{$jumlah} data</strong> ke Recycle Bin. "
                       . "Usulan kenaikan pangkat &amp; pensiun terkait juga ikut diarsipkan.",
            'jumlah'  => $jumlah
        ]);
    } else {
        $err = $koneksi->error;
        $stmt->close();
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus data: ' . $err
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

    // Sanitasi: pastikan semua ID adalah integer positif
    $ids_clean = array_values(array_filter(array_map('intval', $ids)));

    if (empty($ids_clean)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID tidak valid.'
        ]);
        exit;
    }

    $ph_id  = implode(',', array_fill(0, count($ids_clean), '?'));
    $tys_id = str_repeat('i', count($ids_clean));

    // 1. Ambil NIP dari ID terpilih
    $stmt_nip = $koneksi->prepare("
        SELECT nip FROM duk
        WHERE id IN ($ph_id)
          AND deleted_at IS NULL
    ");
    $p_nip = array_merge([$tys_id], $ids_clean);
    $r_nip = [];
    foreach ($p_nip as $k => $v) $r_nip[$k] = &$p_nip[$k];
    call_user_func_array([$stmt_nip, 'bind_param'], $r_nip);
    $stmt_nip->execute();
    $res_nip   = $stmt_nip->get_result();
    $nips_terpilih = array_column($res_nip->fetch_all(MYSQLI_ASSOC), 'nip');
    $stmt_nip->close();

    // 2. Cascade soft delete usulan terkait
    if (!empty($nips_terpilih)) {
        cascadeSoftDeleteUsulan($koneksi, $nips_terpilih, $deleted_at, $deleted_by);
    }

    // 3. Soft delete DUK terpilih
    $stmt = $koneksi->prepare("
        UPDATE duk
        SET deleted_at = ?, deleted_by = ?
        WHERE id IN ($ph_id)
          AND deleted_at IS NULL
    ");
    $bind_params = array_merge(['si' . $tys_id, $deleted_at, $deleted_by], $ids_clean);
    $refs = [];
    foreach ($bind_params as $k => $v) $refs[$k] = &$bind_params[$k];
    call_user_func_array([$stmt, 'bind_param'], $refs);

    if ($stmt->execute()) {
        $jumlah = $stmt->affected_rows;
        $stmt->close();
        echo json_encode([
            'success' => true,
            'message' => "Berhasil memindahkan <strong>{$jumlah} data</strong> ke Recycle Bin. "
                       . "Usulan kenaikan pangkat &amp; pensiun terkait juga ikut diarsipkan.",
            'jumlah'  => $jumlah,
            'ids'     => $ids_clean
        ]);
    } else {
        $err = $koneksi->error;
        $stmt->close();
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus data: ' . $err
        ]);
    }
    exit;
}

// ============================================================
// Aksi tidak dikenal
// ============================================================
echo json_encode([
    'success' => false,
    'message' => 'Aksi tidak dikenal.'
]);