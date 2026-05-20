<?php
require_once __DIR__ . '/../config/wa_config.php';

/**
 * ============================================================
 * CORE: Kirim pesan WhatsApp via Wablas
 * Fix utama: tambah secret key di header (solusi error 403)
 * ============================================================
 */
function kirimWA($nomor, $pesan) {
    // Validasi nomor
    if (empty($nomor)) {
        return ['success' => false, 'message' => 'Nomor WA kosong', 'response' => null];
    }

    // Validasi token
    if (!defined('WABLAS_TOKEN') || empty(WABLAS_TOKEN)) {
        return ['success' => false, 'message' => 'Token Wablas belum diisi di wa_config.php', 'response' => null];
    }

    // Normalisasi nomor: 08xxx → 628xxx
    $nomor = preg_replace('/[^0-9]/', '', $nomor);
    if (substr($nomor, 0, 1) === '0') {
        $nomor = '62' . substr($nomor, 1);
    } elseif (substr($nomor, 0, 2) !== '62') {
        $nomor = '62' . $nomor;
    }

    $url = rtrim(WABLAS_URL, '/') . '/api/send-message';

    $body = json_encode([
        'phone'   => $nomor,
        'message' => $pesan,
        'retry'   => true,
        'isGroup' => false
    ]);

    // =====================================================
    // FIX 403: Wablas butuh secret key di header
    // Authorization format: TOKEN.SECRET_KEY
    // =====================================================
    $secret = defined('WABLAS_SECRET_KEY') ? WABLAS_SECRET_KEY : '';
    $auth   = !empty($secret) ? WABLAS_TOKEN . '.' . $secret : WABLAS_TOKEN;

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            'Authorization: ' . $auth,
            'Content-Type: application/json',
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err      = curl_error($ch);
    curl_close($ch);

    // cURL error
    if ($err) {
        return [
            'success'  => false,
            'message'  => 'cURL Error: ' . $err,
            'response' => null
        ];
    }

    $result = json_decode($response, true);

    // Sukses: HTTP 200 dan status true dari Wablas
    if ($httpCode === 200 && !empty($result['status'])) {
        return [
            'success'  => true,
            'message'  => 'Pesan berhasil dikirim',
            'response' => $response
        ];
    }

    // Gagal
    $errMsg = 'HTTP ' . $httpCode;
    if (!empty($result['message'])) {
        $errMsg .= ': ' . $result['message'];
    } elseif (!empty($result['reason'])) {
        $errMsg .= ': ' . $result['reason'];
    }

    return [
        'success'  => false,
        'message'  => $errMsg,
        'response' => $response
    ];
}

/**
 * Alias agar file lain yang pakai kirim_wa() tetap jalan
 */
function kirim_wa($nomor, $pesan) {
    return kirimWA($nomor, $pesan);
}

/**
 * ============================================================
 * Simpan log notifikasi ke tabel notifikasi_wa
 * ============================================================
 */
function simpanLogNotifikasi($koneksi, $id_kenaikan, $nip, $nama, $nomor_wa, $pesan, $status, $response, $keterangan = '') {
    $stmt = $koneksi->prepare("
        INSERT INTO notifikasi_wa 
            (id_kenaikan_pangkat, nip, nama, nomor_wa, pesan, status, tanggal_kirim, response_api, keterangan)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)
    ");
    $stmt->bind_param('isssssss',
        $id_kenaikan, $nip, $nama, $nomor_wa,
        $pesan, $status, $response, $keterangan
    );
    $stmt->execute();
    $stmt->close();
}

/**
 * ============================================================
 * Kirim + simpan log sekaligus (helper praktis)
 * ============================================================
 */
function kirimDanLog($koneksi, $id_kenaikan, $nip, $nama, $nomor_wa, $pesan) {
    if (empty($nomor_wa)) {
        simpanLogNotifikasi($koneksi, $id_kenaikan, $nip, $nama, '-', $pesan, 'gagal', '', 'Nomor WA kosong');
        return false;
    }

    $result = kirimWA($nomor_wa, $pesan);
    $status = $result['success'] ? 'terkirim' : 'gagal';

    simpanLogNotifikasi(
        $koneksi, $id_kenaikan, $nip, $nama,
        $nomor_wa, $pesan, $status,
        $result['response'] ?? '',
        $result['message'] ?? ''
    );

    return $result['success'];
}

/**
 * ============================================================
 * Kirim NOTIFIKASI persetujuan/penolakan kenaikan pangkat
 * Parameter: $koneksi (DB), $id_kenaikan_pangkat (int)
 * ============================================================
 */
function kirim_notifikasi_kenaikan_pangkat($koneksi, $id_kenaikan_pangkat) {
    // Ambil data lengkap dari DB
    $stmt = $koneksi->prepare("
        SELECT kp.*, d.nomor_wa
        FROM kenaikan_pangkat kp
        LEFT JOIN duk d ON kp.nip = d.nip AND d.deleted_at IS NULL
        WHERE kp.id = ?
        LIMIT 1
    ");
    $stmt->bind_param('i', $id_kenaikan_pangkat);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$data) {
        return ['success' => false, 'message' => 'Data kenaikan pangkat tidak ditemukan (ID: ' . $id_kenaikan_pangkat . ')'];
    }

    if (empty($data['nomor_wa'])) {
        return ['success' => false, 'message' => 'Nomor WhatsApp pegawai belum diisi di data DUK.'];
    }

    $status        = $data['status'] ?? 'disetujui';
    $status_emoji  = $status === 'disetujui' ? '✅' : '❌';
    $status_text   = strtoupper($status);
    $tgl_usulan    = date('d/m/Y', strtotime($data['tanggal_usulan']));
    $tmt_baru      = date('d/m/Y', strtotime($data['tmt_pangkat_baru']));

    $pesan  = "*NOTIFIKASI KENAIKAN PANGKAT*\n";
    $pesan .= "━━━━━━━━━━━━━━━━━━━━\n\n";
    $pesan .= "Yth. Bapak/Ibu *{$data['nama']}*\n";
    $pesan .= "NIP: {$data['nip']}\n\n";
    $pesan .= "Kami informasikan bahwa usulan kenaikan pangkat Anda telah *{$status_text}* {$status_emoji}\n\n";
    $pesan .= "*Detail Usulan:*\n";
    $pesan .= "📋 Nomor  : {$data['nomor_usulan']}\n";
    $pesan .= "📅 Tanggal: {$tgl_usulan}\n\n";
    $pesan .= "*Kenaikan Pangkat:*\n";
    $pesan .= "Dari : {$data['pangkat_lama']} ({$data['golongan_lama']})\n";
    $pesan .= "Ke   : *{$data['pangkat_baru']}* (*{$data['golongan_baru']}*)\n";
    $pesan .= "TMT  : {$tmt_baru}\n\n";

    if ($status === 'ditolak' && !empty($data['keterangan'])) {
        $pesan .= "*Keterangan:*\n{$data['keterangan']}\n\n";
    }

    $pesan .= "Silakan login ke sistem untuk melihat detail lengkap.\n";
    $pesan .= "\n---\nPesan otomatis dari Sistem Informasi Kepegawaian\nDPPKBPM Kota Banjarmasin";

    $result = kirimWA($data['nomor_wa'], $pesan);

    // Simpan log
    simpanLogNotifikasi(
        $koneksi,
        $id_kenaikan_pangkat,
        $data['nip'],
        $data['nama'],
        $data['nomor_wa'],
        $pesan,
        $result['success'] ? 'terkirim' : 'gagal',
        $result['response'] ?? '',
        $result['message'] ?? ''
    );

    return $result;
}

/**
 * ============================================================
 * Kirim REMINDER kenaikan pangkat (~1 tahun sebelum TMT)
 * Parameter: $data = row dari query kenaikan_pangkat JOIN duk
 * ============================================================
 */
function kirim_reminder_kenaikan_pangkat($data) {
    if (empty($data['nomor_wa'])) {
        return [
            'success' => false,
            'message' => 'Nomor WhatsApp belum diisi di data DUK.'
        ];
    }

    $tmt_baru = date('d/m/Y', strtotime($data['tmt_pangkat_baru']));

    $pesan  = "*📢 PENGINGAT KENAIKAN PANGKAT*\n";
    $pesan .= "━━━━━━━━━━━━━━━━━━━━\n\n";
    $pesan .= "Yth. Bapak/Ibu *{$data['nama']}*\n";
    $pesan .= "NIP: {$data['nip']}\n\n";
    $pesan .= "Kami informasikan bahwa Anda akan memasuki periode kenaikan pangkat berikutnya:\n\n";
    $pesan .= "*Data Kenaikan Pangkat Terakhir:*\n";
    $pesan .= "📋 Pangkat : {$data['pangkat_baru']}\n";
    $pesan .= "📋 Golongan: {$data['golongan_baru']}\n";
    $pesan .= "📅 TMT     : {$tmt_baru}\n\n";
    $pesan .= "*⏰ Perkiraan Kenaikan Pangkat Berikutnya:*\n";
    $pesan .= "Sekitar *1 tahun lagi* dari sekarang\n\n";
    $pesan .= "*📝 Persiapan yang Perlu Dilakukan:*\n";
    $pesan .= "✅ SKP 2 tahun terakhir nilai minimal Baik\n";
    $pesan .= "✅ Surat pernyataan melaksanakan tugas\n";
    $pesan .= "✅ Fotocopy SK pangkat terakhir\n";
    $pesan .= "✅ Fotocopy ijazah terakhir yang dilegalisir\n";
    $pesan .= "✅ Pas foto terbaru 3x4 (2 lembar)\n\n";
    $pesan .= "Silakan hubungi bagian kepegawaian atau login ke sistem.\n\n";
    $pesan .= "---\nPesan otomatis dari Sistem Informasi Kepegawaian\nDPPKBPM Kota Banjarmasin";

    return kirimWA($data['nomor_wa'], $pesan);
}

/**
 * ============================================================
 * Buat pesan reminder pensiun sesuai jenis
 * ============================================================
 */
function buatPesanReminderPensiun($row, $jenis) {
    $waktu_map = [
        'reminder_1_tahun'  => 'SATU TAHUN',
        'reminder_1_bulan'  => 'SATU BULAN',
        'reminder_1_minggu' => 'SATU MINGGU',
    ];

    $pesan  = "🔔 *PENGINGAT PERSIAPAN PENSIUN*\n\n";
    $pesan .= "Yth. Bapak/Ibu *{$row['nama']}*\n";
    $pesan .= "NIP: {$row['nip']}\n\n";
    $pesan .= "Dengan hormat, kami sampaikan bahwa masa pensiun Bapak/Ibu akan tiba dalam waktu ";
    $pesan .= "*{$waktu_map[$jenis]}* lagi.\n\n";
    $pesan .= "📅 *Tanggal Pensiun:* " . date('d F Y', strtotime($row['tanggal_pensiun'])) . "\n";
    $pesan .= "📋 *Jenis Pensiun:* {$row['jenis_pensiun']}\n\n";

    if ($jenis === 'reminder_1_tahun') {
        $pesan .= "Mohon segera mempersiapkan berkas berikut:\n";
        $pesan .= "✅ Surat Pengantar dari OPD\n";
        $pesan .= "✅ Surat Pernyataan tidak sedang menjalani proses pidana\n";
        $pesan .= "✅ FC SK CPNS & PNS\n";
        $pesan .= "✅ FC SK Pangkat Terakhir\n";
        $pesan .= "✅ FC Ijazah & Transkrip Nilai\n";
        $pesan .= "✅ FC Kartu Keluarga & KTP\n";
        $pesan .= "✅ Pas Foto 4x6 (6 lembar)\n";
    } elseif ($jenis === 'reminder_1_bulan') {
        $pesan .= "⚠️ *Segera lengkapi berkas pensiun Anda!*\n\n";
        $pesan .= "Pastikan semua dokumen telah diserahkan ke Bagian Kepegawaian.\n";
    } else {
        $pesan .= "🚨 *SEGERA!* Masa pensiun sudah sangat dekat!\n\n";
        $pesan .= "Harap segera hubungi Bagian Kepegawaian untuk memastikan administrasi selesai.\n";
    }

    $pesan .= "\n📞 Hubungi: Bagian Kepegawaian DPPKBPM\n";
    $pesan .= "🏢 DPPKBPM Kota Banjarmasin\n\n";
    $pesan .= "_Pesan otomatis dari Sistem Kepegawaian_";

    return $pesan;
}