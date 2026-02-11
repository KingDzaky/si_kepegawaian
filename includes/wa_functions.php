<?php
require_once __DIR__ . '/../config/wa_config.php';

/**
 * Fungsi untuk kirim WhatsApp via Wablas
 */
function kirim_wa($nomor, $pesan) {
    global $WA_CONFIG;
    
    // Validasi nomor
    if (empty($nomor)) {
        return [
            'success' => false,
            'message' => 'Nomor WhatsApp tidak valid'
        ];
    }
    
    // Validasi token
    if (empty($WA_CONFIG['token'])) {
        return [
            'success' => false,
            'message' => 'Token Wablas belum diisi!'
        ];
    }
    
    // Format nomor (hapus karakter non-digit)
    $nomor = preg_replace('/[^0-9]/', '', $nomor);
    
    // Tambahkan 62 jika diawali 0
    if (substr($nomor, 0, 1) === '0') {
        $nomor = '62' . substr($nomor, 1);
    }
    
    // Data yang akan dikirim
    $data = [
        'phone' => $nomor,
        'message' => $pesan,
        'secret' => false,
        'retry' => true,
        'isGroup' => false
    ];
    
    // Kirim via cURL
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $WA_CONFIG['api_url'] . '/api/send-message',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . $WA_CONFIG['token'],
            'Content-Type: application/json'
        ],
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    curl_close($curl);
    
    // Handle error
    if ($err) {
        return [
            'success' => false,
            'message' => 'cURL Error: ' . $err,
            'response' => null
        ];
    }
    
    // Parse response
    $result = json_decode($response, true);
    
    if ($httpCode == 200 && isset($result['status']) && $result['status'] === true) {
        return [
            'success' => true,
            'message' => 'Pesan berhasil dikirim via Wablas',
            'response' => $response
        ];
    } else {
        $errorMsg = isset($result['message']) ? $result['message'] : 'Gagal mengirim pesan';
        return [
            'success' => false,
            'message' => 'HTTP ' . $httpCode . ': ' . $errorMsg,
            'response' => $response
        ];
    }
}

/**
 * Fungsi untuk kirim REMINDER Kenaikan Pangkat
 * Dikirim 1 tahun sebelum TMT kenaikan pangkat
 */
function kirim_reminder_kenaikan_pangkat($data_kenaikan_pangkat) {
    // Validasi data
    if (!isset($data_kenaikan_pangkat['nomor_wa']) || empty($data_kenaikan_pangkat['nomor_wa'])) {
        return [
            'success' => false,
            'message' => 'Nomor WhatsApp pegawai tidak ditemukan. Silakan update nomor WA di data DUK.'
        ];
    }
    
    // Format tanggal
    $tmt_baru = date('d/m/Y', strtotime($data_kenaikan_pangkat['tmt_pangkat_baru']));
    $tanggal_reminder = date('d/m/Y', strtotime('-1 year', strtotime($data_kenaikan_pangkat['tmt_pangkat_baru'])));
    
    // Hitung berapa tahun lagi naik pangkat berikutnya
    // Misal: dari III/a ke III/b biasanya 4 tahun
    $tahun_mk = isset($data_kenaikan_pangkat['mk_golongan_tahun']) ? $data_kenaikan_pangkat['mk_golongan_tahun'] : 4;
    
    // Template pesan reminder
    $pesan = "*📢 PENGINGAT KENAIKAN PANGKAT*\n";
    $pesan .= "━━━━━━━━━━━━━━━━━━━━\n\n";
    $pesan .= "Yth. Bapak/Ibu *{$data_kenaikan_pangkat['nama']}*\n";
    $pesan .= "NIP: {$data_kenaikan_pangkat['nip']}\n\n";
    $pesan .= "Dengan hormat,\n\n";
    $pesan .= "Kami informasikan bahwa Anda akan memasuki periode kenaikan pangkat berikutnya:\n\n";
    
    $pesan .= "*Data Kenaikan Pangkat Terakhir:*\n";
    $pesan .= "📋 Pangkat: {$data_kenaikan_pangkat['pangkat_baru']}\n";
    $pesan .= "📋 Golongan: {$data_kenaikan_pangkat['golongan_baru']}\n";
    $pesan .= "📅 TMT: {$tmt_baru}\n\n";
    
    $pesan .= "*⏰ Perkiraan Kenaikan Pangkat Berikutnya:*\n";
    $pesan .= "Sekitar *1 tahun lagi* dari sekarang\n\n";
    
    $pesan .= "*📝 Persiapan yang Perlu Dilakukan:*\n";
    $pesan .= "✅ SKP 2 tahun terakhir dengan nilai minimal Baik\n";
    $pesan .= "✅ Surat pernyataan melaksanakan tugas\n";
    $pesan .= "✅ Fotocopy SK pangkat terakhir\n";
    $pesan .= "✅ Fotocopy ijazah terakhir yang dilegalisir\n";
    $pesan .= "✅ Pas foto terbaru 3x4 (2 lembar)\n\n";
    
    $pesan .= "Silakan hubungi bagian kepegawaian untuk informasi lebih lanjut atau login ke sistem untuk cek detail.\n\n";
    $pesan .= "---\n";
    $pesan .= "Pesan otomatis dari Sistem Informasi Kepegawaian\n";
    $pesan .= "DPPKBPM Kota Banjarmasin";
    
    // Kirim WhatsApp
    return kirim_wa($data_kenaikan_pangkat['nomor_wa'], $pesan);
}

/**
 * Fungsi untuk kirim notifikasi persetujuan kenaikan pangkat
 * (Function yang sudah ada sebelumnya)
 */
function kirim_notifikasi_kenaikan_pangkat($data_kenaikan_pangkat, $status = 'disetujui') {
    // Validasi data
    if (!isset($data_kenaikan_pangkat['nomor_wa']) || empty($data_kenaikan_pangkat['nomor_wa'])) {
        return [
            'success' => false,
            'message' => 'Nomor WhatsApp pegawai tidak ditemukan'
        ];
    }
    
    // Format tanggal
    $tgl_usulan = date('d/m/Y', strtotime($data_kenaikan_pangkat['tanggal_usulan']));
    $tmt_baru = date('d/m/Y', strtotime($data_kenaikan_pangkat['tmt_pangkat_baru']));
    
    // Status emoji
    $status_emoji = $status === 'disetujui' ? '✅' : '❌';
    $status_text = strtoupper($status);
    
    // Template pesan
    $pesan = "*NOTIFIKASI KENAIKAN PANGKAT*\n";
    $pesan .= "━━━━━━━━━━━━━━━━━━━━\n\n";
    $pesan .= "Yth. Bapak/Ibu *{$data_kenaikan_pangkat['nama']}*\n";
    $pesan .= "NIP: {$data_kenaikan_pangkat['nip']}\n\n";
    $pesan .= "Dengan hormat,\n\n";
    $pesan .= "Kami informasikan bahwa usulan kenaikan pangkat Anda telah *{$status_text}* {$status_emoji}\n\n";
    
    $pesan .= "*Detail Usulan:*\n";
    $pesan .= "📋 Nomor: {$data_kenaikan_pangkat['nomor_usulan']}\n";
    $pesan .= "📅 Tanggal: {$tgl_usulan}\n\n";
    
    $pesan .= "*Kenaikan Pangkat:*\n";
    $pesan .= "Dari: {$data_kenaikan_pangkat['pangkat_lama']} ({$data_kenaikan_pangkat['golongan_lama']})\n";
    $pesan .= "Ke: *{$data_kenaikan_pangkat['pangkat_baru']}* (*{$data_kenaikan_pangkat['golongan_baru']}*)\n";
    $pesan .= "TMT: {$tmt_baru}\n\n";
    
    if ($status === 'ditolak' && !empty($data_kenaikan_pangkat['keterangan'])) {
        $pesan .= "*Keterangan:*\n{$data_kenaikan_pangkat['keterangan']}\n\n";
    }
    
    $pesan .= "Silakan login ke sistem untuk melihat detail lengkap.\n\n";
    $pesan .= "\n---\n";
    $pesan .= "Pesan otomatis dari Sistem Informasi Kepegawaian\n";
    $pesan .= "DPPKBPM Kota Banjarmasin";
    
    // Kirim WhatsApp
    return kirim_wa($data_kenaikan_pangkat['nomor_wa'], $pesan);
}