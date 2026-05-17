<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/wa_functions.php';

header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Silakan login kembali.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_kenaikan_pangkat'])) {
    echo json_encode(['success' => false, 'message' => 'ID kenaikan pangkat tidak ditemukan']);
    exit;
}

$id_kenaikan_pangkat = (int)$input['id_kenaikan_pangkat'];

try {
    // ✅ FIX: JOIN ke duk untuk ambil nomor_wa
    $stmt = $koneksi->prepare("
        SELECT 
            kp.*,
            d.nomor_wa,
            DATEDIFF(kp.tmt_pangkat_baru, CURDATE()) as hari_tersisa
        FROM kenaikan_pangkat kp
        LEFT JOIN duk d ON kp.nip = d.nip
        WHERE kp.id = ?
        LIMIT 1
    ");
    $stmt->bind_param('i', $id_kenaikan_pangkat);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$data) {
        throw new Exception('Data kenaikan pangkat tidak ditemukan');
    }

    if (empty($data['nomor_wa'])) {
        throw new Exception('Nomor WhatsApp pegawai tidak ditemukan. Silakan update nomor WA di data DUK terlebih dahulu.');
    }

    if ($data['status'] !== 'disetujui') {
        throw new Exception('Hanya kenaikan pangkat yang sudah disetujui yang dapat dikirim reminder.');
    }

    // Cek apakah sudah pernah dikirim reminder
    $check = $koneksi->prepare("
        SELECT id, tanggal_kirim FROM notifikasi_wa 
        WHERE id_kenaikan_pangkat = ? 
        AND status = 'terkirim'
        AND pesan LIKE '%PENGINGAT KENAIKAN PANGKAT%'
        ORDER BY tanggal_kirim DESC
        LIMIT 1
    ");
    $check->bind_param('i', $id_kenaikan_pangkat);
    $check->execute();
    $notif_lama    = $check->get_result()->fetch_assoc();
    $sudah_terkirim = (bool)$notif_lama;
    $tanggal_terkirim = $notif_lama['tanggal_kirim'] ?? null;
    $check->close();

    // Kirim reminder
    $result_kirim = kirim_reminder_kenaikan_pangkat($data);

    // Simpan log
    $status_notif  = $result_kirim['success'] ? 'terkirim' : 'gagal';
    $response_api  = $result_kirim['response'] ?? '';
    $keterangan    = $result_kirim['message'] ?? '';
    $pesan_log     = buatPesanReminderPensiun($data, 'reminder_1_tahun'); // fallback log

    // Buat ulang pesan reminder untuk log
    $tmt_baru  = date('d/m/Y', strtotime($data['tmt_pangkat_baru']));
    $pesan_log = "*📢 PENGINGAT KENAIKAN PANGKAT*\n";
    $pesan_log .= "Yth. *{$data['nama']}* | NIP: {$data['nip']}\n";
    $pesan_log .= "Pangkat: {$data['pangkat_baru']} ({$data['golongan_baru']}) | TMT: {$tmt_baru}";

    $ins = $koneksi->prepare("
        INSERT INTO notifikasi_wa 
            (id_kenaikan_pangkat, nip, nama, nomor_wa, pesan, status, tanggal_kirim, response_api, keterangan) 
        VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)
    ");
    $ins->bind_param(
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
    $ins->execute();
    $ins->close();

    if ($result_kirim['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Reminder berhasil dikirim ke ' . $data['nama'],
            'data'    => [
                'nama'                       => $data['nama'],
                'nip'                        => $data['nip'],
                'nomor_wa'                   => $data['nomor_wa'],
                'sudah_pernah_terkirim'      => $sudah_terkirim,
                'tanggal_terkirim_sebelumnya'=> $tanggal_terkirim,
            ]
        ]);
    } else {
        throw new Exception($result_kirim['message']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$koneksi->close();