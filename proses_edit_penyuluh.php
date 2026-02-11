<?php

session_start();

require_once 'includes/alert_functions.php';
require_once 'config/koneksi.php';

// ==================== CEK AUTENTIKASI ====================
// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Pastikan user adalah admin atau superadmin
if ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'admin') {
    alertGagal('penyuluh.php', 'Anda tidak memiliki akses untuk mengubah data');
}

// ==================== VALIDASI REQUEST METHOD ====================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alertGagal('penyuluh.php', 'Invalid request method');
}

// ==================== VALIDASI ID ====================
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if (!$id || $id <= 0) {
    alertGagal('penyuluh.php', 'ID tidak valid');
}

// Cek apakah data dengan ID ini exist di database
$check = $koneksi->prepare("SELECT id, nama FROM penyuluh WHERE id = ?");
$check->bind_param("i", $id);
$check->execute();
$result_check = $check->get_result();

if ($result_check->num_rows === 0) {
    $check->close();
    alertGagal('penyuluh.php', 'Data tidak ditemukan');
}

// Simpan nama lama untuk log
$old_data = $result_check->fetch_assoc();
$nama_lama = $old_data['nama'];
$check->close();

// ==================== AMBIL DATA DARI FORM ====================
$nama = trim($_POST['nama'] ?? '');
$nip = trim($_POST['nip'] ?? '');
$ttl = trim($_POST['ttl'] ?? '');
$jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
$nomor_wa = trim($_POST['nomor_wa'] ?? '');
$pangkat_terakhir = trim($_POST['pangkat_terakhir'] ?? '');
$golongan = trim($_POST['golongan'] ?? '');
$tmt_pangkat = trim($_POST['tmt_pangkat'] ?? '');
$jabatan_terakhir = trim($_POST['jabatan_terakhir'] ?? '');
$pendidikan_terakhir = trim($_POST['pendidikan_terakhir'] ?? '');
$prodi = trim($_POST['prodi'] ?? '');

// ==================== VALIDASI DATA WAJIB ====================
$errors = [];

// Validasi nama
if (empty($nama)) {
    $errors[] = 'Nama wajib diisi';
} elseif (strlen($nama) < 3) {
    $errors[] = 'Nama minimal 3 karakter';
}

// Validasi jenis kelamin
if (empty($jenis_kelamin)) {
    $errors[] = 'Jenis kelamin wajib diisi';
} elseif (!in_array($jenis_kelamin, ['Laki-laki', 'Perempuan'])) {
    $errors[] = 'Jenis kelamin tidak valid';
}

// Validasi field wajib lainnya
if (empty($ttl)) $errors[] = 'Tempat, Tanggal Lahir wajib diisi';
if (empty($pangkat_terakhir)) $errors[] = 'Pangkat Terakhir wajib diisi';
if (empty($golongan)) $errors[] = 'Golongan wajib diisi';
if (empty($tmt_pangkat)) $errors[] = 'TMT Pangkat wajib diisi';
if (empty($jabatan_terakhir)) $errors[] = 'Jabatan Terakhir wajib diisi';
if (empty($pendidikan_terakhir)) $errors[] = 'Pendidikan Terakhir wajib diisi';
if (empty($prodi)) $errors[] = 'Program Studi wajib diisi';

// Jika ada error validasi dasar, redirect dengan pesan error
if (!empty($errors)) {
    $error_message = implode(', ', $errors);
    alertGagal("form_edit_penyuluh.php?id=$id", $error_message);
}

// ==================== VALIDASI NIP (Jika Diisi) ====================
if (!empty($nip)) {
    // Validasi format NIP (harus 18 digit)
    if (!preg_match('/^\d{18}$/', $nip)) {
        alertGagal("form_edit_penyuluh.php?id=$id", 'NIP harus 18 digit angka');
    }
    
    // Cek duplikasi NIP (kecuali untuk data ini sendiri)
    $check_nip = $koneksi->prepare("SELECT id, nama FROM penyuluh WHERE nip = ? AND id != ?");
    $check_nip->bind_param("si", $nip, $id);
    $check_nip->execute();
    $result_nip = $check_nip->get_result();
    
    if ($result_nip->num_rows > 0) {
        $existing_nip = $result_nip->fetch_assoc();
        $check_nip->close();
        alertGagal(
            "form_edit_penyuluh.php?id=$id", 
            "NIP sudah digunakan oleh penyuluh: {$existing_nip['nama']}"
        );
    }
    $check_nip->close();
}

// ==================== VALIDASI NOMOR WA (Jika Diisi) ====================
if (!empty($nomor_wa)) {
    // Validasi format nomor WA Indonesia (08xxxxxxxxxx, 10-15 digit)
    if (!preg_match('/^08\d{8,13}$/', $nomor_wa)) {
        alertGagal("form_edit_penyuluh.php?id=$id", 'Format Nomor WhatsApp tidak valid. Harus diawali 08 dan terdiri dari 10-15 digit');
    }
    
    // Cek duplikasi Nomor WA (kecuali untuk data ini sendiri)
    $check_wa = $koneksi->prepare("SELECT id, nama FROM penyuluh WHERE nomor_wa = ? AND id != ?");
    $check_wa->bind_param("si", $nomor_wa, $id);
    $check_wa->execute();
    $result_wa = $check_wa->get_result();
    
    if ($result_wa->num_rows > 0) {
        $existing_wa = $result_wa->fetch_assoc();
        $check_wa->close();
        alertGagal(
            "form_edit_penyuluh.php?id=$id", 
            "Nomor WhatsApp sudah digunakan oleh penyuluh: {$existing_wa['nama']}"
        );
    }
    $check_wa->close();
}

// ==================== SANITASI DATA UNTUK DATABASE ====================
// Set NULL untuk field kosong
$nip = empty($nip) ? NULL : $nip;
$nomor_wa = empty($nomor_wa) ? NULL : $nomor_wa;

// ==================== UPDATE KE DATABASE ====================
$sql = "UPDATE penyuluh SET 
    nama = ?, 
    nip = ?, 
    ttl = ?, 
    jenis_kelamin = ?,
    nomor_wa = ?,
    pangkat_terakhir = ?, 
    golongan = ?, 
    tmt_pangkat = ?, 
    jabatan_terakhir = ?, 
    pendidikan_terakhir = ?,
    prodi = ?
WHERE id = ?";

$stmt = $koneksi->prepare($sql);

if (!$stmt) {
    error_log("❌ Error prepare statement: " . $koneksi->error);
    alertGagal("form_edit_penyuluh.php?id=$id", 'Error database: ' . $koneksi->error);
}

// Bind parameters (12 parameter: 11 data + 1 id)
$stmt->bind_param(
    "sssssssssssi",  // 11 x string, 1 x integer (id)
    $nama,
    $nip,
    $ttl,
    $jenis_kelamin,
    $nomor_wa,
    $pangkat_terakhir,
    $golongan,
    $tmt_pangkat,
    $jabatan_terakhir,
    $pendidikan_terakhir,
    $prodi,
    $id
);

// Execute statement
if ($stmt->execute()) {
    // ==================== JIKA UPDATE BERHASIL ====================
    $stmt->close();
    $koneksi->close();
    
    // Log untuk debugging
    error_log("✅ Data Penyuluh berhasil diupdate - ID: $id | Nama Lama: $nama_lama | Nama Baru: $nama");
    
    // ✅ Redirect ke halaman penyuluh.php dengan alert sukses
    alertSuksesUbah('penyuluh.php', "Data penyuluh $nama berhasil diperbarui!");
    
} else {
    // ==================== JIKA UPDATE GAGAL ====================
    $error = $stmt->error;
    $stmt->close();
    $koneksi->close();
    
    // Log error untuk debugging
    error_log("❌ Error update statement: $error");
    
    // ✅ Redirect kembali ke form edit dengan pesan error
    alertGagal("form_edit_penyuluh.php?id=$id", 'Gagal mengupdate data: ' . $error);
}

?>