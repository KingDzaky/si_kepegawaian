<?php
/**
 * ============================================================================
 * PROSES NONAKTIF / REAKTIF PENYULUH
 * ============================================================================
 */

session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/alert_functions.php';

if (!isAdmin()) {
    alertGagal('dashboard.php', 'Akses ditolak!');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alertGagal('penyuluh.php', 'Method tidak valid!');
    exit;
}

$id   = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
$aksi = trim($_POST['aksi'] ?? '');
$alasan     = trim($_POST['alasan'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');

if ($id <= 0) {
    alertGagal('penyuluh.php', 'ID penyuluh tidak valid!');
    exit;
}

if (!in_array($aksi, ['nonaktif', 'reaktif'])) {
    alertGagal('penyuluh.php', 'Aksi tidak valid!');
    exit;
}

// Ambil data penyuluh
$stmt = $koneksi->prepare("SELECT id, nama, nip, status_pegawai, nonaktif_at FROM penyuluh WHERE id = ? AND deleted_at IS NULL LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    alertGagal('penyuluh.php', 'Data penyuluh tidak ditemukan!');
    exit;
}

$penyuluh  = $result->fetch_assoc();
$stmt->close();

$nama_penyuluh = $penyuluh['nama'];
$id_user       = $_SESSION['user_id'] ?? null;

// ============================================================================
// AKSI: NONAKTIFKAN
// ============================================================================
if ($aksi === 'nonaktif') {

    if ($penyuluh['status_pegawai'] === 'nonaktif') {
        alertGagal('penyuluh.php', "Penyuluh $nama_penyuluh sudah berstatus nonaktif!");
        exit;
    }

    if (!in_array($alasan, ['Pensiun', 'Pindah', 'Lainnya'])) {
        alertGagal('penyuluh.php', 'Alasan nonaktif tidak valid!');
        exit;
    }

    // Cek usulan pensiun aktif
    $stmt_cek = $koneksi->prepare("
        SELECT COUNT(*) as total FROM usulan_pensiun
        WHERE nip = ? AND status IN ('draft','diajukan') AND deleted_at IS NULL
    ");
    $stmt_cek->bind_param('s', $penyuluh['nip']);
    $stmt_cek->execute();
    $total_up = $stmt_cek->get_result()->fetch_assoc()['total'];
    $stmt_cek->close();

    if ($total_up > 0) {
        alertGagal(
            'penyuluh.php',
            "Tidak dapat menonaktifkan $nama_penyuluh karena masih memiliki usulan pensiun aktif ($total_up usulan). "
            . "Selesaikan atau batalkan usulan tersebut terlebih dahulu."
        );
        exit;
    }

    // Update nonaktif
    $stmt_upd = $koneksi->prepare("
        UPDATE penyuluh
        SET status_pegawai  = 'nonaktif',
            alasan_nonaktif = ?,
            nonaktif_at     = NOW(),
            nonaktif_by     = ?
        WHERE id = ?
    ");
    $stmt_upd->bind_param('sii', $alasan, $id_user, $id);

    if ($stmt_upd->execute()) {
        $stmt_upd->close();

        // Log
        $log_data  = json_encode(['id'=>$id,'nama'=>$nama_penyuluh,'nip'=>$penyuluh['nip'],'aksi'=>'nonaktif','alasan'=>$alasan,'keterangan'=>$keterangan]);
        $expired   = date('Y-m-d H:i:s', strtotime('+6 months'));
        $alasan_log = "NONAKTIF: $alasan" . (!empty($keterangan) ? " – $keterangan" : '');

        $stmt_log = $koneksi->prepare("
            INSERT INTO deleted_records_log (table_name, record_id, record_data, deleted_at, deleted_by, delete_reason, permanent_delete_at)
            VALUES ('penyuluh_nonaktif', ?, ?, NOW(), ?, ?, ?)
        ");
        $stmt_log->bind_param('isiss', $id, $log_data, $id_user, $alasan_log, $expired);
        $stmt_log->execute();
        $stmt_log->close();

        $koneksi->close();
        alertSuksesUbah('penyuluh.php', "Penyuluh $nama_penyuluh berhasil dinonaktifkan dengan alasan: $alasan.");
        exit;

    } else {
        $err = $stmt_upd->error;
        $stmt_upd->close();
        $koneksi->close();
        alertGagal('penyuluh.php', "Gagal menonaktifkan penyuluh: $err");
        exit;
    }
}

// ============================================================================
// AKSI: REAKTIFKAN
// ============================================================================
if ($aksi === 'reaktif') {

    if ($penyuluh['status_pegawai'] !== 'nonaktif') {
        alertGagal('penyuluh.php', "Penyuluh $nama_penyuluh tidak dalam status nonaktif!");
        exit;
    }

    // Cek 6 bulan (superadmin bisa bypass)
    $is_superadmin = ($_SESSION['role'] ?? '') === 'superadmin';

    if (!$is_superadmin && !empty($penyuluh['nonaktif_at'])) {
        $ts_bisa = strtotime('+6 months', strtotime($penyuluh['nonaktif_at']));
        if (time() < $ts_bisa) {
            $bulan_indo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                           7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
            $tgl = date('d', $ts_bisa) . ' ' . $bulan_indo[(int)date('m', $ts_bisa)] . ' ' . date('Y', $ts_bisa);
            alertGagal(
                'penyuluh.php',
                "Penyuluh $nama_penyuluh belum dapat diaktifkan kembali. "
                . "Masa nonaktif 6 bulan berakhir pada $tgl. "
                . "Hubungi Superadmin jika perlu pengaktifan lebih awal."
            );
            exit;
        }
    }

    $stmt_reaktif = $koneksi->prepare("
        UPDATE penyuluh
        SET status_pegawai  = 'aktif',
            alasan_nonaktif = NULL,
            nonaktif_at     = NULL,
            nonaktif_by     = NULL
        WHERE id = ?
    ");
    $stmt_reaktif->bind_param('i', $id);

    if ($stmt_reaktif->execute()) {
        $stmt_reaktif->close();

        $log_data = json_encode(['id'=>$id,'nama'=>$nama_penyuluh,'nip'=>$penyuluh['nip'],'aksi'=>'reaktif','keterangan'=>$keterangan]);
        $stmt_log = $koneksi->prepare("
            INSERT INTO deleted_records_log (table_name, record_id, record_data, deleted_at, deleted_by, delete_reason, permanent_delete_at)
            VALUES ('penyuluh_nonaktif', ?, ?, NOW(), ?, 'REAKTIF', DATE_ADD(NOW(), INTERVAL 1 YEAR))
        ");
        $stmt_log->bind_param('isi', $id, $log_data, $id_user);
        $stmt_log->execute();
        $stmt_log->close();

        $koneksi->close();
        alertSuksesUbah('penyuluh.php', "Penyuluh $nama_penyuluh berhasil diaktifkan kembali!");
        exit;

    } else {
        $err = $stmt_reaktif->error;
        $stmt_reaktif->close();
        $koneksi->close();
        alertGagal('penyuluh.php', "Gagal mengaktifkan kembali penyuluh: $err");
        exit;
    }
}
?>