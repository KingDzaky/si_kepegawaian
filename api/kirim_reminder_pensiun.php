<?php
header('Content-Type: application/json');

session_start();
require_once '../check_session.php';
require_once '../config/koneksi.php';
require_once '../includes/wa_functions.php';

// Cek akses
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

// Ambil data dari request
$input             = json_decode(file_get_contents('php://input'), true);
$id_usulan_pensiun = (int)($input['id_usulan_pensiun'] ?? 0);
$jenis_reminder    = $input['jenis_reminder'] ?? '';

// Validasi
if ($id_usulan_pensiun <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID usulan tidak valid']);
    exit;
}

if (!in_array($jenis_reminder, ['1_tahun', '1_bulan', '1_minggu'])) {
    echo json_encode(['success' => false, 'message' => 'Jenis reminder tidak valid']);
    exit;
}

// ✅ FIX: JOIN ke tabel duk untuk ambil nomor_wa
$stmt = $koneksi->prepare("
    SELECT up.*,
        CASE 
            WHEN up.sumber_data = 'penyuluh' THEN p.nomor_wa
            ELSE d.nomor_wa
        END AS nomor_wa
    FROM usulan_pensiun up
    LEFT JOIN duk d ON d.nip = up.nip AND d.deleted_at IS NULL
    LEFT JOIN penyuluh p ON p.nip = up.nip AND p.deleted_at IS NULL
    WHERE up.id = ?
    LIMIT 1
");

$stmt->bind_param('i', $id_usulan_pensiun);
$stmt->execute();
$usulan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usulan) {
    echo json_encode(['success' => false, 'message' => 'Data usulan tidak ditemukan']);
    exit;
}

// ✅ FIX: variabel yang benar adalah $usulan['nomor_wa']
if (empty($usulan['nomor_wa'])) {
    echo json_encode(['success' => false, 'message' => 'Nomor WhatsApp tidak tersedia untuk pegawai ini. Silakan isi di Data DUK.']);
    exit;
}

// Mapping jenis reminder ke field log
$jenis_notif_map = [
    '1_tahun'  => 'reminder_1_tahun',
    '1_bulan'  => 'reminder_1_bulan',
    '1_minggu' => 'reminder_1_minggu',
];
$jenis_notif = $jenis_notif_map[$jenis_reminder];

// Cek apakah sudah pernah dikirim
$check = $koneksi->prepare("
    SELECT COUNT(*) as total FROM notifikasi_pensiun 
    WHERE id_usulan_pensiun = ? AND jenis_notif = ? AND status = 'terkirim'
");
$check->bind_param('is', $id_usulan_pensiun, $jenis_notif);
$check->execute();
$sudah_terkirim = (bool)$check->get_result()->fetch_assoc()['total'];
$check->close();

// ✅ Buat pesan lewat helper di wa_functions.php
$pesan = buatPesanReminderPensiun($usulan, $jenis_notif);

// ✅ FIX: kirim ke $usulan['nomor_wa'], bukan $nomor_wa yang tidak terdefinisi
$hasil       = kirimWA($usulan['nomor_wa'], $pesan);
$status_notif = $hasil['success'] ? 'terkirim' : 'gagal';
$tanggal_kirim = $hasil['success'] ? date('Y-m-d H:i:s') : null;
$keterangan  = $sudah_terkirim ? 'Dikirim ulang' : 'Pertama kali';

// Simpan log
$ins = $koneksi->prepare("
    INSERT INTO notifikasi_pensiun 
        (id_usulan_pensiun, nip, nomor_wa, jenis_notif, pesan, status, tanggal_kirim, response_api, keterangan)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$response_json = $hasil['response'] ?? '';
$ins->bind_param(
    'issssssss',
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
$ins->execute();
$ins->close();

if ($hasil['success']) {
    echo json_encode([
        'success' => true,
        'message' => 'Reminder berhasil dikirim ke WhatsApp pegawai',
        'data'    => [
            'jenis_reminder'         => $jenis_reminder,
            'nomor_wa'               => $usulan['nomor_wa'],
            'sudah_pernah_terkirim'  => $sudah_terkirim,
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengirim reminder: ' . $hasil['message'],
    ]);
}

$koneksi->close();