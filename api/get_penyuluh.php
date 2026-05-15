<?php
// api/get_penyuluh.php
header('Content-Type: application/json');
require_once '../config/koneksi.php';

try {
    $query = "SELECT id, nip, nama, ttl, pangkat_terakhir, golongan, jabatan_terakhir, 
              pendidikan_terakhir, prodi, jenis_kelamin, tmt_pangkat,
              status_pegawai, alasan_nonaktif
              FROM penyuluh 
              ORDER BY nama ASC";
    
    $result = $koneksi->query($query);
    
    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            // Pastikan status_pegawai selalu ada nilainya
            $row['status_pegawai'] = $row['status_pegawai'] ?? 'aktif';
            $data[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $data,
            'total' => count($data)
        ]);
    } else {
        throw new Exception($koneksi->error);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$koneksi->close();
?>