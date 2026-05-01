<?php
session_start();
require_once 'config/koneksi.php';

// Header JSON
header('Content-Type: application/json');

// Validasi parameter NIP
if (!isset($_GET['nip']) || empty($_GET['nip'])) {
    echo json_encode([
        'success' => false,
        'message' => 'NIP tidak ditemukan'
    ]);
    exit;
}

$nip = trim($_GET['nip']);

// Validasi format NIP (18 digit)
if (!preg_match('/^[0-9]{18}$/', $nip)) {
    echo json_encode([
        'success' => false,
        'message' => 'Format NIP tidak valid (harus 18 digit angka)'
    ]);
    exit;
}

// ✅ Query ambil data dari DUK + Kepala OPD
$query = "SELECT 
    d.nama,
    d.nip,
    d.kartu_pegawai,
    d.ttl,
    d.pendidikan_terakhir,
    d.prodi,
    d.pangkat_terakhir,
    d.golongan,
    d.tmt_pangkat,
    d.jabatan_terakhir,
    d.id_opd,
    o.nama as nama_kepala_opd,
    o.nip as nip_kepala_opd,
    o.pangkat as pangkat_kepala_opd,
    o.jabatan as jabatan_kepala_opd,
    o.gelar_depan,
    o.gelar_belakang
FROM duk d
LEFT JOIN kepala_opd o ON d.id_opd = o.id
WHERE d.nip = ? 
  AND d.deleted_at IS NULL
LIMIT 1";

$stmt = $koneksi->prepare($query);

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $koneksi->error
    ]);
    exit;
}

$stmt->bind_param("s", $nip);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $koneksi->close();
    
    echo json_encode([
        'success' => false,
        'message' => 'Data pegawai dengan NIP ' . htmlspecialchars($nip) . ' tidak ditemukan'
    ]);
    exit;
}

$pegawai = $result->fetch_assoc();
$stmt->close();

// ========================================
// ✅ PARSE TTL - handle berbagai format
// ========================================
$tempat_lahir = '';
$tanggal_lahir = '';

if (!empty($pegawai['ttl'])) {
    $ttl = trim($pegawai['ttl']);
    
    // Coba pisah dengan koma dulu: "Banjarmasin, 02-08-1969"
    if (strpos($ttl, ',') !== false) {
        list($tempat_lahir, $tanggal_raw) = explode(',', $ttl, 2);
        $tempat_lahir = trim($tempat_lahir);
        $tanggal_raw  = trim($tanggal_raw);
    }
    // Kalau tidak ada koma, pisah pakai spasi sebelum tahun: "Banjarmasin 1969-08-02"
    elseif (preg_match('/^(.+?)\s+(\d{4}-\d{2}-\d{2})$/', $ttl, $m)) {
        $tempat_lahir = trim($m[1]);
        $tanggal_raw  = trim($m[2]);
    }
    // Format: "Banjarmasin 02-08-1969"
    elseif (preg_match('/^(.+?)\s+(\d{2}-\d{2}-\d{4})$/', $ttl, $m)) {
        $tempat_lahir = trim($m[1]);
        $tanggal_raw  = trim($m[2]);
    }
    else {
        // Tidak bisa diparsing, simpan mentah
        $tempat_lahir = $ttl;
        $tanggal_raw  = '';
    }
    
    // Convert tanggal_raw ke Y-m-d
    if (!empty($tanggal_raw)) {
        $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'd F Y', 'd M Y'];
        foreach ($formats as $fmt) {
            $date_obj = DateTime::createFromFormat($fmt, $tanggal_raw);
            if ($date_obj !== false) {
                $tanggal_lahir = $date_obj->format('Y-m-d');
                break;
            }
        }
        // Fallback strtotime
        if (empty($tanggal_lahir)) {
            $ts = strtotime($tanggal_raw);
            if ($ts !== false) {
                $tanggal_lahir = date('Y-m-d', $ts);
            }
        }
    }
}
// ========================================
// ✅ FORMAT NAMA KEPALA OPD
// ========================================
$nama_kepala = '';
$nip_kepala = '';
$pangkat_kepala = '';
$jabatan_kepala = '';

if (!empty($pegawai['nama_kepala_opd'])) {
    $gelar_depan = !empty($pegawai['gelar_depan']) ? $pegawai['gelar_depan'] . ' ' : '';
    $gelar_belakang = !empty($pegawai['gelar_belakang']) ? ', ' . $pegawai['gelar_belakang'] : '';
    
    $nama_kepala = $gelar_depan . $pegawai['nama_kepala_opd'] . $gelar_belakang;
    $nip_kepala = $pegawai['nip_kepala_opd'];
    $pangkat_kepala = $pegawai['pangkat_kepala_opd'];
    $jabatan_kepala = $pegawai['jabatan_kepala_opd'];
}

// ========================================
// ✅ RESPONSE JSON
// ========================================
$response = [
    'success' => true,
    'message' => 'Data pegawai ditemukan',
    'data' => [
        'nama' => $pegawai['nama'],
        'nip' => $pegawai['nip'],
        'kartu_pegawai' => $pegawai['kartu_pegawai'],
        'tempat_lahir' => $tempat_lahir,        // ✅ "Banjarmasin"
        'tanggal_lahir' => $tanggal_lahir,      // ✅ "1969-08-02"
        'pendidikan_terakhir' => $pegawai['pendidikan_terakhir'],
        'prodi' => $pegawai['prodi'],
        'pangkat_lama' => $pegawai['pangkat_terakhir'],
        'golongan_lama' => $pegawai['golongan'],
        'tmt_pangkat_lama' => $pegawai['tmt_pangkat'],
        'jabatan_lama' => $pegawai['jabatan_terakhir'],
        'id_opd' => $pegawai['id_opd'],
        'atasan_nama' => $nama_kepala,
        'atasan_nip' => $nip_kepala,
        'atasan_pangkat' => $pangkat_kepala,
        'atasan_jabatan' => $jabatan_kepala
    ]
];

$koneksi->close();

echo json_encode($response);
?>