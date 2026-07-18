<?php
/**
 * lihat_berkas.php
 * Viewer universal untuk berkas Kenaikan Pangkat & Pensiun.
 * Tujuannya: tampil INLINE di tab baru (PDF/JPG/PNG), bukan ke-download paksa.
 *
 * Kenapa pakai script ini, bukan link langsung ke file?
 * Kalau link langsung ke uploads/.../file.pdf, perilakunya tergantung config
 * Apache/.htaccess di folder uploads (banyak yang sudah diset "Content-Disposition: attachment"
 * demi keamanan). Dengan baca file lewat PHP & set header sendiri, kita bypass itu semua
 * dan kontrol penuh kapan file tampil inline vs harus didownload (mis. .rar/.zip/.docx
 * memang gak bisa di-preview browser, jadi otomatis didownload — itu wajar).
 *
 * Pemakaian:
 *   lihat_berkas.php/berkas.pdf?id=5&jenis=kp        -> dari tabel berkas_kenaikan_pangkat
 *   lihat_berkas.php/berkas.pdf?id=5&jenis=pensiun   -> dari tabel berkas_pensiun
 *
 * CATATAN: bagian "/berkas.pdf" sebelum tanda "?" itu cuma kosmetik (PATH_INFO),
 * PHP tetap baca id & jenis dari query string seperti biasa, gak perlu file
 * fisik bernama "berkas.pdf". Ini sengaja ditambahin supaya browser/download
 * manager (mis. IDM) ngenalin tipe filenya dari URL, bukan cuma dari header,
 * jadi PDF/gambar kebuka inline bukan keunduh sebagai file generik.
 */

session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';

$id    = (int)($_GET['id'] ?? 0);
$jenis = $_GET['jenis'] ?? '';

if (!$id || !in_array($jenis, ['kp', 'pensiun'], true)) {
    http_response_code(400);
    die('Parameter tidak valid.');
}

$table = $jenis === 'kp' ? 'berkas_kenaikan_pangkat' : 'berkas_pensiun';

$stmt = $koneksi->prepare("SELECT file_path, nama_file FROM `{$table}` WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$berkas = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$berkas) {
    http_response_code(404);
    die('Data berkas tidak ditemukan.');
}

// Validasi path fisik — cegah path traversal, file harus tetap di dalam folder uploads/
$rootPath = realpath(__DIR__);
$filePath = realpath($rootPath . '/' . $berkas['file_path']);
$uploadsBase = realpath($rootPath . '/uploads');

if (!$filePath || !$uploadsBase || strpos($filePath, $uploadsBase) !== 0 || !file_exists($filePath)) {
    http_response_code(404);
    die('File fisik tidak ditemukan di server. Kemungkinan file sudah dipindah/dihapus manual.');
}

$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

// Tipe yang BISA ditampilkan langsung di browser
$mimeInline = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
];

if (isset($mimeInline[$ext])) {
    header('Content-Type: ' . $mimeInline[$ext]);
    header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
} else {
    // Tipe lain (rar, zip, docx, dll) — browser memang tidak bisa preview, jadi didownload
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . ($berkas['nama_file'] ?: basename($filePath)) . '"');
}

header('Content-Length: ' . filesize($filePath));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0, must-revalidate');

readfile($filePath);
exit;