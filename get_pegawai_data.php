<?php
session_start();
require_once 'config/koneksi.php';

header('Content-Type: application/json');

// Validasi parameter NIP
if (!isset($_GET['nip']) || empty($_GET['nip'])) {
    echo json_encode([
        'success' => false,
        'type'    => 'error',
        'message' => 'NIP tidak ditemukan'
    ]);
    exit;
}

$nip = trim($_GET['nip']);

// Validasi format NIP (18 digit)
if (!preg_match('/^[0-9]{18}$/', $nip)) {
    echo json_encode([
        'success' => false,
        'type'    => 'error',
        'message' => 'Format NIP tidak valid (harus 18 digit angka)'
    ]);
    exit;
}

// ========================================
// Query ambil data dari DUK + Kepala OPD
// ========================================
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
    d.tmt_pangkat_awal,
    d.jabatan_terakhir,
    d.id_opd,
    d.status_pegawai,
    d.alasan_nonaktif,
    d.nonaktif_at,
    o.nama      AS nama_kepala_opd,
    o.nip       AS nip_kepala_opd,
    o.pangkat   AS pangkat_kepala_opd,
    o.jabatan   AS jabatan_kepala_opd,
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
        'type'    => 'error',
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
        'type'    => 'error',
        'message' => 'Data pegawai dengan NIP ' . htmlspecialchars($nip) . ' tidak ditemukan'
    ]);
    exit;
}

$pegawai = $result->fetch_assoc();
$stmt->close();

// ========================================
// CEK STATUS PEGAWAI (Nonaktif)
// ========================================
if (isset($pegawai['status_pegawai']) && $pegawai['status_pegawai'] === 'nonaktif') {

    $bulan_indo = [
        1=>'Januari', 2=>'Februari', 3=>'Maret',    4=>'April',
        5=>'Mei',     6=>'Juni',     7=>'Juli',      8=>'Agustus',
        9=>'September',10=>'Oktober',11=>'November', 12=>'Desember'
    ];

    $ts_nonaktif   = strtotime($pegawai['nonaktif_at']);
    $tgl_nonaktif  = date('d', $ts_nonaktif) . ' '
                   . $bulan_indo[(int)date('m', $ts_nonaktif)] . ' '
                   . date('Y', $ts_nonaktif);

    $ts_bisa_lagi  = strtotime('+6 months', $ts_nonaktif);
    $tgl_bisa_lagi = date('d', $ts_bisa_lagi) . ' '
                   . $bulan_indo[(int)date('m', $ts_bisa_lagi)] . ' '
                   . date('Y', $ts_bisa_lagi);

    $alasan = $pegawai['alasan_nonaktif'] ?? 'Tidak diketahui';

    $koneksi->close();
    echo json_encode([
        'success' => false,
        'type'    => 'warning',
        'message' => "Pegawai ini berstatus NONAKTIF sejak {$tgl_nonaktif} "
                   . "dengan alasan: {$alasan}. "
                   . "Data hanya tersedia sebagai riwayat. "
                   . "Pegawai ini tidak dapat diusulkan hingga {$tgl_bisa_lagi}."
    ]);
    exit;
}

// ========================================
// CEK USULAN KENAIKAN PANGKAT YANG MASIH AKTIF
// (diajukan atau disetujui tapi SK belum terbit)
// ========================================
$cek_aktif = $koneksi->prepare("
    SELECT id, nomor_usulan, status, tmt_pangkat_baru 
    FROM kenaikan_pangkat 
    WHERE nip = ? 
      AND status IN ('diajukan', 'disetujui')
      AND deleted_at IS NULL
    ORDER BY created_at DESC 
    LIMIT 1
");
$cek_aktif->bind_param("s", $nip);
$cek_aktif->execute();
$usulan_aktif = $cek_aktif->get_result()->fetch_assoc();
$cek_aktif->close();



// ========================================
// PARSE TTL — handle berbagai format
// ========================================
$tempat_lahir  = '';
$tanggal_lahir = '';

if (!empty($pegawai['ttl'])) {
    $ttl = trim($pegawai['ttl']);

    if (strpos($ttl, ',') !== false) {
        list($tempat_lahir, $tanggal_raw) = explode(',', $ttl, 2);
        $tempat_lahir = trim($tempat_lahir);
        $tanggal_raw  = trim($tanggal_raw);
    } elseif (preg_match('/^(.+?)\s+(\d{4}-\d{2}-\d{2})$/', $ttl, $m)) {
        $tempat_lahir = trim($m[1]);
        $tanggal_raw  = trim($m[2]);
    } elseif (preg_match('/^(.+?)\s+(\d{2}-\d{2}-\d{4})$/', $ttl, $m)) {
        $tempat_lahir = trim($m[1]);
        $tanggal_raw  = trim($m[2]);
    } else {
        $tempat_lahir = $ttl;
        $tanggal_raw  = '';
    }

    if (!empty($tanggal_raw)) {
        $bulan_map = [
            'Januari'=>'01','Februari'=>'02','Maret'=>'03','April'=>'04',
            'Mei'=>'05','Juni'=>'06','Juli'=>'07','Agustus'=>'08',
            'September'=>'09','Oktober'=>'10','November'=>'11','Desember'=>'12'
        ];
        foreach ($bulan_map as $nama_bulan => $angka_bulan) {
            if (stripos($tanggal_raw, $nama_bulan) !== false) {
                $tanggal_raw = str_ireplace($nama_bulan, $angka_bulan, $tanggal_raw);
                break;
            }
        }

        $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d'];
        foreach ($formats as $fmt) {
            $date_obj = DateTime::createFromFormat($fmt, $tanggal_raw);
            if ($date_obj !== false) {
                $tanggal_lahir = $date_obj->format('Y-m-d');
                break;
            }
        }

        if (empty($tanggal_lahir)) {
            $ts = strtotime($tanggal_raw);
            if ($ts !== false) {
                $tanggal_lahir = date('Y-m-d', $ts);
            }
        }
    }
}

// ========================================
// HITUNG MASA KERJA LAMA OTOMATIS
// Dihitung dari tmt_pangkat di DUK sampai hari ini.
// Karena DUK hanya diupdate setelah SK terbit (proses_sk_terbit.php),
// nilai tmt_pangkat selalu merupakan TMT pangkat yang sedang berjalan.
// ========================================
// GANTI SELURUH BAGIAN HITUNG MASA KERJA
$mk_tahun_lama = 0;
$mk_bulan_lama = 0;

// Gunakan tmt_pangkat_awal jika tmt_pangkat adalah masa depan
$tmt_untuk_hitung = $pegawai['tmt_pangkat'];

if (!empty($pegawai['tmt_pangkat_awal'])) {
    $tmt_obj = new DateTime($pegawai['tmt_pangkat']);
    $now_obj  = new DateTime();
    
    // Jika tmt_pangkat adalah masa depan, pakai tmt_pangkat_awal
    if ($tmt_obj > $now_obj) {
        $tmt_untuk_hitung = $pegawai['tmt_pangkat_awal'];
    }
}

if (!empty($tmt_untuk_hitung)) {
    $tmt  = new DateTime($tmt_untuk_hitung);
    $now  = new DateTime();
    
    if ($tmt <= $now) {
        $diff = $tmt->diff($now);
        $mk_tahun_lama = $diff->y;
        $mk_bulan_lama = $diff->m;
    }
}

// ========================================
// FORMAT NAMA KEPALA OPD
// ========================================
$nama_kepala    = '';
$nip_kepala     = '';
$pangkat_kepala = '';
$jabatan_kepala = '';

if (!empty($pegawai['nama_kepala_opd'])) {
    $gelar_depan    = !empty($pegawai['gelar_depan'])    ? $pegawai['gelar_depan'] . ' '    : '';
    $gelar_belakang = !empty($pegawai['gelar_belakang']) ? ', ' . $pegawai['gelar_belakang'] : '';

    $nama_kepala    = $gelar_depan . $pegawai['nama_kepala_opd'] . $gelar_belakang;
    $nip_kepala     = $pegawai['nip_kepala_opd'];
    $pangkat_kepala = $pegawai['pangkat_kepala_opd'];
    $jabatan_kepala = $pegawai['jabatan_kepala_opd'];
}

// ========================================
// RESPONSE JSON
// ========================================
$koneksi->close();

echo json_encode([
    'success' => true,
    'type'    => 'success',
    'message' => 'Data pegawai ditemukan',
    'data'    => [
        'nama'                  => $pegawai['nama'],
        'nip'                   => $pegawai['nip'],
        'kartu_pegawai'         => $pegawai['kartu_pegawai'],
        'tempat_lahir'          => $tempat_lahir,
        'tanggal_lahir'         => $tanggal_lahir,
        'pendidikan_terakhir'   => $pegawai['pendidikan_terakhir'],
        'prodi'                 => $pegawai['prodi'],
        'pangkat_lama'          => $pegawai['pangkat_terakhir'],
        'golongan_lama'         => $pegawai['golongan'],
        'tmt_pangkat_lama'      => $pegawai['tmt_pangkat'],
        'tmt_pangkat_awal'      => $pegawai['tmt_pangkat_awal'],
        'jabatan_lama'          => $pegawai['jabatan_terakhir'],
        'masa_kerja_tahun_lama' => $mk_tahun_lama,
        'masa_kerja_bulan_lama' => $mk_bulan_lama,
        'id_opd'                => $pegawai['id_opd'],
        'atasan_nama'           => $nama_kepala,
        'atasan_nip'            => $nip_kepala,
        'atasan_pangkat'        => $pangkat_kepala,
        'atasan_jabatan'        => $jabatan_kepala
    ]
]);