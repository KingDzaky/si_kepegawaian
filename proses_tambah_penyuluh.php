<?php
// proses_tambah_penyuluh.php
session_start();
require_once 'config/koneksi.php';
require_once 'includes/alert_functions.php';

// Cek apakah request method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: form_tambah_penyuluh.php');
    exit;
}

// Function untuk sanitasi input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Ambil dan sanitasi data dari form
$nama = sanitizeInput($_POST['nama'] ?? '');
$nip = sanitizeInput($_POST['nip'] ?? '');
$ttl = sanitizeInput($_POST['ttl'] ?? '');
$pangkat_terakhir = sanitizeInput($_POST['pangkat_terakhir'] ?? '');
$golongan = sanitizeInput($_POST['golongan'] ?? '');
$tmt_pangkat = sanitizeInput($_POST['tmt_pangkat'] ?? '');
$jabatan_terakhir = sanitizeInput($_POST['jabatan_terakhir'] ?? '');
$pendidikan_terakhir = sanitizeInput($_POST['pendidikan_terakhir'] ?? '');
$prodi = sanitizeInput($_POST['prodi'] ?? '');
$jenis_kelamin = sanitizeInput($_POST['jenis_kelamin'] ?? '');
$nomor_wa = sanitizeInput($_POST['nomor_wa'] ?? ''); // FIELD BARU

// Array untuk menyimpan error
$errors = [];

// Validasi field wajib
if (empty($nama)) {
    $errors[] = 'Nama harus diisi';
}

if (empty($jenis_kelamin)) {
    $errors[] = 'Jenis kelamin harus dipilih';
}

// Validasi panjang nama
if (strlen($nama) < 3) {
    $errors[] = 'Nama minimal 3 karakter';
}

if (strlen($nama) > 100) {
    $errors[] = 'Nama maksimal 100 karakter';
}

// Validasi NIP jika diisi
if (!empty($nip)) {
    if (!preg_match('/^\d{18}$/', $nip)) {
        $errors[] = 'NIP harus 18 digit angka';
    }
    
    // Cek apakah NIP sudah ada di database
    $check_nip = $koneksi->prepare("SELECT id FROM penyuluh WHERE nip = ?");
    $check_nip->bind_param("s", $nip);
    $check_nip->execute();
    $check_nip->store_result();
    
    if ($check_nip->num_rows > 0) {
        $errors[] = 'NIP sudah terdaftar dalam sistem';
    }
    $check_nip->close();
}

// Validasi jenis kelamin
if (!in_array($jenis_kelamin, ['Laki-laki', 'Perempuan'])) {
    $errors[] = 'Jenis kelamin tidak valid';
}

// VALIDASI NOMOR WA (OPSIONAL, TAPI JIKA DIISI HARUS VALID)
if (!empty($nomor_wa)) {
    // Format: 62xxx (10-15 digit total)
    if (!preg_match('/^62\d{9,13}$/', $nomor_wa)) {
        $errors[] = 'Format nomor WhatsApp tidak valid (harus: 62xxx dengan total 11-15 digit)';
    }
}

// Validasi format tanggal jika diisi
if (!empty($tmt_pangkat)) {
    $date = DateTime::createFromFormat('Y-m-d', $tmt_pangkat);
    if (!$date || $date->format('Y-m-d') !== $tmt_pangkat) {
        $errors[] = 'Format tanggal TMT Pangkat tidak valid';
    }
}

// Jika ada error, redirect kembali dengan error message
if (!empty($errors)) {
    $error_message = implode(', ', $errors);
    alertGagal('form_tambah_penyuluh.php', $error_message);
    exit;
}

// DEBUG LOG
error_log("===== PROSES TAMBAH PENYULUH =====");
error_log("Nama: $nama");
error_log("NIP: $nip");
error_log("Nomor WA: $nomor_wa");

// Escape data untuk query SQL
$nama = mysqli_real_escape_string($koneksi, $nama);
$nip = mysqli_real_escape_string($koneksi, $nip);
$ttl = mysqli_real_escape_string($koneksi, $ttl);
$pangkat_terakhir = mysqli_real_escape_string($koneksi, $pangkat_terakhir);
$golongan = mysqli_real_escape_string($koneksi, $golongan);
$tmt_pangkat = !empty($tmt_pangkat) ? mysqli_real_escape_string($koneksi, $tmt_pangkat) : null;
$jabatan_terakhir = mysqli_real_escape_string($koneksi, $jabatan_terakhir);
$pendidikan_terakhir = mysqli_real_escape_string($koneksi, $pendidikan_terakhir);
$prodi = mysqli_real_escape_string($koneksi, $prodi);
$jenis_kelamin = mysqli_real_escape_string($koneksi, $jenis_kelamin);
$nomor_wa = !empty($nomor_wa) ? mysqli_real_escape_string($koneksi, $nomor_wa) : null; // FIELD BARU

// Prepare statement untuk insert data
$sql = "INSERT INTO penyuluh (
    nama, 
    nip, 
    ttl, 
    pangkat_terakhir, 
    golongan, 
    tmt_pangkat, 
    jabatan_terakhir, 
    pendidikan_terakhir,
    prodi,
    jenis_kelamin,
    nomor_wa,
    created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $koneksi->prepare($sql);

if (!$stmt) {
    error_log("Prepare error: " . $koneksi->error);
    alertGagal('form_tambah_penyuluh.php', 'Gagal mempersiapkan query: ' . $koneksi->error);
    exit;
}

// Bind parameters - SEKARANG ADA 11 PARAMETER (tambah nomor_wa)
$stmt->bind_param(
    "sssssssssss", 
    $nama, 
    $nip, 
    $ttl, 
    $pangkat_terakhir, 
    $golongan, 
    $tmt_pangkat, 
    $jabatan_terakhir, 
    $pendidikan_terakhir,
    $prodi,
    $jenis_kelamin,
    $nomor_wa // PARAMETER BARU
);

// Execute statement
if ($stmt->execute()) {
    $insert_id = $stmt->insert_id;
    $stmt->close();
    $koneksi->close();
    
    // Log sukses
    error_log("✅ Data Penyuluh berhasil ditambahkan - ID: $insert_id | Nama: $nama | NIP: $nip | WA: " . ($nomor_wa ?: 'tidak ada'));
    
    // Redirect dengan alert sukses
    alertSuksesTambah('penyuluh.php', "Data penyuluh $nama berhasil ditambahkan!");
    
} else {
    $error = $stmt->error;
    $stmt->close();
    $koneksi->close();
    
    // Log error
    error_log("❌ Error execute statement: $error");
    
    // Redirect dengan alert gagal
    alertGagal('form_tambah_penyuluh.php', 'Gagal menyimpan data: ' . $error);
}
?>