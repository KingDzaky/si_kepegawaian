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

// Validasi method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alertGagal('usulan_pensiun.php', 'Method tidak valid!');
    exit;
}

// DEBUG: Log semua POST data
error_log("===== POST DATA USULAN PENSIUN =====");
error_log(print_r($_POST, true));

// Escape semua input untuk keamanan
$nomor_usulan = mysqli_real_escape_string($koneksi, trim($_POST['nomor_usulan'] ?? ''));
$tanggal_usulan = mysqli_real_escape_string($koneksi, $_POST['tanggal_usulan'] ?? '');
$sumber_data = mysqli_real_escape_string($koneksi, $_POST['sumber_data'] ?? '');
$nip = mysqli_real_escape_string($koneksi, trim($_POST['nip'] ?? ''));
$nama = mysqli_real_escape_string($koneksi, trim($_POST['nama'] ?? ''));
$kartu_pegawai = mysqli_real_escape_string($koneksi, trim($_POST['kartu_pegawai'] ?? ''));
$ttl = mysqli_real_escape_string($koneksi, trim($_POST['ttl'] ?? ''));
$tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir'] ?? '');
$tanggal_pensiun = mysqli_real_escape_string($koneksi, $_POST['tanggal_pensiun'] ?? '');
$pangkat_terakhir = mysqli_real_escape_string($koneksi, trim($_POST['pangkat_terakhir'] ?? ''));
$golongan = mysqli_real_escape_string($koneksi, trim($_POST['golongan'] ?? ''));
$tmt_pangkat = mysqli_real_escape_string($koneksi, $_POST['tmt_pangkat'] ?? '');
$jabatan_terakhir = mysqli_real_escape_string($koneksi, trim($_POST['jabatan_terakhir'] ?? ''));
$pendidikan_terakhir = mysqli_real_escape_string($koneksi, trim($_POST['pendidikan_terakhir'] ?? ''));
$prodi = mysqli_real_escape_string($koneksi, trim($_POST['prodi'] ?? ''));
$jenis_kelamin = mysqli_real_escape_string($koneksi, trim($_POST['jenis_kelamin'] ?? ''));
$nomor_wa = mysqli_real_escape_string($koneksi, trim($_POST['nomor_wa'] ?? ''));
$jenis_pensiun = mysqli_real_escape_string($koneksi, $_POST['jenis_pensiun'] ?? 'BUP');
$alasan = mysqli_real_escape_string($koneksi, trim($_POST['alasan'] ?? ''));
$status = mysqli_real_escape_string($koneksi, $_POST['status'] ?? 'draft');

// Validasi data wajib
if (empty($nomor_usulan)) {
    alertWarning('form_tambah_usulan_pensiun.php', 'Nomor usulan harus diisi!');
    exit;
}

if (empty($nip)) {
    alertWarning('form_tambah_usulan_pensiun.php', 'NIP harus diisi!');
    exit;
}

if (empty($nama)) {
    alertWarning('form_tambah_usulan_pensiun.php', 'Nama harus diisi!');
    exit;
}

if (empty($tanggal_pensiun)) {
    alertWarning('form_tambah_usulan_pensiun.php', 'Tanggal pensiun harus diisi!');
    exit;
}

// DEBUG
error_log("Validasi lolos. NIP: $nip, Nama: $nama");

// ✅ CEK STATUS PEGAWAI DI DUK
if (!empty($nip)) {
    $cek_status = $koneksi->prepare("SELECT status_pegawai, alasan_nonaktif, nonaktif_at 
                                     FROM duk 
                                     WHERE nip = ? AND deleted_at IS NULL LIMIT 1");
    $cek_status->bind_param("s", $nip);
    $cek_status->execute();
    $res_status = $cek_status->get_result();
    
    if ($res_status->num_rows > 0) {
        $data_status = $res_status->fetch_assoc();
        
        if ($data_status['status_pegawai'] === 'nonaktif') {
            $alasan   = $data_status['alasan_nonaktif'] ?? '-';
            $tgl_nonaktif = date('d/m/Y', strtotime($data_status['nonaktif_at']));
            
            $cek_status->close();
            alertWarning(
                'form_tambah_usulan_pensiun.php',
                "Pegawai dengan NIP $nip tidak dapat diusulkan karena berstatus NONAKTIF "
                . "sejak $tgl_nonaktif dengan alasan: $alasan."
            );
            exit;
        }
    }
    $cek_status->close();
}

// Cek apakah nomor usulan sudah ada (untuk mencegah duplikasi)
$check_nomor_query = "SELECT id FROM usulan_pensiun WHERE nomor_usulan = ?";
$check_nomor_stmt = $koneksi->prepare($check_nomor_query);

if (!$check_nomor_stmt) {
    error_log("Prepare error (cek nomor): " . $koneksi->error);
    alertGagal('form_tambah_usulan_pensiun.php', 'Terjadi kesalahan sistem!');
    exit;
}

$check_nomor_stmt->bind_param("s", $nomor_usulan);
$check_nomor_stmt->execute();
$check_nomor_result = $check_nomor_stmt->get_result();

if ($check_nomor_result->num_rows > 0) {
    $check_nomor_stmt->close();
    alertWarning('form_tambah_usulan_pensiun.php', 'Nomor usulan sudah digunakan! Gunakan nomor lain.');
    exit;
}
$check_nomor_stmt->close();

// Cek apakah NIP sudah pernah diajukan
$check_query = "SELECT id, nama FROM usulan_pensiun WHERE nip = ?";
$check_stmt = $koneksi->prepare($check_query);

if (!$check_stmt) {
    error_log("Prepare error (cek NIP): " . $koneksi->error);
    alertGagal('form_tambah_usulan_pensiun.php', 'Terjadi kesalahan sistem!');
    exit;
}

$check_stmt->bind_param("s", $nip);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $existing = $check_result->fetch_assoc();
    $check_stmt->close();
    alertWarning('form_tambah_usulan_pensiun.php', "NIP {$nip} (a.n. {$existing['nama']}) sudah pernah diajukan untuk pensiun!");
    exit;
}
$check_stmt->close();

// DEBUG
error_log("Cek duplikasi lolos. Memulai INSERT...");

// Insert data ke database
$query = "INSERT INTO usulan_pensiun (
    nomor_usulan, tanggal_usulan, nip, sumber_data,
    nama, kartu_pegawai, ttl, tanggal_lahir, tanggal_pensiun,
    pangkat_terakhir, golongan, tmt_pangkat, jabatan_terakhir,
    pendidikan_terakhir, prodi, jenis_kelamin, nomor_wa,
    jenis_pensiun, alasan, status
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $koneksi->prepare($query);

if (!$stmt) {
    error_log("Prepare error (INSERT): " . $koneksi->error);
    alertGagal('form_tambah_usulan_pensiun.php', 'Terjadi kesalahan query database!');
    exit;
}

// Bind parameter (20 parameter - 20 tipe 's')
$bind_result = $stmt->bind_param(
    "ssssssssssssssssssss",
    $nomor_usulan, $tanggal_usulan, $nip, $sumber_data,
    $nama, $kartu_pegawai, $ttl, $tanggal_lahir, $tanggal_pensiun,
    $pangkat_terakhir, $golongan, $tmt_pangkat, $jabatan_terakhir,
    $pendidikan_terakhir, $prodi, $jenis_kelamin, $nomor_wa,
    $jenis_pensiun, $alasan, $status
);

if (!$bind_result) {
    error_log("Bind param error");
    alertGagal('form_tambah_usulan_pensiun.php', 'Terjadi kesalahan bind parameter!');
    exit;
}

// Execute statement
if ($stmt->execute()) {
    $insert_id = $stmt->insert_id;
    $stmt->close();
    $koneksi->close();
    
    // Log sukses
    error_log("✅ Usulan Pensiun berhasil ditambahkan - ID: $insert_id | Nama: $nama | NIP: $nip");
    
    // Redirect dengan alert sukses
    alertSuksesTambah('usulan_pensiun.php', "Usulan pensiun atas nama $nama berhasil ditambahkan!");
    
} else {
    $error = $stmt->error;
    $stmt->close();
    $koneksi->close();
    
    // Log error
    error_log("❌ Error execute statement: $error");
    
    // Redirect dengan alert gagal
    alertGagal('form_tambah_usulan_pensiun.php', 'Gagal menyimpan data: ' . $error);
}
?>