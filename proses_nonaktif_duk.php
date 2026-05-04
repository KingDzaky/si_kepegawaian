<?php
/**
 * ============================================================================
 * PROSES NONAKTIF / REAKTIF PEGAWAI DUK
 * ============================================================================
 * Dipakai untuk:
 *   - Nonaktifkan pegawai (Pensiun / Pindah / Lainnya)
 *   - Reaktifkan pegawai kembali (jika salah input / keputusan berubah)
 *
 * Method : POST
 * Params :
 *   - id          : int     → ID pegawai di table duk
 *   - aksi        : string  → 'nonaktif' | 'reaktif'
 *   - alasan      : string  → 'Pensiun' | 'Pindah' | 'Lainnya' (wajib jika nonaktif)
 *   - keterangan  : string  → keterangan tambahan (opsional)
 * ============================================================================
 */

session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/alert_functions.php';

// ── Hanya superadmin dan admin ────────────────────────────────────────────────
if (!isAdmin()) {
    alertGagal('dashboard.php', 'Akses ditolak! Anda tidak memiliki izin.');
    exit;
}

// ── Validasi method ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alertGagal('dataduk.php', 'Method tidak valid!');
    exit;
}

// ── Ambil & validasi input ────────────────────────────────────────────────────
$id          = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
$aksi        = trim($_POST['aksi'] ?? '');           // 'nonaktif' | 'reaktif'
$alasan      = trim($_POST['alasan'] ?? '');          // 'Pensiun' | 'Pindah' | 'Lainnya'
$keterangan  = trim($_POST['keterangan'] ?? '');      // opsional

if ($id <= 0) {
    alertGagal('dataduk.php', 'ID pegawai tidak valid!');
    exit;
}

if (!in_array($aksi, ['nonaktif', 'reaktif'])) {
    alertGagal('dataduk.php', 'Aksi tidak valid!');
    exit;
}

// ── Ambil data pegawai dari DB ────────────────────────────────────────────────
$stmt = $koneksi->prepare("SELECT id, nama, nip, status_pegawai, nonaktif_at FROM duk WHERE id = ? AND deleted_at IS NULL LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    alertGagal('dataduk.php', 'Data pegawai tidak ditemukan!');
    exit;
}

$pegawai = $result->fetch_assoc();
$stmt->close();

$nama_pegawai = $pegawai['nama'];
$id_user      = $_SESSION['user_id'] ?? null;   // ID user yang login

// ============================================================================
// AKSI: NONAKTIFKAN
// ============================================================================
if ($aksi === 'nonaktif') {

    // Cek sudah nonaktif
    if ($pegawai['status_pegawai'] === 'nonaktif') {
        alertGagal('dataduk.php', "Pegawai $nama_pegawai sudah berstatus nonaktif!");
        exit;
    }

    // Validasi alasan wajib
    $alasan_valid = ['Pensiun', 'Pindah', 'Lainnya'];
    if (!in_array($alasan, $alasan_valid)) {
        alertGagal('dataduk.php', 'Alasan nonaktif tidak valid! Pilih: Pensiun, Pindah, atau Lainnya.');
        exit;
    }

    // ── Cek apakah pegawai masih punya usulan aktif ───────────────────────────
    // Cek kenaikan pangkat aktif
    $stmt_cek = $koneksi->prepare("
        SELECT COUNT(*) as total FROM kenaikan_pangkat 
        WHERE nip = ? AND status IN ('draft','diajukan') AND deleted_at IS NULL
    ");
    $stmt_cek->bind_param('s', $pegawai['nip']);
    $stmt_cek->execute();
    $total_kp = $stmt_cek->get_result()->fetch_assoc()['total'];
    $stmt_cek->close();

    // Cek usulan pensiun aktif
    $stmt_cek2 = $koneksi->prepare("
        SELECT COUNT(*) as total FROM usulan_pensiun 
        WHERE nip = ? AND status IN ('draft','diajukan') AND deleted_at IS NULL
    ");
    $stmt_cek2->bind_param('s', $pegawai['nip']);
    $stmt_cek2->execute();
    $total_up = $stmt_cek2->get_result()->fetch_assoc()['total'];
    $stmt_cek2->close();

    if ($total_kp > 0 || $total_up > 0) {
        $jenis_aktif = [];
        if ($total_kp > 0) $jenis_aktif[] = "Kenaikan Pangkat ($total_kp usulan)";
        if ($total_up > 0) $jenis_aktif[] = "Pensiun ($total_up usulan)";

        alertGagal(
            'dataduk.php',
            "Tidak dapat menonaktifkan $nama_pegawai karena masih memiliki usulan aktif: "
            . implode(', ', $jenis_aktif)
            . ". Selesaikan atau batalkan usulan tersebut terlebih dahulu."
        );
        exit;
    }

    // ── Update status ke nonaktif ─────────────────────────────────────────────
    $stmt_update = $koneksi->prepare("
        UPDATE duk 
        SET status_pegawai  = 'nonaktif',
            alasan_nonaktif = ?,
            nonaktif_at     = NOW(),
            nonaktif_by     = ?
        WHERE id = ?
    ");
    $stmt_update->bind_param('sii', $alasan, $id_user, $id);

    if ($stmt_update->execute()) {
        $stmt_update->close();

        // ── Log ke deleted_records_log (reuse tabel yang sudah ada) ──────────
        $log_data = json_encode([
            'id'             => $pegawai['id'],
            'nama'           => $nama_pegawai,
            'nip'            => $pegawai['nip'],
            'aksi'           => 'nonaktif',
            'alasan'         => $alasan,
            'keterangan'     => $keterangan,
            'nonaktif_by'    => $id_user,
        ]);

        $expired_at = date('Y-m-d H:i:s', strtotime('+6 months'));

        $stmt_log = $koneksi->prepare("
            INSERT INTO deleted_records_log 
                (table_name, record_id, record_data, deleted_at, deleted_by, delete_reason, permanent_delete_at)
            VALUES 
                ('duk_nonaktif', ?, ?, NOW(), ?, ?, ?)
        ");
        $alasan_log = "NONAKTIF: $alasan" . (!empty($keterangan) ? " - $keterangan" : '');
        $stmt_log->bind_param('isiss', $id, $log_data, $id_user, $alasan_log, $expired_at);
//                     ^ i=id, s=log_data, i=id_user, s=alasan_log, s=expired_at → 5 param
        $stmt_log->execute();
        $stmt_log->close();

        $koneksi->close();

        alertSuksesUbah(
            'dataduk.php',
            "Pegawai $nama_pegawai berhasil dinonaktifkan dengan alasan: $alasan. "
            . "Data tidak dapat digunakan untuk usulan baru selama 6 bulan."
        );
        exit;

    } else {
        $error = $stmt_update->error;
        $stmt_update->close();
        $koneksi->close();
        alertGagal('dataduk.php', "Gagal menonaktifkan pegawai: $error");
        exit;
    }
}

// ============================================================================
// AKSI: REAKTIFKAN
// ============================================================================
if ($aksi === 'reaktif') {

    // Cek memang sedang nonaktif
    if ($pegawai['status_pegawai'] !== 'nonaktif') {
        alertGagal('dataduk.php', "Pegawai $nama_pegawai tidak dalam status nonaktif!");
        exit;
    }

    // ── Cek apakah 6 bulan sudah lewat (opsional — bisa dilewati superadmin) ─
    $is_superadmin = ($_SESSION['role'] ?? '') === 'superadmin';

    if (!$is_superadmin && !empty($pegawai['nonaktif_at'])) {
        $ts_nonaktif  = strtotime($pegawai['nonaktif_at']);
        $ts_bisa_aktif = strtotime('+6 months', $ts_nonaktif);

        if (time() < $ts_bisa_aktif) {
            $bulan_indo = [
                1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
                5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
                9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
            ];
            $tgl = date('d', $ts_bisa_aktif) . ' '
                 . $bulan_indo[(int)date('m', $ts_bisa_aktif)] . ' '
                 . date('Y', $ts_bisa_aktif);

            alertGagal(
                'dataduk.php',
                "Pegawai $nama_pegawai belum dapat diaktifkan kembali. "
                . "Masa nonaktif 6 bulan berakhir pada $tgl. "
                . "Hubungi Superadmin jika perlu pengaktifan lebih awal."
            );
            exit;
        }
    }

    // ── Update status kembali ke aktif ────────────────────────────────────────
    $stmt_reaktif = $koneksi->prepare("
        UPDATE duk 
        SET status_pegawai  = 'aktif',
            alasan_nonaktif = NULL,
            nonaktif_at     = NULL,
            nonaktif_by     = NULL
        WHERE id = ?
    ");
    $stmt_reaktif->bind_param('i', $id);

    if ($stmt_reaktif->execute()) {
        $stmt_reaktif->close();

        // Log reaktifasi
        $log_data = json_encode([
            'id'          => $pegawai['id'],
            'nama'        => $nama_pegawai,
            'nip'         => $pegawai['nip'],
            'aksi'        => 'reaktif',
            'reaktif_by'  => $id_user,
            'keterangan'  => $keterangan,
        ]);

        $stmt_log = $koneksi->prepare("
            INSERT INTO deleted_records_log 
                (table_name, record_id, record_data, deleted_at, deleted_by, delete_reason, permanent_delete_at)
            VALUES 
                ('duk_nonaktif', ?, ?, NOW(), ?, 'REAKTIF', DATE_ADD(NOW(), INTERVAL 1 YEAR))
        ");
        $stmt_log->bind_param('isi', $id, $log_data, $id_user);
        $stmt_log->execute();
        $stmt_log->close();

        $koneksi->close();

        alertSuksesUbah('dataduk.php', "Pegawai $nama_pegawai berhasil diaktifkan kembali!");
        exit;

    } else {
        $error = $stmt_reaktif->error;
        $stmt_reaktif->close();
        $koneksi->close();
        alertGagal('dataduk.php', "Gagal mengaktifkan kembali pegawai: $error");
        exit;
    }
}
?>