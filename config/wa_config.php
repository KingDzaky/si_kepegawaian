<?php
/**
 * Konfigurasi WhatsApp Gateway
 * 
 * PILIHAN GATEWAY:
 * 1. WABLAS (Default) - Recommended
 * 2. WOOWA
 * 3. FONNTE
 */

// ============================================
// PILIH GATEWAY YANG DIGUNAKAN
// ============================================
define('WA_GATEWAY', 'WABLAS'); // Ganti: WABLAS, WOOWA, atau FONNTE

// ============================================
// KONFIGURASI WABLAS
// ============================================
define('WABLAS_TOKEN', 'GS6Dum7rKBgjLR0gP7CKh3GEdQOTRZFppCAdjTVUzxO9l34EnJOiFav'); // Token dari dashboard Wablas
define('WABLAS_SECRET_KEY', 'YHViIfuj'); // Secret key dari dashboard Wablas
define('WABLAS_URL', 'https://bdg.wablas.com/'); // Domain Wablas Anda

// ============================================
// KONFIGURASI WOOWA (Alternatif)
// ============================================
define('WOOWA_TOKEN', 'GANTI_DENGAN_TOKEN_WOOWA_ANDA');
define('WOOWA_URL', 'https://api.woowa.id');

// ============================================
// KONFIGURASI FONNTE (Alternatif)
// ============================================
define('FONNTE_TOKEN', 'GANTI_DENGAN_TOKEN_FONNTE_ANDA');
define('FONNTE_API_URL', 'https://api.fonnte.com/send');

// ============================================
// PENGATURAN UMUM
// ============================================
define('WA_SENDER_NAME', 'DPPKBPM Kota Banjarmasin');
define('WA_SIGNATURE', "\n\n---\nPesan otomatis dari Sistem Informasi Kepegawaian\nDPPKBPM Kota Banjarmasin");

/**
 * Template pesan notifikasi
 */
class TemplateNotifikasi {
    
    /**
     * Template untuk status disetujui
     */
    public static function disetujui($data) {
        $pesan = "*NOTIFIKASI KENAIKAN PANGKAT*\n";
        $pesan .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        $pesan .= "Yth. Bapak/Ibu *{$data['nama']}*\n";
        $pesan .= "NIP: {$data['nip']}\n\n";
        $pesan .= "Dengan hormat,\n\n";
        $pesan .= "Kami informasikan bahwa usulan kenaikan pangkat Anda telah *DISETUJUI* ✅\n\n";
        $pesan .= "*Detail Usulan:*\n";
        $pesan .= "📋 Nomor: {$data['nomor_usulan']}\n";
        $pesan .= "📅 Tanggal: " . date('d/m/Y', strtotime($data['tanggal_usulan'])) . "\n\n";
        $pesan .= "*Kenaikan Pangkat:*\n";
        $pesan .= "Dari: {$data['pangkat_lama']} ({$data['golongan_lama']})\n";
        $pesan .= "Ke: *{$data['pangkat_baru']}* (*{$data['golongan_baru']}*)\n";
        $pesan .= "TMT: " . date('d/m/Y', strtotime($data['tmt_pangkat_baru'])) . "\n\n";
        $pesan .= "Silakan login ke sistem untuk melihat detail lengkap.\n";
        $pesan .= WA_SIGNATURE;
        
        return $pesan;
    }
    
    /**
     * Template untuk status ditolak
     */
    public static function ditolak($data) {
        $pesan = "*NOTIFIKASI KENAIKAN PANGKAT*\n";
        $pesan .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        $pesan .= "Yth. Bapak/Ibu *{$data['nama']}*\n";
        $pesan .= "NIP: {$data['nip']}\n\n";
        $pesan .= "Dengan hormat,\n\n";
        $pesan .= "Kami informasikan bahwa usulan kenaikan pangkat Anda *TIDAK DISETUJUI* ❌\n\n";
        $pesan .= "*Detail Usulan:*\n";
        $pesan .= "📋 Nomor: {$data['nomor_usulan']}\n";
        $pesan .= "📅 Tanggal: " . date('d/m/Y', strtotime($data['tanggal_usulan'])) . "\n\n";
        
        if (!empty($data['keterangan'])) {
            $pesan .= "*Alasan:*\n{$data['keterangan']}\n\n";
        }
        
        $pesan .= "Silakan hubungi bagian kepegawaian untuk informasi lebih lanjut.\n";
        $pesan .= WA_SIGNATURE;
        
        return $pesan;
    }
    
    /**
     * Template untuk pengingat melengkapi data
     */
    public static function reminder($data) {
        $pesan = "*PENGINGAT KENAIKAN PANGKAT*\n";
        $pesan .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        $pesan .= "Yth. Bapak/Ibu *{$data['nama']}*\n";
        $pesan .= "NIP: {$data['nip']}\n\n";
        $pesan .= "Kami mengingatkan Anda untuk segera melengkapi dokumen kenaikan pangkat.\n\n";
        $pesan .= "*Detail:*\n";
        $pesan .= "📋 Nomor: {$data['nomor_usulan']}\n";
        $pesan .= "⏰ Batas Waktu: " . date('d/m/Y', strtotime($data['batas_waktu'])) . "\n\n";
        $pesan .= "Silakan login ke sistem untuk upload dokumen.\n";
        $pesan .= WA_SIGNATURE;
        
        return $pesan;
    }
}
?>