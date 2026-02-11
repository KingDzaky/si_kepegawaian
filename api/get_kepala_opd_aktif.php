<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $query = "SELECT * FROM kepala_opd WHERE status = 'aktif' ORDER BY tmt_jabatan DESC LIMIT 1";
    $result = $koneksi->query($query);
    
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        // Format nama lengkap dengan gelar
        $nama_lengkap = '';
        if (!empty($data['gelar_depan'])) {
            $nama_lengkap .= $data['gelar_depan'] . ' ';
        }
        $nama_lengkap .= $data['nama'];
        if (!empty($data['gelar_belakang'])) {
            $nama_lengkap .= ', ' . $data['gelar_belakang'];
        }
        
        $data['nama_lengkap'] = $nama_lengkap;
        
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Tidak ada kepala OPD aktif'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>