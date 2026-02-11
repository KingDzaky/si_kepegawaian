<?php
session_start();
require_once '../config/koneksi.php';

header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit;
}

try {
    // Ambil parameter filter
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    
    // Base query
    $query = "SELECT 
                kp.*,
                d.nomor_wa,
                DATEDIFF(kp.tmt_pangkat_baru, CURDATE()) as hari_tersisa,
                DATE_SUB(kp.tmt_pangkat_baru, INTERVAL 1 YEAR) as tanggal_reminder
              FROM kenaikan_pangkat kp
              LEFT JOIN duk d ON kp.nip = d.nip
              WHERE 1=1";
    
    // Filter berdasarkan parameter
    switch($filter) {
        case 'perlu_reminder':
            // ASN yang TMT-nya 1 tahun lagi (toleransi ±30 hari)
            $query .= " AND kp.status = 'disetujui'
                       AND DATEDIFF(kp.tmt_pangkat_baru, CURDATE()) BETWEEN 335 AND 395";
            break;
            
        case 'reminder_1_bulan':
            // ASN yang TMT-nya 1 bulan lagi dari periode 1 tahun (11 bulan dari sekarang)
            $query .= " AND kp.status = 'disetujui'
                       AND DATEDIFF(kp.tmt_pangkat_baru, CURDATE()) BETWEEN 305 AND 365";
            break;
            
        case 'disetujui':
            $query .= " AND kp.status = 'disetujui'";
            break;
            
        case 'draft':
            $query .= " AND kp.status = 'draft'";
            break;
            
        case 'diajukan':
            $query .= " AND kp.status = 'diajukan'";
            break;
            
        case 'ditolak':
            $query .= " AND kp.status = 'ditolak'";
            break;
    }
    
    $query .= " ORDER BY kp.tanggal_usulan DESC";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Format tanggal
        $row['tanggal_usulan_formatted'] = date('d/m/Y', strtotime($row['tanggal_usulan']));
        $row['tmt_pangkat_baru_formatted'] = date('d/m/Y', strtotime($row['tmt_pangkat_baru']));
        $row['tanggal_reminder_formatted'] = date('d/m/Y', strtotime($row['tanggal_reminder']));
        
        // Hitung status reminder
        $hari = (int)$row['hari_tersisa'];
        if ($hari >= 335 && $hari <= 395) {
            $row['status_reminder'] = 'perlu_reminder';
            $row['status_reminder_text'] = '🔔 Perlu Reminder';
        } elseif ($hari >= 305 && $hari <= 365) {
            $row['status_reminder_text'] = '⏰ 1 Bulan Lagi';
        } else {
            $row['status_reminder'] = 'belum';
            $row['status_reminder_text'] = '-';
        }
        
        // Cek apakah sudah pernah dikirim reminder
        $check_notif = mysqli_query($conn, "SELECT id FROM notifikasi_wa 
                                            WHERE id_kenaikan_pangkat = {$row['id']} 
                                            AND status = 'terkirim'
                                            AND pesan LIKE '%PENGINGAT KENAIKAN PANGKAT%'
                                            LIMIT 1");
        $row['reminder_terkirim'] = mysqli_num_rows($check_notif) > 0;
        
        $data[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'total' => count($data),
        'filter' => $filter
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}