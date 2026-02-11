<?php
/**
 * CRON JOB: Kirim Reminder Pensiun Otomatis
 * 
 * Setup di crontab:
 * 0 8 * * * /usr/bin/php /path/to/cron_reminder_pensiun.php
 * (Jalankan setiap hari jam 8 pagi)
 */

require_once 'config/koneksi.php';

// Set timezone
date_default_timezone_set('Asia/Makassar');

$log_file = __DIR__ . '/logs/cron_reminder_' . date('Y-m-d') . '.log';
$log_dir = dirname($log_file);

// Buat folder logs jika belum ada
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0755, true);
}

function writeLog($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
    echo "[$timestamp] $message\n";
}

writeLog("=== START CRON REMINDER PENSIUN ===");

// Ambil usulan yang status disetujui dan belum terkirim reminder
$query = "SELECT 
            up.*,
            DATEDIFF(up.tanggal_pensiun, CURDATE()) as hari_tersisa,
            
            -- Cek reminder yang sudah terkirim
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
          WHERE up.status = 'disetujui'
          AND up.nomor_wa IS NOT NULL
          AND up.nomor_wa != ''
          AND DATEDIFF(up.tanggal_pensiun, CURDATE()) > 0
          ORDER BY up.tanggal_pensiun ASC";

$result = $koneksi->query($query);

if (!$result) {
    writeLog("ERROR: Query failed - " . $koneksi->error);
    exit;
}

$total_processed = 0;
$total_sent = 0;
$total_skipped = 0;
$total_failed = 0;

while ($row = $result->fetch_assoc()) {
    $total_processed++;
    
    $id = $row['id'];
    $nip = $row['nip'];
    $nama = $row['nama'];
    $hari = (int)$row['hari_tersisa'];
    
    writeLog("Processing: $nama (NIP: $nip) - Sisa $hari hari");
    
    // Tentukan jenis reminder yang perlu dikirim
    $reminder_to_send = [];
    
    // Cek reminder 1 tahun (350-380 hari)
    if ($hari >= 350 && $hari <= 380 && $row['reminder_1_tahun_sent'] == 0) {
        $reminder_to_send[] = [
            'jenis' => 'reminder_1_tahun',
            'label' => '1 Tahun'
        ];
    }
    
    // Cek reminder 1 bulan (20-40 hari)
    if ($hari >= 20 && $hari <= 40 && $row['reminder_1_bulan_sent'] == 0) {
        $reminder_to_send[] = [
            'jenis' => 'reminder_1_bulan',
            'label' => '1 Bulan'
        ];
    }
    
    // Cek reminder 1 minggu (3-10 hari)
    if ($hari >= 3 && $hari <= 10 && $row['reminder_1_minggu_sent'] == 0) {
        $reminder_to_send[] = [
            'jenis' => 'reminder_1_minggu',
            'label' => '1 Minggu'
        ];
    }
    
    if (empty($reminder_to_send)) {
        writeLog("  → SKIP: Tidak ada reminder yang perlu dikirim");
        $total_skipped++;
        continue;
    }
    
    // Kirim reminder
    foreach ($reminder_to_send as $reminder) {
        $jenis = $reminder['jenis'];
        $label = $reminder['label'];
        
        writeLog("  → Sending reminder: $label");
        
        // Siapkan pesan
        $waktu_map = [
            'reminder_1_tahun' => 'SATU TAHUN',
            'reminder_1_bulan' => 'SATU BULAN',
            'reminder_1_minggu' => 'SATU MINGGU'
        ];
        
        $pesan = "🔔 *PENGINGAT PERSIAPAN PENSIUN*\n\n";
        $pesan .= "Yth. Bapak/Ibu *{$row['nama']}*\n";
        $pesan .= "NIP: {$row['nip']}\n\n";
        $pesan .= "Dengan hormat, kami sampaikan bahwa masa pensiun Bapak/Ibu akan tiba dalam waktu *{$waktu_map[$jenis]}* lagi.\n\n";
        $pesan .= "📅 *Tanggal Pensiun:* " . date('d F Y', strtotime($row['tanggal_pensiun'])) . "\n";
        $pesan .= "📋 *Jenis Pensiun:* {$row['jenis_pensiun']}\n\n";
        
        if ($jenis === 'reminder_1_tahun') {
            $pesan .= "Mohon segera mempersiapkan berkas-berkas berikut:\n";
            $pesan .= "✅ Surat Pengantar dari OPD\n";
            $pesan .= "✅ Surat Pernyataan tidak sedang menjalani proses pidana\n";
            $pesan .= "✅ FC SK CPNS & PNS\n";
            $pesan .= "✅ FC SK Pangkat Terakhir\n";
            $pesan .= "✅ FC Ijazah & Transkrip Nilai\n";
            $pesan .= "✅ FC Kartu Keluarga & KTP\n";
            $pesan .= "✅ Pas Foto 4x6 (6 lembar)\n";
        } else if ($jenis === 'reminder_1_bulan') {
            $pesan .= "⚠️ *Segera lengkapi berkas pensiun Anda!*\n\n";
            $pesan .= "Pastikan semua dokumen telah dipersiapkan dan diserahkan ke Bagian Kepegawaian untuk diproses lebih lanjut.\n";
        } else {
            $pesan .= "🚨 *SEGERA!* Masa pensiun sudah sangat dekat!\n\n";
            $pesan .= "Harap segera menghubungi Bagian Kepegawaian untuk memastikan semua proses administrasi pensiun telah selesai.\n";
        }
        
        $pesan .= "\n📞 Hubungi: Bagian Kepegawaian DPPKBPM\n";
        $pesan .= "🏢 Dinas Pengendalian Penduduk, Keluarga Berencana dan Pemberdayaan Masyarakat Kota Banjarmasin\n\n";
        $pesan .= "Terima kasih atas perhatiannya.\n";
        $pesan .= "_Pesan otomatis dari Sistem Kepegawaian_";
        
        // Kirim via Wablas
        $wablas_token = 'YOUR_WABLAS_TOKEN_HERE'; // TODO: Ganti dengan token asli
        $wablas_url = 'https://console.wablas.com/api/send-message';
        
        $nomor_wa = $row['nomor_wa'];
        if (substr($nomor_wa, 0, 1) === '0') {
            $nomor_wa = '62' . substr($nomor_wa, 1);
        } else if (substr($nomor_wa, 0, 2) !== '62') {
            $nomor_wa = '62' . $nomor_wa;
        }
        
        $data_wablas = [
            'phone' => $nomor_wa,
            'message' => $pesan,
            'secret' => false,
            'retry' => false,
            'isGroup' => false
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $wablas_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data_wablas));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $wablas_token
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $response_data = json_decode($response, true);
        $status = ($http_code == 200 && isset($response_data['status']) && $response_data['status']) ? 'terkirim' : 'gagal';
        
        // Simpan log notifikasi
        $tanggal_kirim = ($status === 'terkirim') ? date('Y-m-d H:i:s') : null;
        $keterangan = "Dikirim otomatis via CRON JOB";
        
        $insert = "INSERT INTO notifikasi_pensiun 
                   (id_usulan_pensiun, nip, nomor_wa, jenis_notif, pesan, status, tanggal_kirim, response_api, keterangan)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $koneksi->prepare($insert);
        $response_json = json_encode($response_data);
        $stmt->bind_param(
            "issssssss",
            $id, $nip, $nomor_wa, $jenis, $pesan, $status, $tanggal_kirim, $response_json, $keterangan
        );
        $stmt->execute();
        
        if ($status === 'terkirim') {
            writeLog("  → SUCCESS: Reminder $label terkirim ke $nomor_wa");
            $total_sent++;
        } else {
            writeLog("  → FAILED: Gagal kirim reminder $label - " . ($response_data['message'] ?? 'Unknown error'));
            $total_failed++;
        }
    }
}

writeLog("\n=== SUMMARY ===");
writeLog("Total Processed: $total_processed");
writeLog("Total Sent: $total_sent");
writeLog("Total Skipped: $total_skipped");
writeLog("Total Failed: $total_failed");
writeLog("=== END CRON REMINDER PENSIUN ===\n");

$koneksi->close();
?>