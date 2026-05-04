<?php
session_start();
require_once 'check_session.php';
require_once 'config/koneksi.php';
require_once 'includes/alert_functions.php';

// Hanya superadmin dan admin yang bisa akses
if (!isAdmin()) {
    alertGagal('dashboard.php', 'Akses ditolak! Anda tidak memiliki izin.');
    exit;
}

// DEBUG: Log semua POST data
error_log("===== POST DATA =====");
error_log(print_r($_POST, true));

// Escape semua input
$nomor_usulan = mysqli_real_escape_string($koneksi, trim($_POST['nomor_usulan'] ?? ''));
$tanggal_usulan = mysqli_real_escape_string($koneksi, $_POST['tanggal_usulan'] ?? '');
$nip = mysqli_real_escape_string($koneksi, trim($_POST['nip'] ?? ''));
$nama = mysqli_real_escape_string($koneksi, trim($_POST['nama'] ?? ''));
$kartu_pegawai = mysqli_real_escape_string($koneksi, trim($_POST['kartu_pegawai'] ?? ''));
$pendidikan_terakhir = mysqli_real_escape_string($koneksi, $_POST['pendidikan_terakhir'] ?? '');
$prodi = mysqli_real_escape_string($koneksi, trim($_POST['prodi'] ?? ''));

// ✅ PARSING TTL - handle semua format
$tempat_lahir = '';
$tanggal_lahir = null;

if (!empty($nip)) {
    $query_ttl = "SELECT ttl FROM duk WHERE nip = ? AND deleted_at IS NULL LIMIT 1";
    $stmt_ttl = $koneksi->prepare($query_ttl);
    $stmt_ttl->bind_param("s", $nip);
    $stmt_ttl->execute();
    $result_ttl = $stmt_ttl->get_result();
    
    if ($result_ttl->num_rows > 0) {
        $row_ttl = $result_ttl->fetch_assoc();
        $ttl_raw = trim($row_ttl['ttl']);
        error_log("TTL dari DUK: [$ttl_raw]");
        
        // ── STEP 1: Pisah tempat & tanggal ──────────────────────────
        $tanggal_raw = '';
        
        if (strpos($ttl_raw, ',') !== false) {
            // Format: "Banjarmasin, ..." (ada koma)
            [$tempat_lahir, $tanggal_raw] = explode(',', $ttl_raw, 2);
            $tempat_lahir = trim($tempat_lahir);
            $tanggal_raw  = trim($tanggal_raw);
        } elseif (preg_match('/^(.+?)\s+(\d{4}-\d{2}-\d{2})$/', $ttl_raw, $m)) {
            // Format: "Banjarmasin 1990-01-19"
            $tempat_lahir = trim($m[1]);
            $tanggal_raw  = trim($m[2]);
        } elseif (preg_match('/^(.+?)\s+(\d{2}-\d{2}-\d{4})$/', $ttl_raw, $m)) {
            // Format: "Banjarmasin 19-01-1990"
            $tempat_lahir = trim($m[1]);
            $tanggal_raw  = trim($m[2]);
        } else {
            $tempat_lahir = $ttl_raw;
            $tanggal_raw  = '';
        }
        
        // ── STEP 2: Normalisasi bulan nama Indonesia ─────────────────
        // Handle: "1990-Januari-19", "19-Januari-1990", "19 Januari 1990"
        if (!empty($tanggal_raw)) {
            $bulan_map = [
                'Januari'=>'01', 'Februari'=>'02', 'Maret'=>'03',    'April'=>'04',
                'Mei'=>'05',     'Juni'=>'06',     'Juli'=>'07',     'Agustus'=>'08',
                'September'=>'09','Oktober'=>'10', 'November'=>'11', 'Desember'=>'12'
            ];
           foreach ($bulan_map as $nama_bulan => $angka_bulan) {
                if (stripos($tanggal_raw, $nama_bulan) !== false) {
                    $tanggal_raw = str_ireplace($nama_bulan, $angka_bulan, $tanggal_raw);
                    break;
                }
            }
            // Setelah replace: "1990-01-19" atau "19-01-1990" atau "19 01 1990"
        }
        
        // ── STEP 3: Parse ke Y-m-d ──────────────────────────────────
        if (!empty($tanggal_raw)) {
            $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d', 'd-m-Y', 'Y-d-m'];
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
                $tanggal_lahir = ($ts !== false) ? date('Y-m-d', $ts) : null;
            }
        }
        
        error_log("Hasil parse → Tempat: [$tempat_lahir] | Tanggal: [$tanggal_lahir]");
    }
    $stmt_ttl->close();
}

// Data Lama
$pangkat_lama = mysqli_real_escape_string($koneksi, trim($_POST['pangkat_lama'] ?? ''));
$golongan_lama = mysqli_real_escape_string($koneksi, $_POST['golongan_lama'] ?? '');
$tmt_pangkat_lama = mysqli_real_escape_string($koneksi, $_POST['tmt_pangkat_lama'] ?? '');
$masa_kerja_tahun_lama = (int)($_POST['masa_kerja_tahun_lama'] ?? 0);
$masa_kerja_bulan_lama = (int)($_POST['masa_kerja_bulan_lama'] ?? 0);
$gaji_pokok_lama = (float)str_replace(['.', ','], '', $_POST['gaji_pokok_lama'] ?? '0');
$jabatan_lama = mysqli_real_escape_string($koneksi, trim($_POST['jabatan_lama'] ?? ''));

// Data Baru
$pangkat_baru = mysqli_real_escape_string($koneksi, trim($_POST['pangkat_baru'] ?? ''));
$golongan_baru = mysqli_real_escape_string($koneksi, $_POST['golongan_baru'] ?? '');
$tmt_pangkat_baru = mysqli_real_escape_string($koneksi, $_POST['tmt_pangkat_baru'] ?? '');
$masa_kerja_tahun_baru = (int)($_POST['masa_kerja_tahun_baru'] ?? 0);
$masa_kerja_bulan_baru = (int)($_POST['masa_kerja_bulan_baru'] ?? 0);
$gaji_pokok_baru = (float)str_replace(['.', ','], '', $_POST['gaji_pokok_baru'] ?? '0');
$jabatan_baru = mysqli_real_escape_string($koneksi, trim($_POST['jabatan_baru'] ?? ''));

// Masa Kerja Golongan
$mk_golongan_tahun = (int)($_POST['mk_golongan_tahun'] ?? 0);
$mk_golongan_bulan = (int)($_POST['mk_golongan_bulan'] ?? 0);
$mk_dari_sampai = mysqli_real_escape_string($koneksi, trim($_POST['mk_dari_sampai'] ?? ''));

// Jenis & Atasan
$jenis_kenaikan = mysqli_real_escape_string($koneksi, $_POST['jenis_kenaikan'] ?? '');
$atasan_nama = mysqli_real_escape_string($koneksi, trim($_POST['atasan_nama'] ?? ''));
$atasan_nip = mysqli_real_escape_string($koneksi, trim($_POST['atasan_nip'] ?? ''));
$atasan_pangkat = mysqli_real_escape_string($koneksi, trim($_POST['atasan_pangkat'] ?? ''));
$atasan_jabatan = mysqli_real_escape_string($koneksi, trim($_POST['atasan_jabatan'] ?? ''));

// Wilayah & SKP
$wilayah_pembayaran = mysqli_real_escape_string($koneksi, trim($_POST['wilayah_pembayaran'] ?? ''));
$skp_tahun_1 = mysqli_real_escape_string($koneksi, trim($_POST['skp_tahun_1'] ?? ''));
$skp_nilai_1 = mysqli_real_escape_string($koneksi, trim($_POST['skp_nilai_1'] ?? ''));
$skp_tahun_2 = mysqli_real_escape_string($koneksi, trim($_POST['skp_tahun_2'] ?? ''));
$skp_nilai_2 = mysqli_real_escape_string($koneksi, trim($_POST['skp_nilai_2'] ?? ''));

$status = mysqli_real_escape_string($koneksi, $_POST['status'] ?? '');

// TAMBAHAN: Ambil id_opd dari duk berdasarkan NIP
$id_opd = null;
if (!empty($nip)) {
    $query_opd = "SELECT id_opd FROM duk WHERE nip = ? LIMIT 1";
    $stmt_opd = $koneksi->prepare($query_opd);
    $stmt_opd->bind_param("s", $nip);
    $stmt_opd->execute();
    $result_opd = $stmt_opd->get_result();

    if ($result_opd->num_rows > 0) {
        $row_opd = $result_opd->fetch_assoc();
        $id_opd = (int)$row_opd['id_opd'];
    }
    $stmt_opd->close();
}

error_log("ID OPD yang diambil: " . ($id_opd ?? 'NULL'));

// Jika id_opd NULL, set ke 0 atau berikan error
if ($id_opd === null) {
    header('Location: form_tambah_kenaikan_pangkat.php?error=Data pegawai tidak ditemukan atau belum memiliki ID OPD');
    exit;
}

// ✅ Query INSERT dengan tempat_lahir dan tanggal_lahir terpisah (38 kolom)
$query = "INSERT INTO kenaikan_pangkat (
    nomor_usulan, tanggal_usulan, nip, id_opd, nama, kartu_pegawai, 
    tempat_lahir, tanggal_lahir, 
    pendidikan_terakhir, prodi, pangkat_lama, golongan_lama, tmt_pangkat_lama, 
    masa_kerja_tahun_lama, masa_kerja_bulan_lama, gaji_pokok_lama, jabatan_lama,
    pangkat_baru, golongan_baru, tmt_pangkat_baru, masa_kerja_tahun_baru, 
    masa_kerja_bulan_baru, gaji_pokok_baru, jabatan_baru, mk_golongan_tahun, 
    mk_golongan_bulan, mk_dari_sampai, jenis_kenaikan, atasan_nama, atasan_nip, 
    atasan_pangkat, atasan_jabatan, wilayah_pembayaran, skp_tahun_1, skp_nilai_1, 
    skp_tahun_2, skp_nilai_2, status
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Hitung jumlah placeholder
$placeholder_count = substr_count($query, '?');
error_log("Jumlah placeholder (?): $placeholder_count");

$stmt = $koneksi->prepare($query);

if (!$stmt) {
    error_log("Prepare error: " . $koneksi->error);
    header('Location: form_tambah_kenaikan_pangkat.php?error=Query error: ' . urlencode($koneksi->error));
    exit;
}

// ✅ Tipe data: 38 parameter
// s=string, i=integer, d=double
$types = "ssissssssssssiidssssiidssiisssssssssss";
//        ^       ^^ tempat_lahir (s), tanggal_lahir (s)
error_log("Jumlah tipe data: " . strlen($types)); // Harus 38

// ✅ Bind parameter dengan urutan yang BENAR
$bind_result = $stmt->bind_param(
    $types,
    $nomor_usulan,           // 1  - s
    $tanggal_usulan,         // 2  - s
    $nip,                    // 3  - s
    $id_opd,                 // 4  - i
    $nama,                   // 5  - s
    $kartu_pegawai,          // 6  - s
    $tempat_lahir,           // 7  - s ✅ BARU
    $tanggal_lahir,          // 8  - s ✅ BARU (DATE disimpan sebagai string)
    $pendidikan_terakhir,    // 9  - s
    $prodi,                  // 10 - s
    $pangkat_lama,           // 11 - s
    $golongan_lama,          // 12 - s
    $tmt_pangkat_lama,       // 13 - s
    $masa_kerja_tahun_lama,  // 14 - i
    $masa_kerja_bulan_lama,  // 15 - i
    $gaji_pokok_lama,        // 16 - d
    $jabatan_lama,           // 17 - s
    $pangkat_baru,           // 18 - s
    $golongan_baru,          // 19 - s
    $tmt_pangkat_baru,       // 20 - s
    $masa_kerja_tahun_baru,  // 21 - i
    $masa_kerja_bulan_baru,  // 22 - i
    $gaji_pokok_baru,        // 23 - d
    $jabatan_baru,           // 24 - s
    $mk_golongan_tahun,      // 25 - i
    $mk_golongan_bulan,      // 26 - i
    $mk_dari_sampai,         // 27 - s
    $jenis_kenaikan,         // 28 - s
    $atasan_nama,            // 29 - s
    $atasan_nip,             // 30 - s
    $atasan_pangkat,         // 31 - s
    $atasan_jabatan,         // 32 - s
    $wilayah_pembayaran,     // 33 - s
    $skp_tahun_1,            // 34 - s
    $skp_nilai_1,            // 35 - s
    $skp_tahun_2,            // 36 - s
    $skp_nilai_2,            // 37 - s
    $status                  // 38 - s
);

if (!$bind_result) {
    error_log("Bind param error");
    header('Location: form_tambah_kenaikan_pangkat.php?error=Bind parameter error');
    exit;
}

// Execute statement
if ($stmt->execute()) {
    $insert_id = $stmt->insert_id;
    $stmt->close();
    $koneksi->close();
    
    // Log sukses
    error_log("✅ Data Kenaikan Pangkat berhasil ditambahkan - ID: $insert_id | Nama: $nama | Tempat Lahir: $tempat_lahir | Tanggal Lahir: $tanggal_lahir");
    
    // Redirect dengan alert sukses
    alertSuksesTambah('kenaikan_pangkat.php', "Data Usulan Pegawai Atas Nama $nama berhasil ditambahkan!");
    
} else {
    $error = $stmt->error;
    $stmt->close();
    $koneksi->close();
    
    // Log error
    error_log("❌ Error execute statement: $error");
    
    // Redirect dengan alert gagal
    alertGagal('form_tambah_kenaikan_pangkat.php', 'Gagal menyimpan data: ' . $error);
}
?>