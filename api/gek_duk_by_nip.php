<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$nip = $_GET['nip'] ?? '';

if (empty($nip)) {
    echo json_encode(['success' => false, 'message' => 'NIP tidak boleh kosong']);
    exit;
}

try {
    $query = "SELECT * FROM duk WHERE nip = ? LIMIT 1";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("s", $nip);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        // Parse TTL menjadi tempat lahir dan tanggal lahir
        if (!empty($data['ttl'])) {
            $ttl_parts = explode(',', $data['ttl']);
            $data['tempat_lahir'] = trim($ttl_parts[0]);
            
            if (isset($ttl_parts[1])) {
                // Convert format DD-MM-YYYY ke YYYY-MM-DD
                $tanggal = trim($ttl_parts[1]);
                $date_parts = preg_split('/[-\/]/', $tanggal);
                if (count($date_parts) == 3) {
                    $data['tanggal_lahir'] = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Data pegawai dengan NIP tersebut tidak ditemukan'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>