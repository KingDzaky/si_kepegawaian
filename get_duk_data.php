<?php
require_once '../config/koneksi.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT id, nama, nip, pangkat_terakhir, golongan, jabatan_terakhir, ttl, jenis_kelamin, pendidikan_terakhir, tmt_eselon, eselon 
            FROM duk ORDER BY nama ASC";
    $result = $koneksi->query($sql);
    $data = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil data'
    ]);
}
?>