<?php
require_once 'config/koneksi.php';

header('Content-Type: application/json');

$query = "SELECT nama, nip, pangkat, golongan, jabatan, gelar_depan, gelar_belakang 
          FROM kepala_opd 
          WHERE status = 'aktif' 
          LIMIT 1";

$result = $koneksi->query($query);

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    
    // Format nama lengkap dengan gelar
    $namaLengkap = '';
    if (!empty($data['gelar_depan'])) {
        $namaLengkap .= $data['gelar_depan'] . ' ';
    }
    $namaLengkap .= $data['nama'];
    if (!empty($data['gelar_belakang'])) {
        $namaLengkap .= ', ' . $data['gelar_belakang'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'nama' => $data['nama'],
            'nama_lengkap' => $namaLengkap,
            'nip' => $data['nip'],
            'pangkat' => $data['pangkat'],
            'golongan' => $data['golongan'],
            'jabatan' => $data['jabatan']
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Data Kepala OPD tidak ditemukan'
    ]);
}
?>