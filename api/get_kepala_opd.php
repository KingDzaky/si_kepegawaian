<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $query = "SELECT * FROM kepala_opd ORDER BY status DESC, tmt_jabatan DESC";
    $result = $koneksi->query($query);
    
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Format nama lengkap
            $nama_lengkap = '';
            if (!empty($row['gelar_depan'])) {
                $nama_lengkap .= $row['gelar_depan'] . ' ';
            }
            $nama_lengkap .= $row['nama'];
            if (!empty($row['gelar_belakang'])) {
                $nama_lengkap .= ', ' . $row['gelar_belakang'];
            }
            
            $row['nama_lengkap'] = $nama_lengkap;
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
        'message' => $e->getMessage()
    ]);
}
?>