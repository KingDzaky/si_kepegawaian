<?php
session_start();
require_once 'config/koneksi.php';

header('Content-Type: application/json');

if (!isset($_GET['nip']) || empty($_GET['nip'])) {
    echo json_encode(['success' => false, 'message' => 'NIP tidak valid']);
    exit;
}

$nip = mysqli_real_escape_string($koneksi, trim($_GET['nip']));

// Cek koneksi database
if (!$koneksi) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . mysqli_connect_error()]);
    exit;
}

// Query untuk mengambil data pegawai dari tabel duk dengan JOIN ke kepala_opd
$query = "SELECT 
    d.nip,
    d.nama,
    d.kartu_pegawai,
    d.ttl,
    d.pangkat_terakhir,
    d.golongan,
    d.tmt_pangkat,
    d.jabatan_terakhir,
    d.pendidikan_terakhir,
    d.prodi,
    d.id_opd,
    k.nama as nama_kepala_opd,
    k.nip as nip_kepala_opd,
    k.pangkat as pangkat_kepala_opd,
    k.jabatan as jabatan_kepala_opd,
    k.gelar_depan,
    k.gelar_belakang
FROM duk d
LEFT JOIN kepala_opd k ON d.id_opd = k.id AND k.status = 'aktif'
WHERE d.nip = ?
LIMIT 1";

$stmt = $koneksi->prepare($query);

if (!$stmt) {
    echo json_encode([
        'success' => false, 
        'message' => 'Query error: ' . $koneksi->error
    ]);
    exit;
}

$stmt->bind_param("s", $nip);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    
    // Parse TTL (Tempat Tanggal Lahir)
    $ttl = $data['ttl'] ?? '';
    $tempat_lahir = '';
    
    if (!empty($ttl)) {
        // Coba berbagai format: "Banjarmasin, 1990-05-15" atau "Banjarmasin, 15-05-1990"
        if (strpos($ttl, ',') !== false) {
            $ttl_parts = explode(',', $ttl, 2);
            $tempat_lahir = trim($ttl_parts[0]);
            
            if (isset($ttl_parts[1])) {
                $tgl = trim($ttl_parts[1]);
                // Coba parse berbagai format tanggal
                if (strtotime($tgl)) {
                    $tanggal_lahir = date('Y-m-d', strtotime($tgl));
                }
            }
        } else {
            // Jika tidak ada koma, anggap seluruh string adalah tempat lahir
            $tempat_lahir = $ttl;
        }
    }
    
    // Format nama lengkap kepala OPD dengan gelar
    $nama_kepala = '';
    if (!empty($data['nama_kepala_opd'])) {
        $gelar_depan = !empty($data['gelar_depan']) ? $data['gelar_depan'] . ' ' : '';
        $gelar_belakang = !empty($data['gelar_belakang']) ? ', ' . $data['gelar_belakang'] : '';
        $nama_kepala = $gelar_depan . $data['nama_kepala_opd'] . $gelar_belakang;
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'nama' => $data['nama'] ?? '',
            'kartu_pegawai' => $data['kartu_pegawai'] ?? '',
            'tempat_lahir' => $tempat_lahir,
            'pangkat_lama' => $data['pangkat_terakhir'] ?? '',
            'golongan_lama' => $data['golongan'] ?? '',
            'tmt_pangkat_lama' => $data['tmt_pangkat'] ?? '',
            'jabatan_lama' => $data['jabatan_terakhir'] ?? '',
            'pendidikan_terakhir' => $data['pendidikan_terakhir'] ?? '',
            'prodi' => $data['prodi'] ?? '',
            'id_opd' => $data['id_opd'] ?? '',
            'atasan_nama' => $nama_kepala,
            'atasan_nip' => $data['nip_kepala_opd'] ?? '',
            'atasan_pangkat' => $data['pangkat_kepala_opd'] ?? '',
            'atasan_jabatan' => $data['jabatan_kepala_opd'] ?? ''
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Data pegawai dengan NIP ' . $nip . ' tidak ditemukan di database'
    ]);
}

$stmt->close();
$koneksi->close();
?>