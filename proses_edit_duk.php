<?php
// proses_edit_duk.php
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
    alertGagal('dataduk.php', 'Method tidak valid!');
    exit;
}

// Validasi ID
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    alertGagal('dataduk.php', 'ID tidak valid!');
    exit;
}

$id = (int) $_POST['id'];

// Cek apakah data exists
$stmt = $koneksi->prepare('SELECT id, nama FROM duk WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    alertGagal('dataduk.php', 'Data tidak ditemukan!');
    exit;
}

$existing_data = $result->fetch_assoc();
$stmt->close();

// Ambil dan bersihkan input
$nama = trim($_POST['nama'] ?? '');
$nip = trim($_POST['nip'] ?? '');
$kartu_pegawai = trim($_POST['kartu_pegawai'] ?? '');
$ttl = trim($_POST['ttl'] ?? '');
$jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
$pendidikan_terakhir = trim($_POST['pendidikan_terakhir'] ?? '');
$prodi = trim($_POST['prodi'] ?? '');
$nomor_wa = trim($_POST['nomor_wa'] ?? '');
$pangkat_terakhir = trim($_POST['pangkat_terakhir'] ?? '');
$golongan = trim($_POST['golongan'] ?? '');
$tmt_pangkat = trim($_POST['tmt_pangkat'] ?? '');
$jabatan_terakhir = trim($_POST['jabatan_terakhir'] ?? '');
$eselon = trim($_POST['eselon'] ?? '');
$jenis_jabatan = trim($_POST['jenis_jabatan'] ?? '');
$jft_tingkat = trim($_POST['jft_tingkat'] ?? '');
$jfu_kelas = trim($_POST['jfu_kelas'] ?? '');
$tmt_eselon = trim($_POST['tmt_eselon'] ?? '');

// Field Kepala OPD (Foreign Key)
$id_opd = !empty($_POST['id_opd']) ? (int)$_POST['id_opd'] : null;
$kepala_opd_nama = trim($_POST['kepala_opd_nama'] ?? '');
$kepala_opd_nip = trim($_POST['kepala_opd_nip'] ?? '');
$kepala_opd_pangkat = trim($_POST['kepala_opd_pangkat'] ?? '');
$kepala_opd_jabatan = trim($_POST['kepala_opd_jabatan'] ?? '');
// ========== VALIDASI INPUT ==========

// Validasi nama wajib
if (empty($nama)) {
    alertGagal("form_edit_duk.php?id=$id", 'Nama wajib diisi!');
    exit;
}

// Validasi golongan wajib
if (empty($golongan)) {
    alertGagal("form_edit_duk.php?id=$id", 'Golongan wajib diisi!');
    exit;
}

// Validasi jabatan wajib
if (empty($jabatan_terakhir)) {
    alertGagal("form_edit_duk.php?id=$id", 'Jabatan terakhir wajib diisi!');
    exit;
}

// Validasi eselon wajib
if (empty($eselon)) {
    alertGagal("form_edit_duk.php?id=$id", 'Eselon wajib diisi!');
    exit;
}

// Validasi nomor WhatsApp
if (empty($nomor_wa)) {
    alertGagal("form_edit_duk.php?id=$id", 'Nomor WhatsApp wajib diisi!');
    exit;
}

// Format dan validasi nomor WhatsApp
$nomor_wa_clean = preg_replace('/[^0-9]/', '', $nomor_wa);

if (strlen($nomor_wa_clean) < 10 || strlen($nomor_wa_clean) > 15) {
    alertGagal("form_edit_duk.php?id=$id", 'Nomor WhatsApp harus 10-15 digit!');
    exit;
}

// Auto format jika diawali 0 menjadi 62
if (substr($nomor_wa_clean, 0, 1) === '0') {
    $nomor_wa_clean = '62' . substr($nomor_wa_clean, 1);
}

// Validasi khusus untuk Non-Eselon
if ($eselon === 'Non-Eselon') {
    if (empty($jenis_jabatan)) {
        alertGagal("form_edit_duk.php?id=$id", 'Jenis Jabatan wajib diisi untuk Non-Eselon!');
        exit;
    }
    
    if ($jenis_jabatan === 'JFT' && empty($jft_tingkat)) {
        alertGagal("form_edit_duk.php?id=$id", 'Tingkat JFT wajib diisi!');
        exit;
    }
    
    if ($jenis_jabatan === 'JFU' && empty($jfu_kelas)) {
        alertGagal("form_edit_duk.php?id=$id", 'Kelas JFU wajib diisi!');
        exit;
    }
} else {
    // Reset sub-fields jika bukan Non-Eselon
    $jenis_jabatan = '';
    $jft_tingkat = '';
    $jfu_kelas = '';
}

// ========== UPDATE DATABASE ==========

$sql = "UPDATE duk 
        SET nama = ?, 
            nip = ?, 
            kartu_pegawai = ?,
            ttl = ?, 
            jenis_kelamin = ?,
            pendidikan_terakhir = ?,
            prodi = ?,
            nomor_wa = ?,
            pangkat_terakhir = ?, 
            golongan = ?, 
            tmt_pangkat = ?, 
            jabatan_terakhir = ?, 
            eselon = ?, 
            jenis_jabatan = ?,
            jft_tingkat = ?,
            jfu_kelas = ?,
            tmt_eselon = ?,
            id_opd = ?
        WHERE id = ?";

$stmt = $koneksi->prepare($sql);

if (!$stmt) {
    error_log("❌ Prepare failed: " . $koneksi->error);
    alertGagal("form_edit_duk.php?id=$id", 'Gagal mempersiapkan query: ' . $koneksi->error);
    exit;
}

$stmt->bind_param(
    'sssssssssssssssssii',
    $nama,
    $nip,
    $kartu_pegawai,
    $ttl,
    $jenis_kelamin,
    $pendidikan_terakhir,
    $prodi,
    $nomor_wa_clean,
    $pangkat_terakhir,
    $golongan,
    $tmt_pangkat,
    $jabatan_terakhir,
    $eselon,
    $jenis_jabatan,
    $jft_tingkat,
    $jfu_kelas,
    $tmt_eselon,
    $id_opd,
    $id
);

if ($stmt->execute()) {
    // ========== UPDATE BERHASIL ==========
    $stmt->close();
    $koneksi->close();
    
    // Log untuk debugging
    error_log("✅ Data DUK berhasil diupdate - ID: $id | Nama: $nama");
    
    // Redirect dengan alert sukses
    alertSuksesUbah('dataduk.php', "Data pegawai $nama berhasil diperbarui!");
    exit;
    
} else {
    // ========== UPDATE GAGAL ==========
    $error_message = $stmt->error;
    $stmt->close();
    $koneksi->close();
    
    // Log error
    error_log("❌ Error update DUK - ID: $id | Error: $error_message");
    
    // Redirect dengan alert error
    alertGagal("form_edit_duk.php?id=$id", 'Gagal mengupdate data: ' . $error_message);
    exit;
}
?>