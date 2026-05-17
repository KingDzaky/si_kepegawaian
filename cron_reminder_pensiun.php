<?php
/**
 * CRON JOB: Kirim Reminder Pensiun Otomatis
 * 
 * Setup di crontab:
 * 0 8 * * * /usr/bin/php /path/to/cron_reminder_pensiun.php
 * (Jalankan setiap hari jam 8 pagi)
 */

require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/wa_functions.php';

date_default_timezone_set('Asia/Makassar');

$log_dir  = __DIR__ . '/logs';
$log_file = $log_dir . '/cron_reminder_' . date('Y-m-d') . '.log';

if (!file_exists($log_dir)) {
    mkdir($log_dir, 0755, true);
}

function writeLog($message) {
    global $log_file;
    $ts = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$ts] $message\n", FILE_APPEND);
    echo "[$ts] $message\n";
}

writeLog("=== START CRON REMINDER PENSIUN ===");

// ✅ FIX: JOIN ke tabel duk untuk ambil nomor_wa
// (tabel usulan_pensiun tidak punya kolom nomor_wa langsung)
$query = "
    SELECT 
        up.*,
        d.nomor_wa,
        DATEDIFF(up.tanggal_pensiun, CURDATE()) as hari_tersisa,

        (SELECT COUNT(*) FROM notifikasi_pensiun 
         WHERE id_usulan_pensiun = up.id 
         AND jenis_notif = 'reminder_1_tahun'
         AND status = 'terkirim') as reminder_1_tahun_sent,

        (SELECT COUNT(*) FROM notifikasi_pensiun 
         WHERE id_usulan_pensiun = up.id 
         AND jenis_notif = 'reminder_1_bulan'
         AND status = 'terkirim') as reminder_1_bulan_sent,

        (SELECT COUNT(*) FROM notifikasi_pensiun 
         WHERE id_usulan_pensiun = up.id 
         AND jenis_notif = 'reminder_1_minggu'
         AND status = 'terkirim') as reminder_1_minggu_sent

    FROM usulan_pensiun up
    LEFT JOIN duk d ON d.nip = up.nip AND d.deleted_at IS NULL
    WHERE up.status = 'disetujui'
    AND d.nomor_wa IS NOT NULL
    AND d.nomor_wa != ''
    AND DATEDIFF(up.tanggal_pensiun, CURDATE()) > 0
    ORDER BY up.tanggal_pensiun ASC
";

$result = $koneksi->query($query);

if (!$result) {
    writeLog("ERROR: Query gagal - " . $koneksi->error);
    exit;
}

$total_processed = 0;
$total_sent      = 0;
$total_skipped   = 0;
$total_failed    = 0;

while ($row = $result->fetch_assoc()) {
    $total_processed++;

    $id       = $row['id'];
    $nip      = $row['nip'];
    $nama     = $row['nama'];
    $hari     = (int)$row['hari_tersisa'];
    // ✅ FIX: pakai $row['nomor_wa'] dari JOIN, bukan $nomor_wa
    $nomor_wa = $row['nomor_wa'];

    writeLog("Processing: $nama (NIP: $nip) - Sisa $hari hari | WA: $nomor_wa");

    // Tentukan reminder yang perlu dikirim
    $to_send = [];

    if ($hari >= 350 && $hari <= 380 && $row['reminder_1_tahun_sent'] == 0) {
        $to_send[] = 'reminder_1_tahun';
    }
    if ($hari >= 20 && $hari <= 40 && $row['reminder_1_bulan_sent'] == 0) {
        $to_send[] = 'reminder_1_bulan';
    }
    if ($hari >= 3 && $hari <= 10 && $row['reminder_1_minggu_sent'] == 0) {
        $to_send[] = 'reminder_1_minggu';
    }

    if (empty($to_send)) {
        writeLog("  → SKIP: Tidak ada reminder yang perlu dikirim");
        $total_skipped++;
        continue;
    }

    foreach ($to_send as $jenis) {
        writeLog("  → Mengirim reminder: $jenis");

        // ✅ FIX: gunakan helper buatPesanReminderPensiun dari wa_functions.php
        $pesan = buatPesanReminderPensiun($row, $jenis);

        // ✅ FIX: kirim ke $nomor_wa yang sudah terdefinisi dari JOIN
        $hasil        = kirimWA($nomor_wa, $pesan);
        $status_notif = $hasil['success'] ? 'terkirim' : 'gagal';
        $tanggal_kirim = $hasil['success'] ? date('Y-m-d H:i:s') : null;
        $response_json = $hasil['response'] ?? '';
        $keterangan    = 'Dikirim otomatis via CRON JOB';

        // Simpan log notifikasi
        $ins = $koneksi->prepare("
            INSERT INTO notifikasi_pensiun 
                (id_usulan_pensiun, nip, nomor_wa, jenis_notif, pesan, status, tanggal_kirim, response_api, keterangan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $ins->bind_param(
            'issssssss',
            $id, $nip, $nomor_wa, $jenis,
            $pesan, $status_notif, $tanggal_kirim,
            $response_json, $keterangan
        );
        $ins->execute();
        $ins->close();

        if ($hasil['success']) {
            writeLog("  → SUCCESS: Reminder $jenis terkirim ke $nomor_wa");
            $total_sent++;
        } else {
            writeLog("  → FAILED: " . $hasil['message']);
            $total_failed++;
        }
    }
}

writeLog("\n=== SUMMARY ===");
writeLog("Total Processed : $total_processed");
writeLog("Total Sent      : $total_sent");
writeLog("Total Skipped   : $total_skipped");
writeLog("Total Failed    : $total_failed");
writeLog("=== END CRON REMINDER PENSIUN ===\n");

$koneksi->close();