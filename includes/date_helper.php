<?php
/**
 * Format TTL (Tempat, Tanggal Lahir) dalam format Indonesia
 * 
 * @param string $tempat_lahir Tempat lahir
 * @param string $tanggal_lahir Tanggal lahir (Y-m-d)
 * @return string Format: "Banjarmasin, 15 Agustus 1990"
 */
function formatTTL($tempat_lahir, $tanggal_lahir) {
    if (empty($tempat_lahir) || empty($tanggal_lahir)) {
        return '-';
    }
    
    try {
        $date = new DateTime($tanggal_lahir);
        $bulan_indonesia = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        $tanggal = $date->format('d');
        $bulan = $bulan_indonesia[(int)$date->format('m')];
        $tahun = $date->format('Y');
        
        return htmlspecialchars($tempat_lahir) . ', ' . $tanggal . ' ' . $bulan . ' ' . $tahun;
    } catch (Exception $e) {
        return htmlspecialchars($tempat_lahir) . ', ' . htmlspecialchars($tanggal_lahir);
    }
}

/**
 * Parse TTL lama (format: "Banjarmasin, 15-08-1990")
 * menjadi array terpisah untuk migrasi data
 * 
 * @param string $ttl_string TTL format lama
 * @return array ['tempat' => string, 'tanggal' => string (Y-m-d)]
 */
function parseTTL($ttl_string) {
    if (empty($ttl_string)) {
        return ['tempat' => '', 'tanggal' => null];
    }
    
    // Cek apakah ada koma
    if (strpos($ttl_string, ',') !== false) {
        $parts = explode(',', $ttl_string, 2);
        $tempat = trim($parts[0]);
        $tanggal_raw = trim($parts[1]);
        
        // Parse tanggal dengan berbagai format
        $formats = ['d-m-Y', 'd/m/Y', 'Y-m-d', 'd F Y', 'd M Y'];
        $tanggal_parsed = null;
        
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $tanggal_raw);
            if ($date !== false) {
                $tanggal_parsed = $date->format('Y-m-d');
                break;
            }
        }
        
        return [
            'tempat' => $tempat,
            'tanggal' => $tanggal_parsed
        ];
    }
    
    // Jika tidak ada koma, anggap semuanya tempat
    return ['tempat' => $ttl_string, 'tanggal' => null];
}
?>