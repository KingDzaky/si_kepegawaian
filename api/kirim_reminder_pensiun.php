<?php
header('Content-Type: application/json');

session_start();
require_once '../check_session.php';
require_once '../config/koneksi.php';

// Cek akses
if (!isAdmin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Akses ditolak'
    ]);
    exit;
}

// Ambil data dari request
$input = json_decode(file_get_contents('php://input'), true);
$id_usulan_pensiun = $input['id_usulan_pensiun'] ?? 0;
$jenis_reminder = $input['jenis_reminder'] ?? '';

// Validasi
if ($id_usulan_pensiun <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID usulan tidak valid'
    ]);
    exit;
}

if (!in_array($jenis_reminder, ['1_tahun', '1_bulan', '1_minggu'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Jenis reminder tidak valid'
    ]);
    exit;
}

// Ambil data usulan pensiun
$query = "SELECT * FROM usulan_pensiun WHERE id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id_usulan_pensiun);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Data usulan tidak ditemukan'
    ]);
    exit;
}

$usulan = $result->fetch_assoc();

// Cek apakah nomor WA ada
if (empty($usulan['nomor_wa'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Nomor WhatsApp tidak tersedia untuk pegawai ini'
    ]);
    exit;
}

// Mapping jenis reminder ke field di database
$jenis_notif_map = [
    '1_tahun' => 'reminder_1_tahun',
    '1_bulan' => 'reminder_1_bulan',
    '1_minggu' => 'reminder_1_minggu'
];
$jenis_notif = $jenis_notif_map[$jenis_reminder];

// Cek apakah reminder sudah pernah dikirim
$check_query = "SELECT COUNT(*) as total FROM notifikasi_pensiun 
                WHERE id_usulan_pensiun = ? 
                AND jenis_notif = ? 
                AND status = 'terkirim'";
$check_stmt = $koneksi->prepare($check_query);
$check_stmt->bind_param("is", $id_usulan_pensiun, $jenis_notif);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$check_data = $check_result->fetch_assoc();
$sudah_terkirim = ($check_data['total'] > 0);

// Siapkan pesan sesuai jenis reminder
$waktu_text = [
    '1_tahun' => 'SATU TAHUN',
    '1_bulan' => 'SATU BULAN',
    '1_minggu' => 'SATU MINGGU'
];

$pesan = "🔔 *PENGINGAT PERSIAPAN PENSIUN*\n\n";
$pesan .= "Yth. Bapak/Ibu *{$usulan['nama']}*\n";
$pesan .= "NIP: {$usulan['nip']}\n\n";
$pesan .= "Dengan hormat, kami sampaikan bahwa masa pensiun Bapak/Ibu akan tiba dalam waktu *{$waktu_text[$jenis_reminder]}* lagi.\n\n";
$pesan .= "📅 *Tanggal Pensiun:* " . date('d F Y', strtotime($usulan['tanggal_pensiun'])) . "\n";
$pesan .= "📋 *Jenis Pensiun:* {$usulan['jenis_pensiun']}\n\n";

if ($jenis_reminder === '1_tahun') {
    $pesan .= "Mohon segera mempersiapkan berkas-berkas berikut:\n";
    $pesan .= "✅ Surat Pengantar dari OPD\n";
    $pesan .= "✅ Surat Pernyataan tidak sedang menjalani proses pidana\n";
    $pesan .= "✅ FC SK CPNS & PNS\n";
    $pesan .= "✅ FC SK Pangkat Terakhir\n";
    $pesan .= "✅ FC Ijazah & Transkrip Nilai\n";
    $pesan .= "✅ FC Kartu Keluarga & KTP\n";
    $pesan .= "✅ Pas Foto 4x6 (6 lembar)\n";
} else if ($jenis_reminder === '1_bulan') {
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
// TODO: Ganti dengan token Wablas yang sebenarnya
$wablas_token = 'YOUR_WABLAS_TOKEN_HERE';
$wablas_url = 'https://console.wablas.com/api/send-message';

$nomor_wa = $usulan['nomor_wa'];
// Pastikan format nomor dimulai dengan 62
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

// Simpan log notifikasi
$status_notif = ($http_code == 200 && isset($response_data['status']) && $response_data['status']) ? 'terkirim' : 'gagal';
$tanggal_kirim = ($status_notif === 'terkirim') ? date('Y-m-d H:i:s') : null;
$keterangan = $sudah_terkirim ? 'Reminder dikirim ulang' : 'Reminder pertama kali';

$insert_query = "INSERT INTO notifikasi_pensiun 
                 (id_usulan_pensiun, nip, nomor_wa, jenis_notif, pesan, status, tanggal_kirim, response_api, keterangan)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$insert_stmt = $koneksi->prepare($insert_query);
$response_json = json_encode($response_data);
$insert_stmt->bind_param(
    "issssssss",
    $id_usulan_pensiun,
    $usulan['nip'],
    $usulan['nomor_wa'],
    $jenis_notif,
    $pesan,
    $status_notif,
    $tanggal_kirim,
    $response_json,
    $keterangan
);
$insert_stmt->execute();

if ($status_notif === 'terkirim') {
    echo json_encode([
        'success' => true,
        'message' => 'Reminder berhasil dikirim ke WhatsApp pegawai',
        'data' => [
            'jenis_reminder' => $jenis_reminder,
            'nomor_wa' => $usulan['nomor_wa'],
            'sudah_pernah_terkirim' => $sudah_terkirim
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengirim reminder. Silakan coba lagi nanti.',
        'error' => $response_data['message'] ?? 'Unknown error'
    ]);
}

$stmt->close();
$koneksi->close();
?>