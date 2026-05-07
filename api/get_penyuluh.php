<?php
// api/get_penyuluh.php
header('Content-Type: application/json');
require_once '../config/koneksi.php'; // Sesuaikan path ke file koneksi database Anda

try {
    $query = "SELECT id, nip, nama, ttl, pangkat_terakhir, golongan, jabatan_terakhir, 
    pendidikan_terakhir, prodi, jenis_kelamin, tmt_pangkat,
    status_pegawai, alasan_nonaktif
    FROM penyuluh 
    WHERE deleted_at IS NULL 
    ORDER BY nama ASC";
    
    $result = $koneksi->query($query);
    
    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
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
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$koneksi->close();
?>