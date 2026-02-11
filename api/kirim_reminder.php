<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/wa_functions.php';

// Set header JSON
header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Session expired. Silakan login kembali.'
    ]);
    exit;
}

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Ambil data dari request
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_kenaikan_pangkat'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID kenaikan pangkat tidak ditemukan'
    ]);
    exit;
}

$id_kenaikan_pangkat = (int)$input['id_kenaikan_pangkat'];

try {
    // Ambil data kenaikan pangkat lengkap dengan nomor WA
    $query = "SELECT 
                kp.*,
                d.nomor_wa,
                DATEDIFF(kp.tmt_pangkat_baru, CURDATE()) as hari_tersisa
              FROM kenaikan_pangkat kp
              LEFT JOIN duk d ON kp.nip = d.nip
              WHERE kp.id = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id_kenaikan_pangkat);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        throw new Exception('Data kenaikan pangkat tidak ditemukan');
    }
    
    $data = mysqli_fetch_assoc($result);
    
    // Validasi nomor WA
    if (empty($data['nomor_wa'])) {
        throw new Exception('Nomor WhatsApp pegawai tidak ditemukan. Silakan update nomor WA di data DUK terlebih dahulu.');
    }
    
    // Validasi status - hanya yang disetujui yang bisa dikasih reminder
    if ($data['status'] !== 'disetujui') {
        throw new Exception('Hanya kenaikan pangkat yang sudah disetujui yang dapat dikirim reminder.');
    }
    
    // Cek apakah sudah pernah dikirim reminder
    $check_query = "SELECT id, tanggal_kirim FROM notifikasi_wa 
                    WHERE id_kenaikan_pangkat = ? 
                    AND status = 'terkirim'
                    AND pesan LIKE '%PENGINGAT KENAIKAN PANGKAT%'
                    ORDER BY tanggal_kirim DESC
                    LIMIT 1";
    
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'i', $id_kenaikan_pangkat);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    $sudah_terkirim = false;
    $tanggal_terkirim = null;
    
    if (mysqli_num_rows($check_result) > 0) {
        $notif = mysqli_fetch_assoc($check_result);
        $sudah_terkirim = true;
        $tanggal_terkirim = $notif['tanggal_kirim'];
    }
    
    // Kirim reminder menggunakan function yang sudah dibuat
    $result_kirim = kirim_reminder_kenaikan_pangkat($data);
    
    // Simpan log notifikasi
    $status_notif = $result_kirim['success'] ? 'terkirim' : 'gagal';
    $response_api = $result_kirim['response'] ?? 'null';
    $keterangan = $result_kirim['message'];
    
    // Ambil pesan yang dikirim (generate ulang untuk disimpan di log)
    $tmt_baru = date('d/m/Y', strtotime($data['tmt_pangkat_baru']));
    $pesan_log = "*📢 PENGINGAT KENAIKAN PANGKAT*\n";
    $pesan_log .= "━━━━━━━━━━━━━━━━━━━━\n\n";
    $pesan_log .= "Yth. Bapak/Ibu *{$data['nama']}*\n";
    $pesan_log .= "NIP: {$data['nip']}\n\n";
    $pesan_log .= "Dengan hormat,\n\n";
    $pesan_log .= "Kami informasikan bahwa Anda akan memasuki periode kenaikan pangkat berikutnya:\n\n";
    $pesan_log .= "*Data Kenaikan Pangkat Terakhir:*\n";
    $pesan_log .= "📋 Pangkat: {$data['pangkat_baru']}\n";
    $pesan_log .= "📋 Golongan: {$data['golongan_baru']}\n";
    $pesan_log .= "📅 TMT: {$tmt_baru}\n\n";
    $pesan_log .= "*⏰ Perkiraan Kenaikan Pangkat Berikutnya:*\n";
    $pesan_log .= "Sekitar *1 tahun lagi* dari sekarang\n\n";
    $pesan_log .= "*📝 Persiapan yang Perlu Dilakukan:*\n";
    $pesan_log .= "✅ SKP 2 tahun terakhir dengan nilai minimal Baik\n";
    $pesan_log .= "✅ Surat pernyataan melaksanakan tugas\n";
    $pesan_log .= "✅ Fotocopy SK pangkat terakhir\n";
    $pesan_log .= "✅ Fotocopy ijazah terakhir yang dilegalisir\n";
    $pesan_log .= "✅ Pas foto terbaru 3x4 (2 lembar)\n\n";
    $pesan_log .= "Silakan hubungi bagian kepegawaian untuk informasi lebih lanjut atau login ke sistem untuk cek detail.\n\n";
    $pesan_log .= "---\n";
    $pesan_log .= "Pesan otomatis dari Sistem Informasi Kepegawaian\n";
    $pesan_log .= "DPPKBPM Kota Banjarmasin";
    
    // Insert log notifikasi
    $insert_query = "INSERT INTO notifikasi_wa 
                    (id_kenaikan_pangkat, nip, nama, nomor_wa, pesan, status, tanggal_kirim, response_api, keterangan) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
    
    $insert_stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param(
        $insert_stmt, 
        'isssssss',
        $id_kenaikan_pangkat,
        $data['nip'],
        $data['nama'],
        $data['nomor_wa'],
        $pesan_log,
        $status_notif,
        $response_api,
        $keterangan
    );
    
    if (!mysqli_stmt_execute($insert_stmt)) {
        throw new Exception('Gagal menyimpan log notifikasi: ' . mysqli_error($conn));
    }
    
    // Response
    if ($result_kirim['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Reminder berhasil dikirim ke ' . $data['nama'],
            'data' => [
                'nama' => $data['nama'],
                'nip' => $data['nip'],
                'nomor_wa' => $data['nomor_wa'],
                'sudah_pernah_terkirim' => $sudah_terkirim,
                'tanggal_terkirim_sebelumnya' => $tanggal_terkirim
            ]
        ]);
    } else {
        throw new Exception($result_kirim['message']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn);